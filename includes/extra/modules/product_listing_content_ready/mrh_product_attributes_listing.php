<?php
/**
 * MRH Product Attributes - Product Listing Integration
 * Autoinclude: ~/includes/extra/modules/product_listing_content_ready/
 *
 * WICHTIG: Diese Datei MUSS im Hook "product_listing_content_ready" liegen,
 * NICHT in "product_listing_end"! Der Hook product_listing_end wird NACH dem
 * $module_smarty->assign('module_content', ...) aufgerufen, sodass Aenderungen
 * an $module_content dort keine Wirkung mehr haben.
 *
 * Reihenfolge in includes/modules/product_listing.php:
 *   1. product_listing_begin     -> vor DB-Query
 *   2. $module_content wird befuellt (while-Schleife)
 *   3. product_listing_content_ready -> HIER: $module_content anreichern
 *   4. $module_smarty->assign('module_content', $module_content)
 *   5. product_listing_end       -> zu spaet fuer $module_content
 *
 * Available Smarty variables after this hook:
 * - $module_content[x].MRH_BADGES: Badge HTML (picto templatestyle structure)
 * - $module_content[x].MRH_MINI_TABLE: Mini-table HTML
 * - $module_content[x].MRH_HAS_ATTRS: Boolean - has structured data
 * - $module_content[x].MRH_IS_SEED: Boolean - is seed product
 *
 * @package MRH_Product_Attributes
 * @version 1.3.0
 * @fix 2026-04-15 Hook von product_listing_end nach product_listing_content_ready verschoben
 * @fix 2026-04-17 Legacy-Badges IMMER zusaetzlich zu strukturierten Badges anzeigen
 */

if (!defined('TABLE_CONFIGURATION')) { return; }

// Only process if module class is loaded and $module_content exists
if (class_exists('MrhProductAttributes') && isset($module_content) && is_array($module_content)) {
    
    $mrh_pa_lang_id = (int)$_SESSION['languages_id'];
    $mrh_pa_min_fields = (int)MrhProductAttributes::getConfig('min_fields_for_display', 3);
    
    foreach ($module_content as &$mrh_pa_item) {
        if (!isset($mrh_pa_item['PRODUCTS_ID'])) continue;
        
        $mrh_pa_pid = (int)$mrh_pa_item['PRODUCTS_ID'];
        $mrh_pa_attrs = MrhProductAttributes::getAttributes($mrh_pa_pid, $mrh_pa_lang_id);
        
        // 1. Structured badges from DB (if enough fields filled)
        $mrh_pa_struct_badges = '';
        if ($mrh_pa_attrs && (int)($mrh_pa_attrs['fields_filled'] ?? 0) >= $mrh_pa_min_fields) {
            $mrh_pa_struct_badges = MrhProductAttributes::buildBadgeHTML($mrh_pa_attrs);
            $mrh_pa_item['MRH_MINI_TABLE'] = MrhProductAttributes::buildMiniTable($mrh_pa_attrs, 'listing');
            $mrh_pa_item['MRH_IS_SEED'] = (bool)($mrh_pa_attrs['is_seed'] ?? true);
        } else {
            $mrh_pa_item['MRH_MINI_TABLE'] = '';
            $mrh_pa_item['MRH_IS_SEED'] = true;
        }
        
        // 2. Legacy badges from short_description (ALWAYS extract)
        $mrh_pa_legacy_badges = '';
        if (function_exists('mrh_extract_legacy_badges') && !empty($mrh_pa_item['PRODUCTS_SHORT_DESCRIPTION'])) {
            $mrh_pa_legacy_badges = mrh_extract_legacy_badges($mrh_pa_item['PRODUCTS_SHORT_DESCRIPTION']);
        }
        
        // 3. Merge: structured first, then legacy (avoid duplicates)
        $mrh_pa_item['MRH_BADGES'] = mrh_merge_badge_html($mrh_pa_struct_badges, $mrh_pa_legacy_badges);
        $mrh_pa_item['MRH_HAS_ATTRS'] = !empty($mrh_pa_item['MRH_BADGES']) || !empty($mrh_pa_item['MRH_MINI_TABLE']);
    }
    unset($mrh_pa_item);
}
