<?php
/**
 * MRH Product Attributes - AI Handler
 * 
 * Handles communication with OpenAI-compatible APIs for:
 * - Extracting product attributes from descriptions
 * - Translating attributes to other languages
 * - Suggesting additional customer-relevant fields
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

if (!defined('_VALID_XTC')) { return; }

class MrhPaAiHandler {
    
    /**
     * Fill a single product's attributes using AI.
     *
     * @param int $products_id
     * @param string $api_key
     * @return array ['success' => bool, 'message' => string, 'attributes' => array]
     */
    public static function fillProduct($products_id, $api_key) {
        $products_id = (int)$products_id;
        
        // Get product descriptions (all languages)
        $languages = [];
        $lang_q = xtc_db_query("SELECT languages_id, code, name FROM languages ORDER BY sort_order");
        while ($l = xtc_db_fetch_array($lang_q)) {
            $languages[$l['languages_id']] = $l;
        }
        
        // Get product name and descriptions
        $descriptions = [];
        $product_name = '';
        foreach ($languages as $lid => $lang) {
            $q = xtc_db_query("SELECT products_name, products_short_description, products_description 
                FROM products_description 
                WHERE products_id = " . $products_id . " AND language_id = " . $lid);
            if (xtc_db_num_rows($q) > 0) {
                $row = xtc_db_fetch_array($q);
                $descriptions[$lid] = $row;
                if (empty($product_name)) {
                    $product_name = $row['products_name'];
                }
            }
        }
        
        if (empty($descriptions)) {
            return ['success' => false, 'message' => 'Keine Produktbeschreibung gefunden.'];
        }
        
        // Use German description as primary source (or first available)
        // Find German language_id dynamically (not hardcoded)
        $primary_lid = 0;
        foreach ($languages as $lid => $lang) {
            if ($lang['code'] === 'de') {
                $primary_lid = $lid;
                break;
            }
        }
        // Fallback to first available language
        if ($primary_lid == 0 || !isset($descriptions[$primary_lid])) {
            $primary_lid = array_key_first($descriptions);
        }
        $primary_desc = $descriptions[$primary_lid] ?? reset($descriptions);
        
        // Strip HTML but keep text content
        $short_text = strip_tags($primary_desc['products_short_description'] ?? '');
        $long_text = strip_tags($primary_desc['products_description'] ?? '');
        
        // Truncate to avoid token limits
        $short_text = mb_substr($short_text, 0, 2000);
        $long_text = mb_substr($long_text, 0, 4000);
        
        // Build the prompt
        $prompt = self::buildExtractionPrompt($product_name, $short_text, $long_text);
        
        // Call the API
        $model = MrhProductAttributes::getConfig('openai_model', 'gpt-4.1-nano');
        $base_url = MrhProductAttributes::getConfig('openai_base_url', '');
        
        $result = self::callApi($api_key, $model, $base_url, $prompt);
        
        if (!$result['success']) {
            return $result;
        }
        
        // Parse AI response
        $ai_data = $result['data'];
        
        // Map AI response to fields
        $save_data = self::mapAiResponseToFields($ai_data);
        $save_data['ai_confidence'] = $ai_data['confidence'] ?? 0.8;
        
        // DEDUP: Only fill fields that are currently empty
        $existing = MrhProductAttributes::getAttributes($products_id, $primary_lid);
        if ($existing) {
            foreach (MrhProductAttributes::STANDARD_FIELDS as $sf_key => $sf_meta) {
                if (!empty($existing[$sf_key]) && isset($save_data[$sf_key])) {
                    unset($save_data[$sf_key]); // Don't overwrite existing data
                }
            }
            // Don't overwrite existing custom fields
            if (!empty($existing['custom_fields'])) {
                $existing_cf = json_decode($existing['custom_fields'], true) ?: [];
                if (!empty($existing_cf)) {
                    $existing_labels = array_map(function($cf) {
                        return mb_strtolower(trim($cf['label'] ?? ''));
                    }, $existing_cf);
                    // Filter AI custom fields: only add truly new ones
                    if (isset($save_data['custom_fields']) && is_array($save_data['custom_fields'])) {
                        $save_data['custom_fields'] = array_filter($save_data['custom_fields'], function($cf) use ($existing_labels) {
                            return !in_array(mb_strtolower(trim($cf['label'] ?? '')), $existing_labels);
                        });
                        // Also filter out labels that match standard fields
                        $std_labels = [];
                        foreach (MrhProductAttributes::STANDARD_FIELDS as $sf_meta) {
                            $std_labels[] = mb_strtolower(trim($sf_meta[0]));
                            $std_labels[] = mb_strtolower(trim($sf_meta[1]));
                        }
                        $save_data['custom_fields'] = array_filter($save_data['custom_fields'], function($cf) use ($std_labels) {
                            return !in_array(mb_strtolower(trim($cf['label'] ?? '')), $std_labels);
                        });
                        $save_data['custom_fields'] = array_values($save_data['custom_fields']);
                    }
                }
            }
        }
        
        MrhProductAttributes::saveAttributes($products_id, $primary_lid, $save_data, 'ai');
        
        // Auto-translate to other languages if enabled
        $auto_translate = MrhProductAttributes::getConfig('ai_auto_translate', '1');
        $translated_attrs = [$primary_lid => $save_data];
        
        if ($auto_translate === '1' && count($languages) > 1) {
            foreach ($languages as $lid => $lang) {
                if ($lid == $primary_lid) continue;
                
                // For select fields, copy as-is (they use keys, not translated values)
                $trans_data = [];
                $select_fields = ['gender', 'flowering_type', 'type', 'growing', 'is_seed'];
                foreach ($select_fields as $sf) {
                    if (isset($save_data[$sf])) {
                        $trans_data[$sf] = $save_data[$sf];
                    }
                }
                
                // For text fields, translate via AI
                $text_fields = ['cross_genetics', 'thc', 'cbd', 'yield_indoor', 'yield_outdoor',
                    'height_indoor', 'height_outdoor', 'flowering_time', 'harvest_time',
                    'climate', 'effect', 'taste'];
                
                $to_translate = [];
                foreach ($text_fields as $tf) {
                    if (!empty($save_data[$tf])) {
                        $to_translate[$tf] = $save_data[$tf];
                    }
                }
                
                if (!empty($to_translate)) {
                    $trans_result = self::translateFields($api_key, $model, $base_url, $to_translate, $lang['code']);
                    if ($trans_result['success']) {
                        $trans_data = array_merge($trans_data, $trans_result['data']);
                    } else {
                        // Fallback: copy German values
                        $trans_data = array_merge($trans_data, $to_translate);
                    }
                }
                
                $trans_data['ai_confidence'] = $save_data['ai_confidence'] ?? 0.7;
                MrhProductAttributes::saveAttributes($products_id, $lid, $trans_data, 'ai');
                $translated_attrs[$lid] = $trans_data;
            }
        }
        
        return [
            'success' => true,
            'message' => defined('MRH_PA_MSG_AI_SUCCESS') ? MRH_PA_MSG_AI_SUCCESS : 'KI-Befuellung erfolgreich.',
            'attributes' => $translated_attrs,
        ];
    }
    
    /**
     * Build the extraction prompt for the AI.
     *
     * @param string $name Product name
     * @param string $short Short description text
     * @param string $long Long description text
     * @return string
     */
    private static function buildExtractionPrompt($name, $short, $long) {
        return 'Du bist ein Cannabis-Samen-Experte. Analysiere die folgende Produktbeschreibung und extrahiere die strukturierten Eigenschaften.

Produktname: ' . $name . '

Kurzbeschreibung:
' . $short . '

Beschreibung:
' . $long . '

Extrahiere folgende Felder als JSON. Verwende NUR die angegebenen Werte fuer Select-Felder:

{
  "gender": "feminized" | "regular" | "autoflower" | null,
  "flowering_type": "photoperiod" | "autoflower" | null,
  "cross_genetics": "Elternpflanzen / Genetik (String)" | null,
  "thc": "THC-Gehalt als String, z.B. 20-25%" | null,
  "cbd": "CBD-Gehalt als String, z.B. 0.1%" | null,
  "type": "indica" | "sativa" | "hybrid" | "indica_dom" | "sativa_dom" | null,
  "yield_indoor": "Ertrag Indoor als String, z.B. 500-600g/m²" | null,
  "yield_outdoor": "Ertrag Outdoor als String, z.B. 600-800g/Pflanze" | null,
  "height_indoor": "Hoehe Indoor als String, z.B. 80-120cm" | null,
  "height_outdoor": "Hoehe Outdoor als String, z.B. 150-200cm" | null,
  "flowering_time": "Bluetezeit als String, z.B. 8-10 Wochen" | null,
  "harvest_time": "Erntezeit als String, z.B. Oktober" | null,
  "climate": "Klima als String, z.B. Warm, Mediterran" | null,
  "effect": "Wirkung als String, z.B. Entspannend, Euphorisch" | null,
  "taste": "Geschmack als String, z.B. Fruchtig, Erdig" | null,
  "growing": "indoor" | "outdoor" | "greenhouse" | "all" | null,
  "is_seed": true | false,
  "confidence": 0.0-1.0,
  "suggested_fields": [
    {"label": "Feldname", "value": "Wert"}
  ]
}

Regeln:
- Wenn ein Feld nicht aus der Beschreibung ableitbar ist, setze null
- "is_seed" ist true wenn es sich um Samen/Seeds handelt, false bei Zubehoer, Duenger etc.
- "confidence" ist dein Vertrauen in die Gesamtextraktion (0.0 = unsicher, 1.0 = sicher)
- "suggested_fields" sind zusaetzliche Felder die fuer Kunden interessant sein koennten
- Antworte NUR mit dem JSON, kein weiterer Text';
    }
    
    /**
     * Map AI response to database fields.
     *
     * @param array $ai_data Parsed AI response
     * @return array
     */
    private static function mapAiResponseToFields($ai_data) {
        $fields = [];
        
        $direct_map = [
            'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
            'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
            'flowering_time', 'harvest_time', 'climate', 'effect', 'taste', 'growing'
        ];
        
        foreach ($direct_map as $key) {
            if (isset($ai_data[$key]) && $ai_data[$key] !== null) {
                $fields[$key] = $ai_data[$key];
            }
        }
        
        // is_seed
        $fields['is_seed'] = isset($ai_data['is_seed']) ? ($ai_data['is_seed'] ? 1 : 0) : 1;
        
        // Suggested fields as custom_fields
        if (!empty($ai_data['suggested_fields']) && is_array($ai_data['suggested_fields'])) {
            $fields['custom_fields'] = $ai_data['suggested_fields'];
        }
        
        return $fields;
    }
    
    /**
     * Translate text fields to another language.
     *
     * @param string $api_key
     * @param string $model
     * @param string $base_url
     * @param array $fields Key-value pairs to translate
     * @param string $target_lang Target language code (en, fr, es)
     * @return array
     */
    private static function translateFields($api_key, $model, $base_url, $fields, $target_lang) {
        $lang_names = ['en' => 'English', 'fr' => 'French', 'es' => 'Spanish', 'de' => 'German'];
        $target_name = $lang_names[$target_lang] ?? $target_lang;
        
        $json_input = json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $prompt = 'Translate the following cannabis product attribute values from German to ' . $target_name . '. 
Keep the JSON keys unchanged, only translate the values. Keep technical terms, numbers, and units as-is.
Return ONLY the JSON, no other text.

' . $json_input;
        
        $result = self::callApi($api_key, $model, $base_url, $prompt);
        
        if ($result['success']) {
            return ['success' => true, 'data' => $result['data']];
        }
        
        return ['success' => false, 'message' => $result['message'] ?? 'Translation failed'];
    }
    
    /**
     * Call the OpenAI-compatible API.
     *
     * @param string $api_key
     * @param string $model
     * @param string $base_url
     * @param string $prompt
     * @return array
     */
    private static function callApi($api_key, $model, $base_url, $prompt) {
        if (empty($base_url)) {
            $base_url = 'https://api.openai.com/v1';
        }
        
        $url = rtrim($base_url, '/') . '/chat/completions';
        
        $payload = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Du bist ein Experte fuer Cannabis-Samen und Produktdaten. Antworte immer in validem JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 2000,
            'response_format' => ['type' => 'json_object'],
        ]);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => 'cURL Error: ' . $error];
        }
        
        if ($http_code !== 200) {
            $error_data = json_decode($response, true);
            $error_msg = $error_data['error']['message'] ?? ('HTTP ' . $http_code);
            return ['success' => false, 'message' => 'API Error: ' . $error_msg];
        }
        
        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            return ['success' => false, 'message' => 'Ungueltige API-Antwort'];
        }
        
        $content = $data['choices'][0]['message']['content'];
        $parsed = json_decode($content, true);
        
        if (!$parsed) {
            // Try to extract JSON from markdown code blocks
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $m)) {
                $parsed = json_decode($m[1], true);
            }
        }
        
        if (!$parsed) {
            return ['success' => false, 'message' => 'KI-Antwort konnte nicht als JSON geparst werden.'];
        }
        
        return ['success' => true, 'data' => $parsed];
    }
    
    /**
     * Batch process products with AI (for migration step 2).
     *
     * @param string $api_key
     * @param int $batch_size
     * @param int $min_fields Minimum fields threshold
     * @return array
     */
    public static function processBatchAi($api_key, $batch_size = 10, $min_fields = 3) {
        $results = ['processed' => 0, 'success' => 0, 'failed' => 0, 'done' => false];
        
        // Find products with less than min_fields filled
        $q = xtc_db_query("SELECT DISTINCT pa.products_id 
            FROM " . MrhProductAttributes::TABLE . " pa
            WHERE pa.fields_filled < " . (int)$min_fields . "
            AND pa.products_id NOT IN (
                SELECT products_id FROM " . MrhProductAttributes::TABLE . " 
                WHERE data_source = 'ai' AND fields_filled >= " . (int)$min_fields . "
            )
            LIMIT " . (int)$batch_size);
        
        if (xtc_db_num_rows($q) == 0) {
            $results['done'] = true;
            return $results;
        }
        
        while ($row = xtc_db_fetch_array($q)) {
            $result = self::fillProduct((int)$row['products_id'], $api_key);
            $results['processed']++;
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            // Small delay to avoid rate limiting
            usleep(200000); // 200ms
        }
        
        return $results;
    }
}
