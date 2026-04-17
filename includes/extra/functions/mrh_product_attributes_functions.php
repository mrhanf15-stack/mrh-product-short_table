<?php
/**
 * MRH Product Attributes - Frontend Helper Functions
 * Autoinclude: ~/includes/extra/functions/
 * 
 * Provides helper functions for template integration.
 * These functions are available in all frontend PHP files.
 *
 * @package MRH_Product_Attributes
 * @version 1.6.0
 * @fix 2026-04-17 Duplikat-Filterung direkt in extract, nicht in merge
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
 * Detect which badge types are present in structured badge HTML.
 *
 * Scans the structured badge HTML for mrh-badge-XXX classes and returns
 * an array of base type names (fem, auto, photo, reg, medical, cup).
 *
 * @param string $struct_badge_html HTML from MrhProductAttributes::buildBadgeHTML()
 * @return array List of type strings, e.g. ['fem', 'auto', 'cup']
 */
function mrh_detect_struct_badge_types($struct_badge_html) {
    $types = [];
    if (empty($struct_badge_html)) return $types;
    
    // Match mrh-badge-XXX but NOT mrh-badge-picto-XXX or mrh-badge-bar
    if (preg_match_all('/mrh-badge-(?!picto-|bar)(\w+)/', $struct_badge_html, $m)) {
        $types = array_unique($m[1]);
    }
    return $types;
}

/**
 * Extract legacy picto badges from products_short_description HTML.
 *
 * Parses <div/span class="picto templatestyle"> elements from the raw
 * short description. Filters by CONTENT, not by class "off":
 * - Skip if text content is only "HIERDASICON" (placeholder)
 * - Skip if content is empty / &nbsp; only (no icons)
 * - KEEP if content has real FA icons (trophy, tachometer, etc.)
 *   even when the wrapper has class "off"
 * - Skip badge types that are already in $exclude_types (from structured attrs)
 *
 * @param string $short_description Raw products_short_description HTML
 * @param array  $exclude_types     Badge types to exclude (e.g. ['fem','auto','cup'])
 * @return string Badge HTML or empty string
 * @version 1.6.0 - Added $exclude_types parameter for duplicate prevention
 */
function mrh_extract_legacy_badges($short_description, $exclude_types = []) {
    if (empty($short_description)) return '';

    // Quick check: does it contain picto + templatestyle at all?
    if (stripos($short_description, 'picto') === false || stripos($short_description, 'templatestyle') === false) {
        return '';
    }

    $badges = [];

    // Match <div class="...picto...templatestyle...">...all content...</div>
    // IMPORTANT: Use GREEDY (.+) not lazy (.+?) because the inner content
    // contains multiple <span>...</span> elements and we need ALL of them
    $pattern_div = '/<div\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+)<\/div>/si';

    $all_matches = [];
    if (preg_match_all($pattern_div, $short_description, $div_matches, PREG_SET_ORDER)) {
        $all_matches = array_merge($all_matches, $div_matches);
    }

    // Fallback: try span wrappers if no div matches found
    if (empty($all_matches)) {
        $pattern_span = '/<span\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+)<\/span>/si';
        if (preg_match_all($pattern_span, $short_description, $span_matches, PREG_SET_ORDER)) {
            $all_matches = array_merge($all_matches, $span_matches);
        }
    }

    if (empty($all_matches)) {
        return '';
    }

    foreach ($all_matches as $match) {
        $inner = trim($match[2]);

        // Filter by CONTENT:
        // 1. Skip placeholder text "HIERDASICON"
        $text_only = strip_tags($inner);
        $text_clean = trim(html_entity_decode($text_only, ENT_QUOTES, 'UTF-8'));
        if (stripos($text_clean, 'HIERDASICON') !== false) {
            continue;
        }

        // 2. Skip empty / &nbsp; only content WITHOUT any icon spans
        $text_check = str_replace(['&nbsp;', ' ', "\t", "\n", "\r"], '', $text_clean);
        if (empty($text_check) && stripos($inner, '<span') === false) {
            continue;
        }

        // 3. Must contain at least one FA icon span to be a valid badge
        if (stripos($inner, 'fa-') === false) {
            continue;
        }

        // Normalize FA4 to FA6/7
        $normalized = $inner;
        $normalized = preg_replace('/\bfa\s+fa-fw\b/', 'fa-solid fa-fw', $normalized);
        $normalized = str_replace('fa-tachometer', 'fa-gauge-high', $normalized);
        $normalized = preg_replace('/\s{2,}/', ' ', $normalized);
        $normalized = str_replace('" >', '">', $normalized);

        // Determine badge type from icon classes
        $badge_type = 'legacy';
        if (stripos($normalized, 'fa-trophy') !== false) {
            $badge_type = 'cup';
        } elseif (stripos($normalized, 'fa-gauge-high') !== false) {
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

        // v1.6.0: Skip this badge if its type is already in the structured badges
        if (!empty($exclude_types) && $badge_type !== 'legacy' && in_array($badge_type, $exclude_types)) {
            continue;
        }

        // Extract title from the first inner span for the wrapper
        $title = '';
        if (preg_match('/title="([^"]*)"/i', $normalized, $title_match)) {
            $title = $title_match[1];
        }

        // Build the badge in the standard mrh structure
        $badges[] = '<span class="mrh-type-badge mrh-badge-picto-' . $badge_type . '" title="' . htmlspecialchars($title) . '">' . $normalized . '</span>';
    }

    if (empty($badges)) return '';

    // Wrap in the standard picto templatestyle structure
    return '<span class="picto templatestyle"><span class="mrh-badge-bar">'
        . implode('', $badges)
        . '</span></span>';
}

/**
 * Merge structured badge HTML with legacy badge HTML.
 *
 * Simple concatenation: structured badges first, then legacy badges.
 * Duplicate filtering is handled by mrh_extract_legacy_badges() via $exclude_types.
 *
 * @param string $struct_html  Badge HTML from buildBadgeHTML() (structured DB attrs)
 * @param string $legacy_html  Badge HTML from mrh_extract_legacy_badges() (short_description)
 * @return string Merged badge HTML or empty string
 * @version 1.6.0 - Simplified: just concatenate, filtering done in extract
 */
function mrh_merge_badge_html($struct_html, $legacy_html) {
    if (empty($struct_html) && empty($legacy_html)) return '';
    if (empty($legacy_html)) return $struct_html;
    if (empty($struct_html)) return $legacy_html;
    
    // Both exist: extract inner badge-bar content from both
    $struct_inner = '';
    if (preg_match('/<span class="mrh-badge-bar">(.*)<\/span>\s*<\/span>/si', $struct_html, $m)) {
        $struct_inner = $m[1];
    }
    $legacy_inner = '';
    if (preg_match('/<span class="mrh-badge-bar">(.*)<\/span>\s*<\/span>/si', $legacy_html, $m)) {
        $legacy_inner = $m[1];
    }
    
    if (empty($struct_inner) && empty($legacy_inner)) return '';
    if (empty($legacy_inner)) return $struct_html;
    if (empty($struct_inner)) return $legacy_html;
    
    // Combine into single badge-bar
    return '<span class="picto templatestyle"><span class="mrh-badge-bar">'
        . $struct_inner . $legacy_inner
        . '</span></span>';
}

endif; // TABLE_CONFIGURATION
