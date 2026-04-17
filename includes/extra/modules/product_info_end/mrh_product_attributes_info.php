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
 * @version 1.3.0
 * @note Uses $info_smarty (not $smarty) because product_info.php renders via $info_smarty->fetch()
 * @fix 2026-04-17 Legacy-Badges IMMER zusaetzlich zu strukturierten Badges anzeigen
 */

if (!defined('TABLE_CONFIGURATION')) { return; }

if (class_exists('MrhProductAttributes') && isset($product->data['products_id'])) {
    
    $mrh_pa_pid = (int)$product->data['products_id'];
    $mrh_pa_lang_id = (int)$_SESSION['languages_id'];
    $mrh_pa_attrs = MrhProductAttributes::getAttributes($mrh_pa_pid, $mrh_pa_lang_id);
    $mrh_pa_min_fields = (int)MrhProductAttributes::getConfig('min_fields_for_display', 3);
    
    // 1. Structured badges + table from DB
    $mrh_pa_struct_badges = '';
    $mrh_pa_mini_table = '';
    $mrh_pa_is_seed = true;
    $mrh_pa_attrs_data = [];
    
    if ($mrh_pa_attrs && (int)($mrh_pa_attrs['fields_filled'] ?? 0) >= $mrh_pa_min_fields) {
        $mrh_pa_struct_badges = MrhProductAttributes::buildBadgeHTML($mrh_pa_attrs);
        $mrh_pa_mini_table = MrhProductAttributes::buildMiniTable($mrh_pa_attrs, 'detail');
        $mrh_pa_is_seed = (bool)($mrh_pa_attrs['is_seed'] ?? true);
        $mrh_pa_attrs_data = $mrh_pa_attrs;
    }
    
    // 2. Legacy badges from short_description (ALWAYS extract)
    $mrh_pa_legacy_badges = '';
    if (function_exists('mrh_extract_legacy_badges') && isset($product->data['products_short_description'])) {
        $mrh_pa_legacy_badges = mrh_extract_legacy_badges($product->data['products_short_description']);
    }
    
    // 3. Merge: structured first, then legacy
    $mrh_pa_merged_badges = mrh_merge_badge_html($mrh_pa_struct_badges, $mrh_pa_legacy_badges);
    
    $info_smarty->assign('mrh_badges', $mrh_pa_merged_badges);
    $info_smarty->assign('mrh_mini_table', $mrh_pa_mini_table);
    $info_smarty->assign('mrh_has_attrs', !empty($mrh_pa_merged_badges) || !empty($mrh_pa_mini_table));
    $info_smarty->assign('mrh_is_seed', $mrh_pa_is_seed);
    $info_smarty->assign('mrh_attrs', $mrh_pa_attrs_data);
}
