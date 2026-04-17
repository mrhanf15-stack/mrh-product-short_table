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

if (defined('TABLE_CONFIGURATION')):

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

/**
 * Extract legacy picto badges from products_short_description HTML.
 *
 * Parses <div/span class="picto templatestyle"> elements from the raw
 * short description. Skips elements with class "off" and placeholder
 * text "HIERDASICON". Returns cleaned badge HTML wrapped in the
 * standard mrh-badge-bar structure.
 *
 * @param string $short_description Raw products_short_description HTML
 * @return string Badge HTML or empty string
 */
function mrh_extract_legacy_badges($short_description) {
    if (empty($short_description)) return '';

    // Quick check: does it contain picto + templatestyle at all?
    if (stripos($short_description, 'picto') === false || stripos($short_description, 'templatestyle') === false) {
        return '';
    }

    $badges = [];

    // Match <div|span class="...picto...templatestyle...">...content...</div|span>
    // Uses a regex that handles both div and span, and captures class + inner HTML
    $pattern = '/<(?:div|span)\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+?)<\/(?:div|span)>/si';

    if (!preg_match_all($pattern, $short_description, $matches, PREG_SET_ORDER)) {
        return '';
    }

    foreach ($matches as $match) {
        $classes = $match[1];
        $inner   = $match[2];

        // Skip elements with class "off"
        if (preg_match('/\boff\b/', $classes)) {
            continue;
        }

        // Skip placeholder text "HIERDASICON"
        $text_only = strip_tags($inner);
        $text_clean = trim(html_entity_decode($text_only, ENT_QUOTES, 'UTF-8'));
        if (stripos($text_clean, 'HIERDASICON') !== false) {
            continue;
        }

        // Skip empty / &nbsp; only content
        $text_check = str_replace(['&nbsp;', ' ', "\t", "\n", "\r"], '', $text_clean);
        if (empty($text_check) && stripos($inner, '<span') === false) {
            continue;
        }

        // The inner HTML contains FA icon spans – keep them as-is
        // Normalize old FA4 classes to FA6/7 format:
        //   "fa fa-fw fa-trophy" => "fa-solid fa-fw fa-trophy"
        //   "fa fa-fw fa-tachometer" => "fa-solid fa-fw fa-gauge-high"
        $normalized = $inner;

        // Replace legacy "fa " prefix with "fa-solid " (but not "fa-" which is already correct)
        $normalized = preg_replace('/\bfa\s+fa-fw\b/', 'fa-solid fa-fw', $normalized);

        // Replace deprecated fa-tachometer with fa-gauge-high (FA6 equivalent)
        $normalized = str_replace('fa-tachometer', 'fa-gauge-high', $normalized);

        // Remove legacy extra classes like "pukal", "shortfongc" that are not needed
        $normalized = preg_replace('/\b(pukal|shortfongc)\b/', '', $normalized);
        // Clean up double spaces
        $normalized = preg_replace('/\s{2,}/', ' ', $normalized);
        $normalized = str_replace('" >', '">', $normalized);

        // Wrap each icon in mrh-type-badge structure
        // Extract title from the inner span
        $title = '';
        if (preg_match('/title="([^"]*)"/i', $normalized, $title_match)) {
            $title = $title_match[1];
        }

        // Determine badge type from icon class
        $badge_type = 'legacy';
        if (stripos($normalized, 'fa-trophy') !== false) {
            $badge_type = 'cup';
        } elseif (stripos($normalized, 'fa-gauge-high') !== false || stripos($normalized, 'fa-tachometer') !== false) {
            $badge_type = 'auto';
        } elseif (stripos($normalized, 'fa-venus') !== false) {
            $badge_type = 'fem';
        } elseif (stripos($normalized, 'fa-mars') !== false) {
            $badge_type = 'reg';
        } elseif (stripos($normalized, 'fa-sun') !== false) {
            $badge_type = 'photo';
        } elseif (stripos($normalized, 'fa-medkit') !== false || stripos($normalized, 'fa-kit-medical') !== false) {
            $badge_type = 'medical';
        }

        // Build the badge in the standard mrh structure
        $badges[] = '<span class="mrh-type-badge mrh-badge-' . $badge_type . '" title="' . htmlspecialchars($title) . '">' . $normalized . '</span>';
    }

    if (empty($badges)) return '';

    // Wrap in the standard picto templatestyle structure
    return '<span class="picto templatestyle"><span class="mrh-badge-bar">'
        . implode('', $badges)
        . '</span></span>';
}

endif; // TABLE_CONFIGURATION
