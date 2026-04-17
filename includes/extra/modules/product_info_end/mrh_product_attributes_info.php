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
 * @version 1.2.0
 * @note Uses $info_smarty (not $smarty) because product_info.php renders via $info_smarty->fetch()
 * @fix 2026-04-17 Legacy-Badge-Fallback: picto templatestyle aus short_description extrahieren
 */

if (!defined('TABLE_CONFIGURATION')) { return; }

if (class_exists('MrhProductAttributes') && isset($product->data['products_id'])) {
    
    $mrh_pa_pid = (int)$product->data['products_id'];
    $mrh_pa_lang_id = (int)$_SESSION['languages_id'];
    $mrh_pa_attrs = MrhProductAttributes::getAttributes($mrh_pa_pid, $mrh_pa_lang_id);
    $mrh_pa_min_fields = (int)MrhProductAttributes::getConfig('min_fields_for_display', 3);
    
    if ($mrh_pa_attrs && (int)($mrh_pa_attrs['fields_filled'] ?? 0) >= $mrh_pa_min_fields) {
        $info_smarty->assign('mrh_badges', MrhProductAttributes::buildBadgeHTML($mrh_pa_attrs));
        $info_smarty->assign('mrh_mini_table', MrhProductAttributes::buildMiniTable($mrh_pa_attrs, 'detail'));
        $info_smarty->assign('mrh_has_attrs', true);
        $info_smarty->assign('mrh_is_seed', (bool)($mrh_pa_attrs['is_seed'] ?? true));
        $info_smarty->assign('mrh_attrs', $mrh_pa_attrs);
    } else {
        // Fallback: Extract legacy picto badges from short_description
        $mrh_pa_legacy_badges = '';
        if (function_exists('mrh_extract_legacy_badges') && isset($product->data['products_short_description'])) {
            $mrh_pa_legacy_badges = mrh_extract_legacy_badges($product->data['products_short_description']);
        }
        $info_smarty->assign('mrh_badges', $mrh_pa_legacy_badges);
        $info_smarty->assign('mrh_mini_table', '');
        $info_smarty->assign('mrh_has_attrs', !empty($mrh_pa_legacy_badges));
        $info_smarty->assign('mrh_is_seed', true);
        $info_smarty->assign('mrh_attrs', []);
    }
}
