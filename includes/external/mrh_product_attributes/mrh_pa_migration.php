<?php
/**
 * MRH Product Attributes - Migration Handler
 * 
 * Handles bulk migration of product attributes from existing
 * HTML tables in products_short_description to the new structured table.
 * 
 * Parses:
 * - TR-class based data (fem, reg, aut, kreuzung, cbd_w, sort, anbau, etc.)
 * - FontAwesome picto icons (fa-medkit, fa-tachometer, etc.)
 * - Cannabis Cup trophies (fa-trophy count)
 * - Preset detection from picto icons (feminized/autoflowering/regular)
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MrhPaMigration {
    
    /**
     * TR-class to field mapping.
     * Maps the CSS class on <tr> elements to our database fields.
     */
    const TR_CLASS_MAP = [
        'fem'        => 'gender',
        'reg'        => 'gender',
        'aut'        => 'flowering_type',
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
     * Known picto icon mapping.
     * Maps FontAwesome icon class to a structured picto entry.
     * These are the standard icons used in mr-hanf.at short descriptions.
     */
    const PICTO_ICON_MAP = [
        'fa-medkit'     => ['title_de' => 'Medical', 'title_en' => 'Medical', 'default_color' => '#ff6666'],
        'fa-tachometer' => ['title_de' => 'Autoflowering', 'title_en' => 'Autoflowering', 'default_color' => '#54B80D'],
        'fa-female'     => ['title_de' => 'Feminisiert', 'title_en' => 'Feminized', 'default_color' => '#e84393'],
        'fa-mars'       => ['title_de' => 'Regulaer', 'title_en' => 'Regular', 'default_color' => '#0984e3'],
        'fa-leaf'       => ['title_de' => 'CBD-reich', 'title_en' => 'CBD Rich', 'default_color' => '#00b894'],
        'fa-sun-o'      => ['title_de' => 'Outdoor', 'title_en' => 'Outdoor', 'default_color' => '#fdcb6e'],
        'fa-home'       => ['title_de' => 'Indoor', 'title_en' => 'Indoor', 'default_color' => '#6c5ce7'],
        'fa-bolt'       => ['title_de' => 'Schnelle Bluete', 'title_en' => 'Fast Flowering', 'default_color' => '#e17055'],
        'fa-diamond'    => ['title_de' => 'Premium', 'title_en' => 'Premium', 'default_color' => '#00cec9'],
        'fa-star'       => ['title_de' => 'Bestseller', 'title_en' => 'Bestseller', 'default_color' => '#f39c12'],
        'fa-fire'       => ['title_de' => 'Hoher THC', 'title_en' => 'High THC', 'default_color' => '#d63031'],
        'fa-shield'     => ['title_de' => 'Resistent', 'title_en' => 'Resistant', 'default_color' => '#636e72'],
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
                
                // Parse the HTML table + pictos
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
     * Now also extracts picto icons and cannabis cup trophies.
     *
     * @param string $html The short description HTML
     * @return array Extracted fields (may be empty if no table found)
     */
    public static function parseShortDescription($html) {
        if (empty($html)) return [];
        
        $result = [];
        $is_seed = false;
        $pictos = [];
        $cannabis_cups = 0;
        
        // Use DOMDocument to parse HTML
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', 
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $xpath = new \DOMXPath($doc);
        
        // ============================================================
        // STEP 1: Parse TR-class based data (existing logic)
        // ============================================================
        $rows = $xpath->query('//tr');
        
        foreach ($rows as $tr) {
            $class = $tr->getAttribute('class');
            if (empty($class)) continue;
            
            $classes = preg_split('/\s+/', trim($class));
            
            foreach ($classes as $cls) {
                $cls = strtolower(trim($cls));
                
                if (!isset(self::TR_CLASS_MAP[$cls])) continue;
                
                $field = self::TR_CLASS_MAP[$cls];
                $is_seed = true;
                
                // Get the value from the second TD
                $tds = $tr->getElementsByTagName('td');
                if ($tds->length < 2) continue;
                
                $value = trim(strip_tags($tds->item(1)->textContent));
                if (empty($value) || $value === '-' || $value === 'n/a') continue;
                
                // Map special values
                if ($field === 'gender') {
                    if ($cls === 'fem') {
                        $result['gender'] = 'feminized';
                    } elseif ($cls === 'reg') {
                        $result['gender'] = 'regular';
                    } else {
                        $value_lower = mb_strtolower($value);
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
                        if (!isset($result['gender'])) {
                            $result['gender'] = 'feminized';
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
                        $result['type'] = $value;
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
        
        // ============================================================
        // STEP 2: Parse FontAwesome picto icons
        // ============================================================
        $spans = $xpath->query('//span[contains(@class, "fa ")]');
        $seen_icons = [];
        
        foreach ($spans as $span) {
            $span_class = $span->getAttribute('class');
            $span_title = $span->getAttribute('title');
            $span_style = $span->getAttribute('style');
            
            // Extract the FA icon class (e.g., fa-medkit, fa-trophy)
            $icon_class = '';
            if (preg_match('/fa-([\w-]+)/', $span_class, $m)) {
                // Skip utility classes
                if (in_array($m[0], ['fa-fw', 'fa-lg', 'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-plus'])) {
                    continue;
                }
                $icon_class = $m[0];
            }
            
            if (empty($icon_class)) continue;
            
            // Extract color from style
            $color = '';
            if (preg_match('/color:\s*([^;]+)/', $span_style, $cm)) {
                $color = trim($cm[1]);
            }
            
            // Extract font-size from style
            $size = '1em';
            if (preg_match('/font-size:\s*([^;]+)/', $span_style, $sm)) {
                $size = trim($sm[1]);
            }
            
            // Handle trophies (Cannabis Cups) — count them
            if ($icon_class === 'fa-trophy') {
                $cannabis_cups++;
                continue; // Don't add individual trophies as pictos
            }
            
            // Avoid duplicate pictos (same icon)
            if (isset($seen_icons[$icon_class])) continue;
            $seen_icons[$icon_class] = true;
            
            // Build picto entry
            $picto = [
                'icon' => 'fa ' . $icon_class,
                'color' => $color ?: '#333333',
                'size' => $size,
                'title' => $span_title ?: (isset(self::PICTO_ICON_MAP[$icon_class]) ? self::PICTO_ICON_MAP[$icon_class]['title_de'] : ucfirst(str_replace('fa-', '', $icon_class))),
            ];
            
            $pictos[] = $picto;
            
            // Also detect preset from picto icons
            if ($icon_class === 'fa-tachometer' && !isset($result['flowering_type'])) {
                $result['flowering_type'] = 'autoflower';
                $is_seed = true;
            }
            if ($icon_class === 'fa-medkit') {
                // Medical strain indicator — could be added as custom field
                $is_seed = true;
            }
        }
        
        // ============================================================
        // STEP 3: Also try to extract data from plain text TD values
        // (for tables without TR classes but with label:value pairs)
        // ============================================================
        $all_trs = $xpath->query('//tr');
        foreach ($all_trs as $tr) {
            $class = $tr->getAttribute('class');
            // Skip rows we already processed via TR_CLASS_MAP
            if (!empty($class)) {
                $has_known_class = false;
                foreach (preg_split('/\s+/', trim($class)) as $cls) {
                    if (isset(self::TR_CLASS_MAP[strtolower(trim($cls))])) {
                        $has_known_class = true;
                        break;
                    }
                }
                if ($has_known_class) continue;
            }
            
            $tds = $tr->getElementsByTagName('td');
            if ($tds->length < 2) continue;
            
            $label = mb_strtolower(trim(strip_tags($tds->item(0)->textContent)));
            $value = trim(strip_tags($tds->item(1)->textContent));
            
            if (empty($value) || $value === '-' || $value === 'n/a') continue;
            
            // Try to match label to known fields
            $label_map = [
                'thc' => 'thc',
                'cbd' => 'cbd',
                'kreuzung' => 'cross_genetics',
                'genetics' => 'cross_genetics',
                'genetik' => 'cross_genetics',
                'cross' => 'cross_genetics',
                'bluetezeit' => 'flowering_time',
                'blütezeit' => 'flowering_time',
                'flowering' => 'flowering_time',
                'ertrag indoor' => 'yield_indoor',
                'yield indoor' => 'yield_indoor',
                'ertrag outdoor' => 'yield_outdoor',
                'yield outdoor' => 'yield_outdoor',
                'erntezeit' => 'harvest_time',
                'harvest' => 'harvest_time',
                'höhe indoor' => 'height_indoor',
                'hoehe indoor' => 'height_indoor',
                'height indoor' => 'height_indoor',
                'höhe outdoor' => 'height_outdoor',
                'hoehe outdoor' => 'height_outdoor',
                'height outdoor' => 'height_outdoor',
                'klima' => 'climate',
                'climate' => 'climate',
                'wirkung' => 'effect',
                'effect' => 'effect',
                'geschmack' => 'taste',
                'taste' => 'taste',
                'aroma' => 'taste',
                'sorte' => 'type',
                'type' => 'type',
                'anbau' => 'growing',
                'growing' => 'growing',
            ];
            
            foreach ($label_map as $pattern => $field) {
                if (mb_stripos($label, $pattern) !== false && !isset($result[$field])) {
                    // Apply value mapping for special fields
                    if ($field === 'type') {
                        $vl = mb_strtolower($value);
                        foreach (self::TYPE_MAP as $tp => $tv) {
                            if (mb_stripos($vl, $tp) !== false) { $result[$field] = $tv; break 2; }
                        }
                    }
                    if ($field === 'growing') {
                        $vl = mb_strtolower($value);
                        foreach (self::GROWING_MAP as $gp => $gv) {
                            if (mb_stripos($vl, $gp) !== false) { $result[$field] = $gv; break 2; }
                        }
                    }
                    $result[$field] = $value;
                    $is_seed = true;
                    break;
                }
            }
        }
        
        // ============================================================
        // STEP 4: Determine flowering_type from context
        // ============================================================
        if (!isset($result['flowering_type']) && isset($result['gender'])) {
            if ($result['gender'] === 'autoflower') {
                $result['flowering_type'] = 'autoflower';
                $result['gender'] = 'feminized';
            } else {
                $result['flowering_type'] = 'photoperiod';
            }
        }
        
        // ============================================================
        // STEP 5: Store pictos and cannabis cups
        // ============================================================
        if (!empty($pictos)) {
            $result['pictos'] = $pictos;
        }
        if ($cannabis_cups > 0) {
            $result['cannabis_cups'] = $cannabis_cups;
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
