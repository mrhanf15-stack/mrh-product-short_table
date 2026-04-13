<?php
/**
 * MRH Product Attributes - Product Listing Integration
 * Autoinclude: ~/includes/extra/modules/product_listing_end/
 * 
 * Assigns structured product attributes to Smarty variables
 * for use in listing templates (product_listing_include.html).
 *
 * Available Smarty variables after this hook:
 * - $module_content[x].MRH_BADGES: Badge HTML (picto templatestyle structure)
 * - $module_content[x].MRH_MINI_TABLE: Mini-table HTML
 * - $module_content[x].MRH_HAS_ATTRS: Boolean - has structured data
 * - $module_content[x].MRH_IS_SEED: Boolean - is seed product
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

if (!defined('_VALID_XTC')) { return; }

// Only process if module class is loaded and $module_content exists
if (class_exists('MrhProductAttributes') && isset($module_content) && is_array($module_content)) {
    
    $mrh_pa_lang_id = (int)$_SESSION['languages_id'];
    $mrh_pa_min_fields = (int)MrhProductAttributes::getConfig('min_fields_for_display', 3);
    
    foreach ($module_content as &$mrh_pa_item) {
        if (!isset($mrh_pa_item['PRODUCTS_ID'])) continue;
        
        $mrh_pa_pid = (int)$mrh_pa_item['PRODUCTS_ID'];
        $mrh_pa_attrs = MrhProductAttributes::getAttributes($mrh_pa_pid, $mrh_pa_lang_id);
        
        if ($mrh_pa_attrs && (int)($mrh_pa_attrs['fields_filled'] ?? 0) >= $mrh_pa_min_fields) {
            $mrh_pa_item['MRH_BADGES'] = MrhProductAttributes::buildBadgeHTML($mrh_pa_attrs);
            $mrh_pa_item['MRH_MINI_TABLE'] = MrhProductAttributes::buildMiniTable($mrh_pa_attrs, 'listing');
            $mrh_pa_item['MRH_HAS_ATTRS'] = true;
            $mrh_pa_item['MRH_IS_SEED'] = (bool)($mrh_pa_attrs['is_seed'] ?? true);
        } else {
            $mrh_pa_item['MRH_BADGES'] = '';
            $mrh_pa_item['MRH_MINI_TABLE'] = '';
            $mrh_pa_item['MRH_HAS_ATTRS'] = false;
            $mrh_pa_item['MRH_IS_SEED'] = true; // Default: assume seed
        }
    }
    unset($mrh_pa_item);
}
