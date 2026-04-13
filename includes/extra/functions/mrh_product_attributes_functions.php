<?php
/**
 * MRH Product Attributes - Frontend Helper Functions
 * Autoinclude: ~/includes/extra/functions/
 * 
 * Provides helper functions for template integration.
 * These functions are available in all frontend PHP files.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

if (!defined('_VALID_XTC')) { return; }

/**
 * Get structured product attributes for a product.
 * Returns null if no data exists (fallback to old short_description).
 *
 * @param int $products_id
 * @param int $language_id (0 = current session language)
 * @return array|null
 */
function mrh_get_product_attributes($products_id, $language_id = 0) {
    if (!class_exists('MrhProductAttributes')) return null;
    return MrhProductAttributes::getAttributes($products_id, $language_id);
}

/**
 * Build badge HTML for a product.
 * Returns empty string if no structured data exists.
 *
 * @param int $products_id
 * @param int $language_id
 * @return string HTML
 */
function mrh_get_product_badges($products_id, $language_id = 0) {
    if (!class_exists('MrhProductAttributes')) return '';
    $attrs = MrhProductAttributes::getAttributes($products_id, $language_id);
    if (empty($attrs)) return '';
    return MrhProductAttributes::buildBadgeHTML($attrs);
}

/**
 * Build mini-table HTML for a product.
 * Returns empty string if no structured data exists.
 *
 * @param int $products_id
 * @param int $language_id
 * @param string $context 'listing'|'box'|'seedfinder'|'compare'|'detail'
 * @return string HTML
 */
function mrh_get_product_mini_table($products_id, $language_id = 0, $context = 'listing') {
    if (!class_exists('MrhProductAttributes')) return '';
    $attrs = MrhProductAttributes::getAttributes($products_id, $language_id);
    if (empty($attrs)) return '';
    return MrhProductAttributes::buildMiniTable($attrs, $context);
}

/**
 * Check if structured attributes exist for a product.
 *
 * @param int $products_id
 * @param int $language_id
 * @return bool
 */
function mrh_has_product_attributes($products_id, $language_id = 0) {
    if (!class_exists('MrhProductAttributes')) return false;
    $attrs = MrhProductAttributes::getAttributes($products_id, $language_id);
    return !empty($attrs) && (int)($attrs['fields_filled'] ?? 0) >= 1;
}
