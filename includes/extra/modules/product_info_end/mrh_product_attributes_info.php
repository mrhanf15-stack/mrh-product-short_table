<?php
/**
 * MRH Product Attributes - Product Info Integration
 * Autoinclude: ~/includes/extra/modules/product_info_end/
 * 
 * Assigns structured product attributes to Smarty variables
 * for use in the product detail page template.
 *
 * Available Smarty variables:
 * - $mrh_badges: Badge HTML
 * - $mrh_mini_table: Full attributes table HTML
 * - $mrh_has_attrs: Boolean
 * - $mrh_is_seed: Boolean
 * - $mrh_attrs: Raw attributes array
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

if (!defined('_VALID_XTC')) { return; }

if (class_exists('MrhProductAttributes') && isset($product->data['products_id'])) {
    
    $mrh_pa_pid = (int)$product->data['products_id'];
    $mrh_pa_lang_id = (int)$_SESSION['languages_id'];
    $mrh_pa_attrs = MrhProductAttributes::getAttributes($mrh_pa_pid, $mrh_pa_lang_id);
    $mrh_pa_min_fields = (int)MrhProductAttributes::getConfig('min_fields_for_display', 3);
    
    if ($mrh_pa_attrs && (int)($mrh_pa_attrs['fields_filled'] ?? 0) >= $mrh_pa_min_fields) {
        $smarty->assign('mrh_badges', MrhProductAttributes::buildBadgeHTML($mrh_pa_attrs));
        $smarty->assign('mrh_mini_table', MrhProductAttributes::buildMiniTable($mrh_pa_attrs, 'detail'));
        $smarty->assign('mrh_has_attrs', true);
        $smarty->assign('mrh_is_seed', (bool)($mrh_pa_attrs['is_seed'] ?? true));
        $smarty->assign('mrh_attrs', $mrh_pa_attrs);
    } else {
        $smarty->assign('mrh_badges', '');
        $smarty->assign('mrh_mini_table', '');
        $smarty->assign('mrh_has_attrs', false);
        $smarty->assign('mrh_is_seed', true);
        $smarty->assign('mrh_attrs', []);
    }
}
