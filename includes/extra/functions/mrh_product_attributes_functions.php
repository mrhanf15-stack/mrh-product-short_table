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
 * short description. Filters by CONTENT, not by class "off":
 * - Skip if text content is only "HIERDASICON" (placeholder)
 * - Skip if content is empty / &nbsp; only (no icons)
 * - KEEP if content has real FA icons (trophy, tachometer, etc.)
 *   even when the wrapper has class "off"
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

    // Match <div class="...picto...templatestyle...">...all content...</div>
    // and  <span class="...picto...templatestyle...">...all content...</span>
    // Two separate patterns because inner content contains nested spans
    // which would break a single pattern with </(?:div|span)>
    $pattern_div  = '/<div\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+?)<\/div>/si';
    $pattern_span = '/<span\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+?)<\/span>\s*<\/span>/si';
    
    // Try div first (most common in legacy data), then span
    $all_matches = [];
    if (preg_match_all($pattern_div, $short_description, $div_matches, PREG_SET_ORDER)) {
        $all_matches = array_merge($all_matches, $div_matches);
    }
    // For span wrappers: match the outermost span.picto.templatestyle
    // by finding spans that are NOT nested inside another picto span
    $pattern_span2 = '/<span\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(.+?)(?=<\/span>\s*(?:<\/|$))/si';
    // Actually simpler: just use the div pattern result. Most legacy data uses div.
    // If span-wrapped, the existing pattern_div won't match, so add span fallback:
    if (empty($all_matches)) {
        // Fallback: try matching span wrappers by finding the closing </span> that
        // is NOT preceded by another opening <span (i.e., the outermost one)
        $pattern_span_outer = '/<span\s+class="([^"]*picto[^"]*templatestyle[^"]*)">(((?!<span\s+class="[^"]*picto[^"]*templatestyle).)*)<\/span>/si';
        if (preg_match_all($pattern_span_outer, $short_description, $span_matches, PREG_SET_ORDER)) {
            $all_matches = array_merge($all_matches, $span_matches);
        }
    }
    
    $matches = $all_matches;
    $pattern = $pattern_div; // kept for reference

    if (empty($matches)) {
        return '';
    }

    foreach ($matches as $match) {
        $classes = $match[1];
        $inner   = trim($match[2]);

        // Filter by CONTENT, not by class "off":
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

        // The inner HTML contains FA icon spans - normalize FA4 to FA6/7:
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

        // Extract title from the first inner span for the wrapper
        $title = '';
        if (preg_match('/title="([^"]*)"/i', $normalized, $title_match)) {
            $title = $title_match[1];
        }

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

        // Build the badge in the standard mrh structure
        $badges[] = '<span class="mrh-type-badge mrh-badge-' . $badge_type . '" title="' . htmlspecialchars($title) . '">' . $normalized . '</span>';
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
 * Combines both badge sources into a single picto templatestyle wrapper.
 * Avoids duplicates by checking if the legacy badge type (cup, auto, etc.)
 * is already present in the structured badges.
 *
 * @param string $struct_html  Badge HTML from buildBadgeHTML() (structured DB attrs)
 * @param string $legacy_html  Badge HTML from mrh_extract_legacy_badges() (short_description)
 * @return string Merged badge HTML or empty string
 */
function mrh_merge_badge_html($struct_html, $legacy_html) {
    // If both are empty, return empty
    if (empty($struct_html) && empty($legacy_html)) return '';
    
    // If only one exists, return it directly
    if (empty($legacy_html)) return $struct_html;
    if (empty($struct_html)) return $legacy_html;
    
    // Both exist: extract inner badges from both and merge
    // Extract the inner content of <span class="mrh-badge-bar">...</span>
    $struct_inner = '';
    if (preg_match('/<span class="mrh-badge-bar"[^>]*>(.*)<\/span>/si', $struct_html, $m)) {
        $struct_inner = $m[1];
    }
    $legacy_inner = '';
    if (preg_match('/<span class="mrh-badge-bar"[^>]*>(.*)<\/span>/si', $legacy_html, $m)) {
        $legacy_inner = $m[1];
    }
    
    if (empty($struct_inner) && empty($legacy_inner)) return '';
    if (empty($legacy_inner)) return $struct_html;
    if (empty($struct_inner)) return $legacy_html;
    
    // Check for duplicate badge types: extract mrh-badge-XXX classes from structured
    $struct_types = [];
    if (preg_match_all('/mrh-badge-(\w+)/', $struct_inner, $type_matches)) {
        $struct_types = array_unique($type_matches[1]);
    }
    
    // Filter legacy badges: only keep those whose type is NOT in structured
    $filtered_legacy = '';
    if (preg_match_all('/<span class="mrh-type-badge[^"]*"[^>]*>.*?<\/span>/si', $legacy_inner, $legacy_badges)) {
        foreach ($legacy_badges[0] as $badge) {
            // Extract badge type
            $badge_type = '';
            if (preg_match('/mrh-badge-(\w+)/', $badge, $bt)) {
                $badge_type = $bt[1];
            }
            // Skip if this type already exists in structured badges
            // Exception: 'cup' badges can have different counts, so always add
            if ($badge_type !== 'cup' && in_array($badge_type, $struct_types)) {
                continue;
            }
            // For cup badges: skip if structured already has cup
            if ($badge_type === 'cup' && in_array('cup', $struct_types)) {
                continue;
            }
            $filtered_legacy .= $badge;
        }
    }
    
    if (empty($filtered_legacy)) return $struct_html;
    
    // Combine: structured badges + filtered legacy badges in one wrapper
    return '<span class="picto templatestyle"><span class="mrh-badge-bar">'
        . $struct_inner . $filtered_legacy
        . '</span></span>';
}

endif; // TABLE_CONFIGURATION
