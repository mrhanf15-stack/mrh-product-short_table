<?php
/**
 * MRH Product Attributes - Migration Handler
 * 
 * Handles bulk migration of product attributes from existing
 * HTML tables in products_short_description to the new structured table.
 * 
 * Uses TR-class parsing (language-independent classes like fem, reg, aut,
 * kreuzung, cbd_w, sort, anbau, etc.) to extract values.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MrhPaMigration {
    
    /**
     * TR-class to field mapping.
     * Maps the CSS class on <tr> elements to our database fields.
     */
    const TR_CLASS_MAP = [
        'fem'        => 'gender',       // Feminisiert row
        'reg'        => 'gender',       // Regulaer row
        'aut'        => 'flowering_type', // Autoflowering row
        'kreuzung'   => 'cross_genetics',
        'thc'        => 'thc',
        'cbd_w'      => 'cbd',
        'sort'       => 'type',
        'ertrag_in'  => 'yield_indoor',
        'ertrag_out' => 'yield_outdoor',
        'hoehe_in'   => 'height_indoor',
        'hoehe_out'  => 'height_outdoor',
        'bluete'     => 'flowering_time',
        'ernte'      => 'harvest_time',
        'klima'      => 'climate',
        'wirkung'    => 'effect',
        'geschmack'  => 'taste',
        'anbau'      => 'growing',
    ];
    
    /**
     * Gender value mapping (from TR content to DB value).
     */
    const GENDER_MAP = [
        'feminisiert'  => 'feminized',
        'feminized'    => 'feminized',
        'féminisée'    => 'feminized',
        'feminizada'   => 'feminized',
        'regulär'      => 'regular',
        'regulaer'     => 'regular',
        'regular'      => 'regular',
        'régulière'    => 'regular',
        'autoflowering' => 'autoflower',
        'autofloraison' => 'autoflower',
        'autofloreciente' => 'autoflower',
    ];
    
    /**
     * Type value mapping.
     */
    const TYPE_MAP = [
        'indica'          => 'indica',
        'sativa'          => 'sativa',
        'hybrid'          => 'hybrid',
        'indica dominant' => 'indica_dom',
        'indica-dominant' => 'indica_dom',
        'sativa dominant' => 'sativa_dom',
        'sativa-dominant' => 'sativa_dom',
    ];
    
    /**
     * Growing value mapping.
     */
    const GROWING_MAP = [
        'indoor'          => 'indoor',
        'outdoor'         => 'outdoor',
        'gewächshaus'     => 'greenhouse',
        'gewaechshaus'    => 'greenhouse',
        'greenhouse'      => 'greenhouse',
        'serre'           => 'greenhouse',
        'invernadero'     => 'greenhouse',
        'indoor/outdoor'  => 'all',
        'indoor / outdoor' => 'all',
    ];
    
    /**
     * Process a batch of products for migration.
     *
     * @return array Status information
     */
    public static function processBatch() {
        $batch_size = (int)MrhProductAttributes::getConfig('migration_batch_size', 100);
        $last_id = (int)MrhProductAttributes::getConfig('migration_last_id', 0);
        
        // Get total count
        $total_q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt FROM products_description");
        $total_row = xtc_db_fetch_array($total_q);
        $total = (int)$total_row['cnt'];
        
        // Get batch of products
        $q = xtc_db_query("SELECT DISTINCT pd.products_id 
            FROM products_description pd
            INNER JOIN products p ON p.products_id = pd.products_id
            WHERE pd.products_id > " . $last_id . "
            ORDER BY pd.products_id ASC
            LIMIT " . $batch_size);
        
        if (xtc_db_num_rows($q) == 0) {
            MrhProductAttributes::setConfig('migration_status', 'done');
            return [
                'done' => true,
                'processed' => 0,
                'migrated' => 0,
                'skipped' => 0,
                'total' => $total,
                'processed_total' => $total,
            ];
        }
        
        MrhProductAttributes::setConfig('migration_status', 'running');
        
        $processed = 0;
        $migrated = 0;
        $skipped = 0;
        $max_id = $last_id;
        
        while ($row = xtc_db_fetch_array($q)) {
            $pid = (int)$row['products_id'];
            $max_id = max($max_id, $pid);
            $processed++;
            
            // Get all language descriptions
            $desc_q = xtc_db_query("SELECT language_id, products_short_description 
                FROM products_description 
                WHERE products_id = " . $pid);
            
            $any_migrated = false;
            
            while ($desc = xtc_db_fetch_array($desc_q)) {
                $lid = (int)$desc['language_id'];
                $html = $desc['products_short_description'];
                
                if (empty($html)) continue;
                
                // Parse the HTML table
                $parsed = self::parseShortDescription($html);
                
                if (!empty($parsed)) {
                    // Check if already migrated
                    $existing = MrhProductAttributes::getAttributes($pid, $lid);
                    if ($existing && $existing['data_source'] === 'manual') {
                        // Don't overwrite manual entries
                        continue;
                    }
                    
                    MrhProductAttributes::saveAttributes($pid, $lid, $parsed, 'migration');
                    $any_migrated = true;
                }
            }
            
            if ($any_migrated) {
                $migrated++;
            } else {
                $skipped++;
            }
        }
        
        // Update last processed ID
        MrhProductAttributes::setConfig('migration_last_id', (string)$max_id);
        
        // Calculate total processed so far
        $processed_total_q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt 
            FROM products_description 
            WHERE products_id <= " . $max_id);
        $pt_row = xtc_db_fetch_array($processed_total_q);
        $processed_total = (int)$pt_row['cnt'];
        
        return [
            'done' => false,
            'processed' => $processed,
            'migrated' => $migrated,
            'skipped' => $skipped,
            'total' => $total,
            'processed_total' => $processed_total,
            'last_id' => $max_id,
        ];
    }
    
    /**
     * Parse a products_short_description HTML to extract structured data.
     *
     * @param string $html The short description HTML
     * @return array Extracted fields (may be empty if no table found)
     */
    public static function parseShortDescription($html) {
        if (empty($html)) return [];
        
        $result = [];
        $is_seed = false;
        
        // Use DOMDocument to parse HTML
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', 
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $xpath = new \DOMXPath($doc);
        
        // Find all TR elements
        $rows = $xpath->query('//tr');
        
        foreach ($rows as $tr) {
            $class = $tr->getAttribute('class');
            if (empty($class)) continue;
            
            // Check each known TR class
            $classes = preg_split('/\s+/', trim($class));
            
            foreach ($classes as $cls) {
                $cls = strtolower(trim($cls));
                
                if (!isset(self::TR_CLASS_MAP[$cls])) continue;
                
                $field = self::TR_CLASS_MAP[$cls];
                $is_seed = true; // If we find seed-related TR classes, it's a seed product
                
                // Get the value from the second TD
                $tds = $tr->getElementsByTagName('td');
                if ($tds->length < 2) continue;
                
                $value = trim(strip_tags($tds->item(1)->textContent));
                if (empty($value) || $value === '-' || $value === 'n/a') continue;
                
                // Map special values
                if ($field === 'gender') {
                    $value_lower = mb_strtolower($value);
                    if ($cls === 'fem') {
                        $result['gender'] = 'feminized';
                    } elseif ($cls === 'reg') {
                        $result['gender'] = 'regular';
                    } else {
                        // Try to match from content
                        foreach (self::GENDER_MAP as $pattern => $mapped) {
                            if (mb_stripos($value_lower, $pattern) !== false) {
                                $result['gender'] = $mapped;
                                break;
                            }
                        }
                    }
                    continue;
                }
                
                if ($field === 'flowering_type') {
                    if ($cls === 'aut') {
                        $result['flowering_type'] = 'autoflower';
                        // If autoflower, also set gender to autoflower if not already set
                        if (!isset($result['gender'])) {
                            $result['gender'] = 'feminized'; // Most autos are feminized
                        }
                    }
                    continue;
                }
                
                if ($field === 'type') {
                    $value_lower = mb_strtolower($value);
                    $mapped = false;
                    foreach (self::TYPE_MAP as $pattern => $type_val) {
                        if (mb_stripos($value_lower, $pattern) !== false) {
                            $result['type'] = $type_val;
                            $mapped = true;
                            break;
                        }
                    }
                    if (!$mapped) {
                        $result['type'] = $value; // Store raw value
                    }
                    continue;
                }
                
                if ($field === 'growing') {
                    $value_lower = mb_strtolower($value);
                    $mapped = false;
                    foreach (self::GROWING_MAP as $pattern => $grow_val) {
                        if (mb_stripos($value_lower, $pattern) !== false) {
                            $result['growing'] = $grow_val;
                            $mapped = true;
                            break;
                        }
                    }
                    if (!$mapped) {
                        $result['growing'] = $value;
                    }
                    continue;
                }
                
                // For all other text fields, store the raw value
                $result[$field] = $value;
            }
        }
        
        // Determine flowering_type from context if not explicitly set
        if (!isset($result['flowering_type']) && isset($result['gender'])) {
            if ($result['gender'] === 'autoflower') {
                $result['flowering_type'] = 'autoflower';
                $result['gender'] = 'feminized'; // Reclassify: auto is a flowering type, not gender
            } else {
                $result['flowering_type'] = 'photoperiod'; // Default for non-auto
            }
        }
        
        // Set is_seed flag
        $result['is_seed'] = $is_seed ? 1 : 0;
        
        return $result;
    }
    
    /**
     * Test migration on a single product (for debugging).
     *
     * @param int $products_id
     * @return array
     */
    public static function testProduct($products_id) {
        $results = [];
        
        $q = xtc_db_query("SELECT language_id, products_short_description 
            FROM products_description 
            WHERE products_id = " . (int)$products_id);
        
        while ($row = xtc_db_fetch_array($q)) {
            $parsed = self::parseShortDescription($row['products_short_description']);
            $results[$row['language_id']] = [
                'html_length' => strlen($row['products_short_description']),
                'parsed_fields' => count($parsed),
                'data' => $parsed,
            ];
        }
        
        return $results;
    }
}
