<?php
/**
 * MRH Product Attributes - Save Hook (Product Description)
 * Autoinclude: ~/admin/includes/extra/modules/new_product_description/
 * 
 * Saves the structured attributes when a product is saved in categories.php.
 * This hook runs inside the product save loop (once per language).
 *
 * Variables available from Modified Shop core:
 * - $products_id: The product ID being saved
 * - $languages_id: Current language ID in the loop (from $languages[$i]['id'])
 * - $_POST: Form data
 *
 * Now also handles:
 * - pictos (JSON array of icon objects)
 * - cannabis_cups (integer trophy count)
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Only process if our form data is present
if (isset($_POST['mrh_pa']) && !empty($_POST['mrh_pa']['products_id'])) {
    
    // Load module class
    if (!class_exists('MrhProductAttributes')) {
        $mrh_pa_class = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_product_attributes.php';
        if (file_exists($mrh_pa_class)) {
            require_once($mrh_pa_class);
        }
    }
    
    if (class_exists('MrhProductAttributes')) {
        $mrh_pa_pid = (int)$_POST['mrh_pa']['products_id'];
        $mrh_pa_is_seed = isset($_POST['mrh_pa']['is_seed']) ? (int)$_POST['mrh_pa']['is_seed'] : 1;
        
        // Pictos (global, not per language) — JSON string from hidden input
        $mrh_pa_pictos = null;
        if (isset($_POST['mrh_pa']['pictos'])) {
            $mrh_pa_pictos_raw = $_POST['mrh_pa']['pictos'];
            $mrh_pa_pictos_decoded = json_decode($mrh_pa_pictos_raw, true);
            if (is_array($mrh_pa_pictos_decoded)) {
                // Sanitize each picto entry
                $mrh_pa_pictos = [];
                foreach ($mrh_pa_pictos_decoded as $p) {
                    if (!empty($p['icon'])) {
                        $mrh_pa_pictos[] = [
                            'icon'  => preg_replace('/[^a-zA-Z0-9\- ]/', '', $p['icon'] ?? ''),
                            'color' => preg_replace('/[^a-zA-Z0-9#]/', '', $p['color'] ?? '#333333'),
                            'size'  => preg_replace('/[^a-zA-Z0-9.]/', '', $p['size'] ?? '1em'),
                            'title' => mb_substr(strip_tags($p['title'] ?? ''), 0, 100),
                        ];
                    }
                }
            }
        }
        
        // Cannabis Cups (global, not per language)
        $mrh_pa_cups = isset($_POST['mrh_pa']['cannabis_cups']) ? max(0, min(20, (int)$_POST['mrh_pa']['cannabis_cups'])) : 0;
        
        // If we're in the first language iteration, save all languages at once
        // (to avoid saving multiple times in the loop)
        static $mrh_pa_saved = false;
        if (!$mrh_pa_saved) {
            $mrh_pa_saved = true;
            
            // Process each language
            foreach ($_POST['mrh_pa'] as $lang_key => $lang_data) {
                // Skip non-language keys
                if (!is_numeric($lang_key)) continue;
                
                $lang_id = (int)$lang_key;
                
                // Build data array
                $data = [
                    'is_seed' => $mrh_pa_is_seed,
                ];
                
                // Standard fields
                $standard_fields = [
                    'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
                    'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
                    'flowering_time', 'harvest_time', 'climate', 'effect', 'taste', 'growing'
                ];
                
                foreach ($standard_fields as $field) {
                    if (isset($lang_data[$field])) {
                        $data[$field] = trim($lang_data[$field]);
                    }
                }
                
                // Custom fields
                if (isset($lang_data['custom']) && is_array($lang_data['custom'])) {
                    $custom_fields = [];
                    foreach ($lang_data['custom'] as $cf) {
                        if (!empty($cf['label']) || !empty($cf['value'])) {
                            $custom_fields[] = [
                                'label' => trim($cf['label'] ?? ''),
                                'value' => trim($cf['value'] ?? ''),
                            ];
                        }
                    }
                    if (!empty($custom_fields)) {
                        $data['custom_fields'] = $custom_fields;
                    }
                }
                
                // Pictos (same for all languages)
                if ($mrh_pa_pictos !== null) {
                    $data['pictos'] = $mrh_pa_pictos;
                }
                
                // Cannabis Cups (same for all languages)
                $data['cannabis_cups'] = $mrh_pa_cups;
                
                // Save
                MrhProductAttributes::saveAttributes($mrh_pa_pid, $lang_id, $data, 'manual');
            }
        }
    }
}
