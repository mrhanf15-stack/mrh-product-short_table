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
 * @package MRH_Product_Attributes
 * @version 1.0.0
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
        
        // Get the current language_id from the save loop
        // In Modified Shop, the description save loop uses $languages[$i]['id']
        $mrh_pa_current_lang_id = isset($languages_id) ? (int)$languages_id : 0;
        
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
                
                // Save
                MrhProductAttributes::saveAttributes($mrh_pa_pid, $lang_id, $data, 'manual');
            }
        }
    }
}
