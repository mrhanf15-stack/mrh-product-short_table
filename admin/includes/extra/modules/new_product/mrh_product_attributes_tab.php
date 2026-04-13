<?php
/**
 * MRH Product Attributes - categories.php Product Edit Hook
 * Autoinclude: ~/admin/includes/extra/modules/new_product/
 * 
 * Injects the "Eigenschaften (MRH)" tab into the product edit form.
 *
 * Features v1.2.0:
 * - 4 Preset buttons (Feminisiert | Autoflowering | Regulaer | Auto Regulaer)
 * - Language tabs with all standard fields
 * - FontAwesome Icon Editor with SEARCHABLE LIBRARY (730 icons)
 * - Inline editing of existing icons (color + size)
 * - Cannabis Cup trophy count
 * - AI fill button (single product)
 * - Auto-preset detection from loaded data
 *
 * @package MRH_Product_Attributes
 * @version 1.2.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Load module class
if (!class_exists('MrhProductAttributes')) {
    $mrh_pa_class = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_product_attributes.php';
    if (file_exists($mrh_pa_class)) {
        require_once($mrh_pa_class);
    }
}

// Self-install check (runs once per admin session)
if (class_exists('MrhProductAttributes')) {
    MrhProductAttributes::checkAndInstall();
}

// Only show for existing products (not new ones without ID)
$mrh_pa_products_id = isset($pInfo->products_id) ? (int)$pInfo->products_id : 0;
if ($mrh_pa_products_id == 0 && isset($_GET['pID'])) {
    $mrh_pa_products_id = (int)$_GET['pID'];
}

// Load existing attributes for all languages
$mrh_pa_all_attrs = [];
if ($mrh_pa_products_id > 0 && class_exists('MrhProductAttributes')) {
    $mrh_pa_all_attrs = MrhProductAttributes::getAllLanguageAttributes($mrh_pa_products_id);
}

// Get languages
$mrh_pa_languages = [];
$mrh_pa_lang_q = xtc_db_query("SELECT languages_id, name, code, image, directory FROM languages ORDER BY sort_order");
while ($mrh_pa_lang_row = xtc_db_fetch_array($mrh_pa_lang_q)) {
    $mrh_pa_languages[] = $mrh_pa_lang_row;
}

// Standard fields definition
$mrh_pa_fields = [
    'gender'         => ['label' => defined('MRH_PA_FIELD_GENDER') ? MRH_PA_FIELD_GENDER : 'Geschlecht', 'type' => 'select', 'priority' => false],
    'flowering_type' => ['label' => defined('MRH_PA_FIELD_FLOWERING_TYPE') ? MRH_PA_FIELD_FLOWERING_TYPE : 'Bluetentyp', 'type' => 'select', 'priority' => false],
    'type'           => ['label' => defined('MRH_PA_FIELD_TYPE') ? MRH_PA_FIELD_TYPE : 'Sorte', 'type' => 'select', 'priority' => 'prio'],
    'thc'            => ['label' => defined('MRH_PA_FIELD_THC') ? MRH_PA_FIELD_THC : 'THC', 'type' => 'text', 'priority' => 'prio'],
    'cbd'            => ['label' => defined('MRH_PA_FIELD_CBD') ? MRH_PA_FIELD_CBD : 'CBD', 'type' => 'text', 'priority' => 'prio'],
    'cross_genetics' => ['label' => defined('MRH_PA_FIELD_CROSS') ? MRH_PA_FIELD_CROSS : 'Kreuzung', 'type' => 'text', 'priority' => 'alt'],
    'flowering_time' => ['label' => defined('MRH_PA_FIELD_FLOWERING_TIME') ? MRH_PA_FIELD_FLOWERING_TIME : 'Bluetezeit', 'type' => 'text', 'priority' => 'alt'],
    'yield_indoor'   => ['label' => defined('MRH_PA_FIELD_YIELD_INDOOR') ? MRH_PA_FIELD_YIELD_INDOOR : 'Ertrag Indoor', 'type' => 'text', 'priority' => 'alt'],
    'harvest_time'   => ['label' => defined('MRH_PA_FIELD_HARVEST_TIME') ? MRH_PA_FIELD_HARVEST_TIME : 'Erntezeit', 'type' => 'text', 'priority' => 'alt'],
    'yield_outdoor'  => ['label' => defined('MRH_PA_FIELD_YIELD_OUTDOOR') ? MRH_PA_FIELD_YIELD_OUTDOOR : 'Ertrag Outdoor', 'type' => 'text', 'priority' => false],
    'height_indoor'  => ['label' => defined('MRH_PA_FIELD_HEIGHT_INDOOR') ? MRH_PA_FIELD_HEIGHT_INDOOR : 'Hoehe Indoor', 'type' => 'text', 'priority' => false],
    'height_outdoor' => ['label' => defined('MRH_PA_FIELD_HEIGHT_OUTDOOR') ? MRH_PA_FIELD_HEIGHT_OUTDOOR : 'Hoehe Outdoor', 'type' => 'text', 'priority' => false],
    'climate'        => ['label' => defined('MRH_PA_FIELD_CLIMATE') ? MRH_PA_FIELD_CLIMATE : 'Klima', 'type' => 'text', 'priority' => false],
    'effect'         => ['label' => defined('MRH_PA_FIELD_EFFECT') ? MRH_PA_FIELD_EFFECT : 'Wirkung', 'type' => 'text', 'priority' => false],
    'taste'          => ['label' => defined('MRH_PA_FIELD_TASTE') ? MRH_PA_FIELD_TASTE : 'Geschmack', 'type' => 'text', 'priority' => false],
    'growing'        => ['label' => defined('MRH_PA_FIELD_GROWING') ? MRH_PA_FIELD_GROWING : 'Anbau', 'type' => 'select', 'priority' => false],
];

// Select options
$mrh_pa_select_options = [
    'gender' => [
        '' => '-- Bitte waehlen --',
        'feminized' => defined('MRH_PA_GENDER_FEMINIZED') ? MRH_PA_GENDER_FEMINIZED : 'Feminisiert',
        'regular' => defined('MRH_PA_GENDER_REGULAR') ? MRH_PA_GENDER_REGULAR : 'Regulaer',
        'autoflower' => defined('MRH_PA_GENDER_AUTOFLOWER') ? MRH_PA_GENDER_AUTOFLOWER : 'Autoflowering',
    ],
    'flowering_type' => [
        '' => '-- Bitte waehlen --',
        'photoperiod' => defined('MRH_PA_FLOWERING_PHOTOPERIOD') ? MRH_PA_FLOWERING_PHOTOPERIOD : 'Photoperiodisch',
        'autoflower' => defined('MRH_PA_FLOWERING_AUTOFLOWER') ? MRH_PA_FLOWERING_AUTOFLOWER : 'Autoflowering',
    ],
    'type' => [
        '' => '-- Bitte waehlen --',
        'indica' => defined('MRH_PA_TYPE_INDICA') ? MRH_PA_TYPE_INDICA : 'Indica',
        'sativa' => defined('MRH_PA_TYPE_SATIVA') ? MRH_PA_TYPE_SATIVA : 'Sativa',
        'hybrid' => defined('MRH_PA_TYPE_HYBRID') ? MRH_PA_TYPE_HYBRID : 'Hybrid',
        'indica_dom' => defined('MRH_PA_TYPE_INDICA_DOM') ? MRH_PA_TYPE_INDICA_DOM : 'Indica-dominant',
        'sativa_dom' => defined('MRH_PA_TYPE_SATIVA_DOM') ? MRH_PA_TYPE_SATIVA_DOM : 'Sativa-dominant',
    ],
    'growing' => [
        '' => '-- Bitte waehlen --',
        'indoor' => defined('MRH_PA_GROWING_INDOOR') ? MRH_PA_GROWING_INDOOR : 'Indoor',
        'outdoor' => defined('MRH_PA_GROWING_OUTDOOR') ? MRH_PA_GROWING_OUTDOOR : 'Outdoor',
        'greenhouse' => defined('MRH_PA_GROWING_GREENHOUSE') ? MRH_PA_GROWING_GREENHOUSE : 'Gewaechshaus',
        'all' => defined('MRH_PA_GROWING_ALL') ? MRH_PA_GROWING_ALL : 'Indoor/Outdoor',
    ],
];

// Decode pictos from first language for initial display
$mrh_pa_first_attr = !empty($mrh_pa_all_attrs) ? reset($mrh_pa_all_attrs) : [];
$mrh_pa_pictos = [];
if (!empty($mrh_pa_first_attr['pictos'])) {
    $mrh_pa_pictos = json_decode($mrh_pa_first_attr['pictos'], true) ?: [];
}
$mrh_pa_cups = (int)($mrh_pa_first_attr['cannabis_cups'] ?? 0);

// Auto-detect preset from loaded data
$mrh_pa_detected_preset = '';
if (!empty($mrh_pa_first_attr)) {
    $g = $mrh_pa_first_attr['gender'] ?? '';
    $f = $mrh_pa_first_attr['flowering_type'] ?? '';
    if ($g === 'feminized' && $f === 'autoflower') $mrh_pa_detected_preset = 'autoflower';
    elseif ($g === 'feminized' && $f === 'photoperiod') $mrh_pa_detected_preset = 'feminized';
    elseif ($g === 'regular' && $f === 'autoflower') $mrh_pa_detected_preset = 'auto_regular';
    elseif ($g === 'regular' && $f === 'photoperiod') $mrh_pa_detected_preset = 'regular';
    elseif ($g === 'regular') $mrh_pa_detected_preset = 'regular';
    elseif ($g === 'feminized') $mrh_pa_detected_preset = 'feminized';
}
?>

<!-- FontAwesome 4.7 CDN (required for icon previews) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />

<!-- MRH Product Attributes Tab v1.2.0 -->
<style>
#mrh-pa-container { margin: 10px 0; padding: 0; }
#mrh-pa-container .mrh-pa-header { 
    background: #2c3e50; color: #fff; padding: 12px 20px; 
    border-radius: 6px 6px 0 0; display: flex; align-items: center; justify-content: space-between;
}
#mrh-pa-container .mrh-pa-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
#mrh-pa-container .mrh-pa-body { 
    border: 1px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; 
    padding: 15px; background: #fafafa; 
}
#mrh-pa-container .mrh-pa-preset-tabs { 
    display: flex; gap: 0; margin-bottom: 15px; 
    border: 2px solid #2c3e50; border-radius: 6px; overflow: hidden;
}
#mrh-pa-container .mrh-pa-preset-tab {
    flex: 1; padding: 10px 12px; border: none; border-right: 1px solid #dee2e6;
    background: #fff; cursor: pointer; font-size: 13px; font-weight: 600;
    transition: all 0.2s; text-align: center; white-space: nowrap;
}
#mrh-pa-container .mrh-pa-preset-tab:last-child { border-right: none; }
#mrh-pa-container .mrh-pa-preset-tab:hover { background: #e8f5e9; }
#mrh-pa-container .mrh-pa-preset-tab.active { background: #27ae60; color: #fff; }
#mrh-pa-container .mrh-pa-preset-tab .fa { margin-right: 4px; }
#mrh-pa-container .mrh-pa-lang-tabs {
    display: flex; gap: 3px; margin-bottom: 10px; border-bottom: 2px solid #27ae60;
}
#mrh-pa-container .mrh-pa-lang-tab {
    padding: 6px 14px; border: 1px solid #ccc; border-bottom: none;
    border-radius: 4px 4px 0 0; background: #fff; cursor: pointer; font-size: 12px;
}
#mrh-pa-container .mrh-pa-lang-tab.active { background: #27ae60; color: #fff; border-color: #27ae60; }
#mrh-pa-container .mrh-pa-lang-panel { display: none; }
#mrh-pa-container .mrh-pa-lang-panel.active { display: block; }
#mrh-pa-container .mrh-pa-field-row {
    display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 6px 0;
    border-bottom: 1px solid #eee;
}
#mrh-pa-container .mrh-pa-field-row.priority { background: #f0fdf4; padding: 6px 8px; border-radius: 4px; }
#mrh-pa-container .mrh-pa-field-row.alt-priority { background: #f0f4fd; padding: 6px 8px; border-radius: 4px; }
#mrh-pa-container .mrh-pa-field-label { width: 180px; font-weight: 600; font-size: 13px; flex-shrink: 0; }
#mrh-pa-container .mrh-pa-field-label .priority-star { color: #27ae60; margin-left: 4px; }
#mrh-pa-container .mrh-pa-field-label .alt-priority-star { color: #3498db; margin-left: 4px; }
#mrh-pa-container .mrh-pa-field-input { flex: 1; }
#mrh-pa-container .mrh-pa-field-input input,
#mrh-pa-container .mrh-pa-field-input select {
    width: 100%; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px;
    font-size: 13px; box-sizing: border-box;
}
#mrh-pa-container .mrh-pa-field-input select { background: #fff; }
#mrh-pa-container .mrh-pa-seed-toggle {
    display: flex; align-items: center; gap: 10px; margin-bottom: 15px;
    padding: 10px; background: #fff3cd; border-radius: 4px; border: 1px solid #ffc107;
}
#mrh-pa-container .mrh-pa-actions {
    display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;
}
#mrh-pa-container .mrh-pa-btn {
    padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer;
    font-size: 13px; font-weight: 600; transition: all 0.2s;
}
#mrh-pa-container .mrh-pa-btn-primary { background: #27ae60; color: #fff; }
#mrh-pa-container .mrh-pa-btn-primary:hover { background: #219a52; }
#mrh-pa-container .mrh-pa-btn-ai { background: #3498db; color: #fff; }
#mrh-pa-container .mrh-pa-btn-ai:hover { background: #2980b9; }
#mrh-pa-container .mrh-pa-btn-secondary { background: #95a5a6; color: #fff; }
#mrh-pa-container .mrh-pa-btn-add { background: #fff; border: 1px dashed #27ae60; color: #27ae60; }
#mrh-pa-container .mrh-pa-btn-add:hover { background: #f0fdf4; }
#mrh-pa-container .mrh-pa-custom-fields { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc; }
#mrh-pa-container .mrh-pa-status { font-size: 12px; color: #666; margin-top: 5px; }
#mrh-pa-container .mrh-pa-status .badge { 
    display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; 
}
#mrh-pa-container .mrh-pa-status .badge-success { background: #d4edda; color: #155724; }
#mrh-pa-container .mrh-pa-status .badge-warning { background: #fff3cd; color: #856404; }
#mrh-pa-container .mrh-pa-status .badge-info { background: #d1ecf1; color: #0c5460; }

/* ================================================================ */
/* ICON EDITOR v1.2.0 - Searchable Library + Inline Editing         */
/* ================================================================ */
#mrh-pa-container .mrh-pa-icon-section {
    margin-top: 20px; padding: 15px; background: #fff; border: 2px solid #2c3e50; border-radius: 6px;
}
#mrh-pa-container .mrh-pa-icon-section h4 {
    margin: 0 0 12px 0; font-size: 15px; color: #2c3e50; font-weight: 700;
    border-bottom: 2px solid #27ae60; padding-bottom: 8px;
}

/* Current icons list (editable) */
#mrh-pa-container .mrh-pa-icon-list {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; min-height: 44px;
    padding: 10px; background: #f8f9fa; border-radius: 6px; border: 2px dashed #dee2e6;
}
#mrh-pa-container .mrh-pa-icon-item {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px;
    background: #fff; border: 1px solid #ddd; border-radius: 20px; font-size: 12px;
    cursor: default; transition: all 0.2s; position: relative;
}
#mrh-pa-container .mrh-pa-icon-item:hover { border-color: #27ae60; box-shadow: 0 2px 8px rgba(39,174,96,0.15); }
#mrh-pa-container .mrh-pa-icon-item .icon-preview { font-size: 18px; min-width: 20px; text-align: center; }
#mrh-pa-container .mrh-pa-icon-item .icon-title { font-weight: 500; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
#mrh-pa-container .mrh-pa-icon-item .icon-edit-color {
    width: 24px; height: 24px; padding: 0; border: 1px solid #ccc; border-radius: 50%; 
    cursor: pointer; vertical-align: middle;
}
#mrh-pa-container .mrh-pa-icon-item .icon-edit-size {
    padding: 2px 4px; border: 1px solid #ccc; border-radius: 3px; font-size: 10px; 
    background: #fff; cursor: pointer; width: 55px;
}
#mrh-pa-container .mrh-pa-icon-item .icon-remove {
    cursor: pointer; color: #e74c3c; font-weight: 700; margin-left: 2px;
    width: 18px; height: 18px; text-align: center; line-height: 18px;
    border-radius: 50%; font-size: 14px; transition: all 0.15s;
}
#mrh-pa-container .mrh-pa-icon-item .icon-remove:hover { background: #fde8e8; }

/* Icon Library (searchable) */
#mrh-pa-container .mrh-pa-icon-library {
    margin-top: 12px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;
}
#mrh-pa-container .mrh-pa-icon-library-header {
    background: #2c3e50; color: #fff; padding: 10px 14px; display: flex; align-items: center; gap: 10px;
}
#mrh-pa-container .mrh-pa-icon-library-header .lib-title {
    font-weight: 600; font-size: 13px; white-space: nowrap;
}
#mrh-pa-container .mrh-pa-icon-library-header input {
    flex: 1; padding: 6px 10px; border: none; border-radius: 4px; font-size: 13px;
    background: rgba(255,255,255,0.9);
}
#mrh-pa-container .mrh-pa-icon-library-header .lib-count {
    font-size: 11px; opacity: 0.8; white-space: nowrap;
}
#mrh-pa-container .mrh-pa-icon-library-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 4px;
    padding: 10px; max-height: 320px; overflow-y: auto; background: #fafafa;
}
#mrh-pa-container .mrh-pa-icon-lib-btn {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 8px 4px; background: #fff; border: 1px solid #e8e8e8; border-radius: 4px;
    cursor: pointer; transition: all 0.15s; min-height: 60px;
}
#mrh-pa-container .mrh-pa-icon-lib-btn:hover { border-color: #27ae60; background: #f0fdf4; transform: scale(1.05); }
#mrh-pa-container .mrh-pa-icon-lib-btn.selected { border-color: #27ae60; background: #d4edda; box-shadow: 0 0 0 2px #27ae60; }
#mrh-pa-container .mrh-pa-icon-lib-btn .fa { font-size: 22px; color: #333; margin-bottom: 4px; }
#mrh-pa-container .mrh-pa-icon-lib-btn .icon-name { font-size: 8px; color: #888; text-align: center; word-break: break-all; line-height: 1.2; }

/* Quick-Pick predefined icons */
#mrh-pa-container .mrh-pa-quickpick {
    margin-bottom: 12px; padding: 10px; background: #eef7ee; border: 1px solid #c3e6c3; border-radius: 6px;
}
#mrh-pa-container .mrh-pa-quickpick-title {
    font-size: 12px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;
}
#mrh-pa-container .mrh-pa-quickpick-grid {
    display: flex; flex-wrap: wrap; gap: 6px;
}
#mrh-pa-container .mrh-pa-quickpick-btn {
    display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px;
    background: #fff; border: 1px solid #ddd; border-radius: 16px; cursor: pointer;
    font-size: 12px; transition: all 0.15s; white-space: nowrap;
}
#mrh-pa-container .mrh-pa-quickpick-btn:hover { border-color: #27ae60; background: #f0fdf4; transform: scale(1.03); }
#mrh-pa-container .mrh-pa-quickpick-btn.active { border-color: #27ae60; background: #d4edda; box-shadow: 0 0 0 2px #27ae60; }
#mrh-pa-container .mrh-pa-quickpick-btn .fa { font-size: 16px; }

/* Add icon controls */
#mrh-pa-container .mrh-pa-icon-add-controls {
    display: flex; gap: 8px; align-items: center; margin-top: 10px; padding: 10px;
    background: #f0fdf4; border: 1px solid #d4edda; border-radius: 6px; flex-wrap: wrap;
}
#mrh-pa-container .mrh-pa-icon-add-controls label { font-size: 12px; font-weight: 600; color: #555; }
#mrh-pa-container .mrh-pa-icon-add-controls input[type="text"] {
    padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; width: 130px;
}
#mrh-pa-container .mrh-pa-icon-add-controls input[type="color"] {
    width: 32px; height: 28px; padding: 0; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;
}
#mrh-pa-container .mrh-pa-icon-add-controls select {
    padding: 5px 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; background: #fff;
}

/* Cannabis Cup section */
#mrh-pa-container .mrh-pa-cups-section {
    margin-top: 15px; padding: 15px; background: #fffbf0; border: 2px solid #f0c040; border-radius: 6px;
}
#mrh-pa-container .mrh-pa-cups-section h4 {
    margin: 0 0 10px 0; font-size: 14px; color: #8a6d3b; font-weight: 700;
}
#mrh-pa-container .mrh-pa-cups-row {
    display: flex; align-items: center; gap: 12px;
}
#mrh-pa-container .mrh-pa-cups-row input[type="number"] {
    width: 80px; padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
}
#mrh-pa-container .mrh-pa-cups-preview {
    display: flex; gap: 2px; font-size: 18px; color: #f39c12;
}
</style>

<div id="mrh-pa-container">
    <div class="mrh-pa-header">
        <h3><span class="fa fa-leaf"></span> <?php echo defined('MRH_PA_PRODUCT_TAB') ? MRH_PA_PRODUCT_TAB : 'Eigenschaften (MRH)'; ?></h3>
        <div class="mrh-pa-status">
            <?php if (!empty($mrh_pa_all_attrs)): ?>
                <?php 
                    $first_attr = reset($mrh_pa_all_attrs);
                    $filled = (int)($first_attr['fields_filled'] ?? 0);
                    $source = $first_attr['data_source'] ?? 'manual';
                ?>
                <span class="badge badge-success"><?php echo $filled; ?> Felder</span>
                <span class="badge badge-info"><?php echo ucfirst($source); ?></span>
            <?php else: ?>
                <span class="badge badge-warning">Keine Daten</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mrh-pa-body">
        <?php if ($mrh_pa_products_id == 0): ?>
            <p><em>Bitte speichern Sie das Produkt zuerst, bevor Sie Eigenschaften hinzufuegen.</em></p>
        <?php else: ?>
        
        <!-- Seed/Non-Seed Toggle -->
        <div class="mrh-pa-seed-toggle">
            <label><strong><?php echo defined('MRH_PA_PRODUCT_IS_SEED') ? MRH_PA_PRODUCT_IS_SEED : 'Ist Saatgut-Produkt'; ?>:</strong></label>
            <select name="mrh_pa[is_seed]" id="mrh_pa_is_seed">
                <option value="1" <?php echo (empty($mrh_pa_all_attrs) || ($first_attr['is_seed'] ?? 1) == 1) ? 'selected' : ''; ?>>
                    <?php echo defined('MRH_PA_PRODUCT_IS_SEED_YES') ? MRH_PA_PRODUCT_IS_SEED_YES : 'Ja (Samen)'; ?>
                </option>
                <option value="0" <?php echo (!empty($mrh_pa_all_attrs) && ($first_attr['is_seed'] ?? 1) == 0) ? 'selected' : ''; ?>>
                    <?php echo defined('MRH_PA_PRODUCT_IS_SEED_NO') ? MRH_PA_PRODUCT_IS_SEED_NO : 'Nein (Non-Seed)'; ?>
                </option>
            </select>
        </div>
        
        <!-- Preset Tabs -->
        <div class="mrh-pa-preset-tabs">
            <div class="mrh-pa-preset-tab <?php echo $mrh_pa_detected_preset === 'feminized' ? 'active' : ''; ?>" data-preset="feminized" onclick="mrhPaApplyPreset('feminized')">
                <span class="fa fa-venus"></span> <?php echo defined('MRH_PA_PRESET_FEMINIZED') ? MRH_PA_PRESET_FEMINIZED : 'Feminisiert'; ?>
            </div>
            <div class="mrh-pa-preset-tab <?php echo $mrh_pa_detected_preset === 'autoflower' ? 'active' : ''; ?>" data-preset="autoflower" onclick="mrhPaApplyPreset('autoflower')">
                <span class="fa fa-bolt"></span> <?php echo defined('MRH_PA_PRESET_AUTOFLOWER') ? MRH_PA_PRESET_AUTOFLOWER : 'Autoflowering'; ?>
            </div>
            <div class="mrh-pa-preset-tab <?php echo $mrh_pa_detected_preset === 'regular' ? 'active' : ''; ?>" data-preset="regular" onclick="mrhPaApplyPreset('regular')">
                <span class="fa fa-mars"></span> <?php echo defined('MRH_PA_PRESET_REGULAR') ? MRH_PA_PRESET_REGULAR : 'Regulaer'; ?>
            </div>
            <div class="mrh-pa-preset-tab <?php echo $mrh_pa_detected_preset === 'auto_regular' ? 'active' : ''; ?>" data-preset="auto_regular" onclick="mrhPaApplyPreset('auto_regular')">
                <span class="fa fa-bolt"></span><span class="fa fa-mars"></span> <?php echo defined('MRH_PA_PRESET_AUTO_REGULAR') ? MRH_PA_PRESET_AUTO_REGULAR : 'Auto Regulaer'; ?>
            </div>
        </div>
        
        <!-- Language Tabs -->
        <div class="mrh-pa-lang-tabs">
            <?php foreach ($mrh_pa_languages as $idx => $lang): ?>
                <div class="mrh-pa-lang-tab <?php echo $idx === 0 ? 'active' : ''; ?>" 
                     data-lang-id="<?php echo $lang['languages_id']; ?>"
                     onclick="mrhPaSwitchLang(<?php echo $lang['languages_id']; ?>, this)">
                    <?php echo $lang['name']; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Language Panels -->
        <?php foreach ($mrh_pa_languages as $idx => $lang): ?>
            <?php 
                $lid = $lang['languages_id'];
                $attrs = $mrh_pa_all_attrs[$lid] ?? [];
            ?>
            <div class="mrh-pa-lang-panel <?php echo $idx === 0 ? 'active' : ''; ?>" 
                 id="mrh-pa-lang-<?php echo $lid; ?>">
                
                <?php foreach ($mrh_pa_fields as $field_key => $field_def): ?>
                    <?php 
                        $row_class = '';
                        if ($field_def['priority'] === 'prio') $row_class = 'priority';
                        elseif ($field_def['priority'] === 'alt') $row_class = 'alt-priority';
                    ?>
                    <div class="mrh-pa-field-row <?php echo $row_class; ?>">
                        <div class="mrh-pa-field-label">
                            <?php echo $field_def['label']; ?>
                            <?php if ($field_def['priority'] === 'prio'): ?>
                                <span class="priority-star" title="Prio-Feld (immer in Mini-Tabelle)">&#9733;</span>
                            <?php elseif ($field_def['priority'] === 'alt'): ?>
                                <span class="alt-priority-star" title="Alt-Prio (Fallback wenn Prio leer)">&#9734;</span>
                            <?php endif; ?>
                        </div>
                        <div class="mrh-pa-field-input">
                            <?php if ($field_def['type'] === 'select' && isset($mrh_pa_select_options[$field_key])): ?>
                                <select name="mrh_pa[<?php echo $lid; ?>][<?php echo $field_key; ?>]" 
                                        id="mrh_pa_<?php echo $lid; ?>_<?php echo $field_key; ?>"
                                        class="mrh-pa-input" data-field="<?php echo $field_key; ?>">
                                    <?php foreach ($mrh_pa_select_options[$field_key] as $opt_val => $opt_label): ?>
                                        <option value="<?php echo htmlspecialchars($opt_val); ?>"
                                            <?php echo (isset($attrs[$field_key]) && $attrs[$field_key] == $opt_val) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($opt_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" 
                                       name="mrh_pa[<?php echo $lid; ?>][<?php echo $field_key; ?>]"
                                       id="mrh_pa_<?php echo $lid; ?>_<?php echo $field_key; ?>"
                                       class="mrh-pa-input" data-field="<?php echo $field_key; ?>"
                                       value="<?php echo htmlspecialchars($attrs[$field_key] ?? ''); ?>"
                                       placeholder="<?php echo htmlspecialchars($field_def['label']); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Custom Fields -->
                <div class="mrh-pa-custom-fields" id="mrh-pa-custom-<?php echo $lid; ?>">
                    <?php 
                    $custom = [];
                    if (!empty($attrs['custom_fields'])) {
                        $custom = json_decode($attrs['custom_fields'], true) ?: [];
                    }
                    foreach ($custom as $ci => $cf): ?>
                        <div class="mrh-pa-field-row mrh-pa-custom-row">
                            <div class="mrh-pa-field-input" style="width:180px;flex:none;">
                                <input type="text" 
                                       name="mrh_pa[<?php echo $lid; ?>][custom][<?php echo $ci; ?>][label]"
                                       value="<?php echo htmlspecialchars($cf['label'] ?? ''); ?>"
                                       placeholder="Feldname">
                            </div>
                            <div class="mrh-pa-field-input">
                                <input type="text" 
                                       name="mrh_pa[<?php echo $lid; ?>][custom][<?php echo $ci; ?>][value]"
                                       value="<?php echo htmlspecialchars($cf['value'] ?? ''); ?>"
                                       placeholder="Wert">
                            </div>
                            <button type="button" class="mrh-pa-btn mrh-pa-btn-secondary" 
                                    onclick="this.closest('.mrh-pa-custom-row').remove()" 
                                    title="Entfernen">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="mrh-pa-btn mrh-pa-btn-add" 
                        onclick="mrhPaAddCustomField(<?php echo $lid; ?>)">
                    + <?php echo defined('MRH_PA_BUTTON_ADD_FIELD') ? MRH_PA_BUTTON_ADD_FIELD : 'Feld hinzufuegen'; ?>
                </button>
            </div>
        <?php endforeach; ?>
        
        <!-- ============================================================ -->
        <!-- ICON EDITOR v1.2.0 with Searchable Library                   -->
        <!-- ============================================================ -->
        <div class="mrh-pa-icon-section">
            <h4><span class="fa fa-paint-brush"></span> Picto-Icons (Badges) — Editor</h4>
            
            <!-- Current icons list (editable inline) -->
            <div style="font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Aktuelle Icons (klicke Farbe/Groesse zum Bearbeiten):</div>
            <div class="mrh-pa-icon-list" id="mrh-pa-icon-list">
                <span style="color:#999;font-size:12px;" id="mrh-pa-icon-empty">Keine Icons. Waehlen Sie aus der Bibliothek unten.</span>
            </div>
            
            <!-- Quick-Pick: Vordefinierte Cannabis-Icons -->
            <div class="mrh-pa-quickpick">
                <div class="mrh-pa-quickpick-title"><span class="fa fa-star"></span> Schnellauswahl — Cannabis-Icons (Klick zum Hinzufuegen/Entfernen)</div>
                <div class="mrh-pa-quickpick-grid" id="mrh-pa-quickpick-grid">
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-medkit" data-color="#ff6666" data-title="Medical" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-medkit" style="color:#ff6666"></span> Medical
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-tachometer" data-color="#54B80D" data-title="Autoflowering" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-tachometer" style="color:#54B80D"></span> Autoflowering
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-female" data-color="#e84393" data-title="Feminisiert" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-female" style="color:#e84393"></span> Feminisiert
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-mars" data-color="#0984e3" data-title="Regulaer" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-mars" style="color:#0984e3"></span> Regulaer
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-leaf" data-color="#00b894" data-title="CBD-reich" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-leaf" style="color:#00b894"></span> CBD-reich
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-fire" data-color="#d63031" data-title="Hoher THC" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-fire" style="color:#d63031"></span> Hoher THC
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-star" data-color="#f39c12" data-title="Bestseller" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-star" style="color:#f39c12"></span> Bestseller
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-diamond" data-color="#00cec9" data-title="Premium" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-diamond" style="color:#00cec9"></span> Premium
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-bolt" data-color="#e17055" data-title="Schnelle Bluete" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-bolt" style="color:#e17055"></span> Schnelle Bluete
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-shield" data-color="#636e72" data-title="Resistent" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-shield" style="color:#636e72"></span> Resistent
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-sun-o" data-color="#fdcb6e" data-title="Outdoor" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-sun-o" style="color:#fdcb6e"></span> Outdoor
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-home" data-color="#6c5ce7" data-title="Indoor" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-home" style="color:#6c5ce7"></span> Indoor
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-pagelines" data-color="#27ae60" data-title="Organic" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-pagelines" style="color:#27ae60"></span> Organic
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-snowflake-o" data-color="#74b9ff" data-title="Kaltresistent" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-snowflake-o" style="color:#74b9ff"></span> Kaltresistent
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-thermometer-full" data-color="#e74c3c" data-title="Hitzeresistent" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-thermometer-full" style="color:#e74c3c"></span> Hitzeresistent
                    </span>
                    <span class="mrh-pa-quickpick-btn" data-icon="fa-trophy" data-color="#f39c12" data-title="Preisgekroent" onclick="mrhPaQuickPick(this)">
                        <span class="fa fa-trophy" style="color:#f39c12"></span> Preisgekroent
                    </span>
                </div>
            </div>
            
            <!-- Icon Library with Search -->
            <div class="mrh-pa-icon-library">
                <div class="mrh-pa-icon-library-header">
                    <span class="lib-title"><span class="fa fa-search"></span> Icon-Bibliothek</span>
                    <input type="text" id="mrh-pa-icon-search" placeholder="Icon suchen... (z.B. leaf, star, heart, trophy)" oninput="mrhPaFilterLibrary(this.value)">
                    <span class="lib-count" id="mrh-pa-icon-lib-count">730 Icons</span>
                </div>
                <div class="mrh-pa-icon-add-controls" id="mrh-pa-icon-add-controls">
                    <label>Titel:</label>
                    <input type="text" id="mrh-pa-add-title" placeholder="Anzeigename">
                    <label>Farbe:</label>
                    <input type="color" id="mrh-pa-add-color" value="#333333">
                    <label>Groesse:</label>
                    <select id="mrh-pa-add-size">
                        <option value="1em">Normal (1em)</option>
                        <option value="1.2em">Mittel (1.2em)</option>
                        <option value="1.5em">Gross (1.5em)</option>
                        <option value="2em">Sehr gross (2em)</option>
                    </select>
                    <span style="font-size:11px;color:#666;">Klicke ein Icon unten zum Hinzufuegen</span>
                </div>
                <div class="mrh-pa-icon-library-grid" id="mrh-pa-icon-library-grid">
                    <!-- Icons werden per JS geladen -->
                </div>
            </div>
            
            <!-- Hidden input to store pictos JSON -->
            <input type="hidden" name="mrh_pa[pictos]" id="mrh-pa-pictos-json" value="<?php echo htmlspecialchars(json_encode($mrh_pa_pictos)); ?>">
        </div>
        
        <!-- ============================================================ -->
        <!-- CANNABIS CUP TROPHIES                                        -->
        <!-- ============================================================ -->
        <div class="mrh-pa-cups-section">
            <h4><span class="fa fa-trophy" style="color:#f39c12"></span> Cannabis Cup Auszeichnungen</h4>
            <div class="mrh-pa-cups-row">
                <label style="font-size:13px;font-weight:600;">Anzahl Pokale:</label>
                <input type="number" name="mrh_pa[cannabis_cups]" id="mrh-pa-cups-input" 
                       min="0" max="20" value="<?php echo $mrh_pa_cups; ?>"
                       onchange="mrhPaUpdateCupsPreview()" oninput="mrhPaUpdateCupsPreview()">
                <div class="mrh-pa-cups-preview" id="mrh-pa-cups-preview">
                    <?php for ($i = 0; $i < $mrh_pa_cups; $i++): ?>
                        <span class="fa fa-trophy"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mrh-pa-actions">
            <button type="button" class="mrh-pa-btn mrh-pa-btn-ai" onclick="mrhPaAiFill(<?php echo $mrh_pa_products_id; ?>)">
                <span class="fa fa-magic"></span> <?php echo defined('MRH_PA_BUTTON_AI_FILL') ? MRH_PA_BUTTON_AI_FILL : 'Mit KI befuellen'; ?>
            </button>
            <div id="mrh-pa-ai-status" style="display:none;font-size:12px;color:#666;align-self:center;"></div>
        </div>
        
        <!-- Hidden field for product ID -->
        <input type="hidden" name="mrh_pa[products_id]" value="<?php echo $mrh_pa_products_id; ?>">
        
        <?php endif; ?>
    </div>
</div>

<script>
// ============================================================
// FONTAWESOME 4.7 COMPLETE ICON LIST (730 icons)
// ============================================================
var mrhPaAllIcons = <?php echo file_get_contents(DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/fa47_icons.json') ?: '["fa-leaf","fa-star","fa-heart","fa-fire","fa-bolt","fa-trophy","fa-medkit","fa-shield","fa-diamond","fa-eye"]'; ?>;

// ============================================================
// PICTOS / ICON EDITOR v1.2.0
// ============================================================
var mrhPaCurrentPictos = <?php echo json_encode($mrh_pa_pictos); ?> || [];

function mrhPaRenderIcons() {
    var list = document.getElementById('mrh-pa-icon-list');
    if (!list) return;
    list.innerHTML = '';
    
    if (mrhPaCurrentPictos.length === 0) {
        list.innerHTML = '<span style="color:#999;font-size:12px;" id="mrh-pa-icon-empty">Keine Icons. Waehlen Sie aus der Bibliothek unten.</span>';
    }
    
    for (var i = 0; i < mrhPaCurrentPictos.length; i++) {
        var p = mrhPaCurrentPictos[i];
        var iconClass = (p.icon || '').replace(/^fa\s+/, '');
        var item = document.createElement('div');
        item.className = 'mrh-pa-icon-item';
        item.setAttribute('data-idx', i);
        item.innerHTML = 
            '<span class="icon-preview fa ' + mrhPaEsc(iconClass) + '" style="color:' + mrhPaEsc(p.color || '#333') + ';font-size:' + mrhPaEsc(p.size || '1em') + '"></span>' +
            '<span class="icon-title" title="' + mrhPaEsc(p.title || iconClass) + '">' + mrhPaEsc(p.title || iconClass) + '</span>' +
            '<input type="color" class="icon-edit-color" value="' + mrhPaEsc(p.color || '#333333') + '" onchange="mrhPaEditIconColor(' + i + ', this.value)" title="Farbe aendern">' +
            '<select class="icon-edit-size" onchange="mrhPaEditIconSize(' + i + ', this.value)" title="Groesse aendern">' +
                '<option value="1em"' + (p.size === '1em' || !p.size ? ' selected' : '') + '>1em</option>' +
                '<option value="1.2em"' + (p.size === '1.2em' ? ' selected' : '') + '>1.2em</option>' +
                '<option value="1.5em"' + (p.size === '1.5em' ? ' selected' : '') + '>1.5em</option>' +
                '<option value="2em"' + (p.size === '2em' ? ' selected' : '') + '>2em</option>' +
            '</select>' +
            '<span class="icon-remove" onclick="mrhPaRemoveIcon(' + i + ')" title="Entfernen">&times;</span>';
        list.appendChild(item);
    }
    
    // Update hidden JSON field
    var jsonField = document.getElementById('mrh-pa-pictos-json');
    if (jsonField) jsonField.value = JSON.stringify(mrhPaCurrentPictos);
    
    // Update library selected states
    mrhPaUpdateLibrarySelection();
    
    // Update quick-pick selected states
    mrhPaUpdateQuickPick();
}

function mrhPaEditIconColor(idx, color) {
    if (mrhPaCurrentPictos[idx]) {
        mrhPaCurrentPictos[idx].color = color;
        // Update preview immediately without full re-render
        var item = document.querySelector('.mrh-pa-icon-item[data-idx="' + idx + '"]');
        if (item) {
            var preview = item.querySelector('.icon-preview');
            if (preview) preview.style.color = color;
        }
        document.getElementById('mrh-pa-pictos-json').value = JSON.stringify(mrhPaCurrentPictos);
    }
}

function mrhPaEditIconSize(idx, size) {
    if (mrhPaCurrentPictos[idx]) {
        mrhPaCurrentPictos[idx].size = size;
        // Update preview immediately
        var item = document.querySelector('.mrh-pa-icon-item[data-idx="' + idx + '"]');
        if (item) {
            var preview = item.querySelector('.icon-preview');
            if (preview) preview.style.fontSize = size;
        }
        document.getElementById('mrh-pa-pictos-json').value = JSON.stringify(mrhPaCurrentPictos);
    }
}

function mrhPaRemoveIcon(idx) {
    mrhPaCurrentPictos.splice(idx, 1);
    mrhPaRenderIcons();
}

function mrhPaAddIconFromLibrary(iconClass) {
    // Check if already added
    var existIdx = -1;
    for (var i = 0; i < mrhPaCurrentPictos.length; i++) {
        var existing = (mrhPaCurrentPictos[i].icon || '').replace(/^fa\s+/, '');
        if (existing === iconClass || existing === iconClass.replace('fa-', '')) {
            existIdx = i;
            break;
        }
    }
    
    if (existIdx >= 0) {
        // Remove if already exists (toggle)
        mrhPaCurrentPictos.splice(existIdx, 1);
    } else {
        // Add new
        var title = document.getElementById('mrh-pa-add-title').value.trim() || iconClass.replace('fa-', '').replace(/-/g, ' ');
        var color = document.getElementById('mrh-pa-add-color').value || '#333333';
        var size = document.getElementById('mrh-pa-add-size').value || '1em';
        
        mrhPaCurrentPictos.push({
            icon: iconClass,
            color: color,
            size: size,
            title: title
        });
        
        // Clear title for next
        document.getElementById('mrh-pa-add-title').value = '';
    }
    
    mrhPaRenderIcons();
}

// ============================================================
// ICON LIBRARY - Build & Search
// ============================================================
function mrhPaBuildLibrary() {
    var grid = document.getElementById('mrh-pa-icon-library-grid');
    if (!grid) return;
    
    var html = '';
    for (var i = 0; i < mrhPaAllIcons.length; i++) {
        var icon = mrhPaAllIcons[i];
        var name = icon.replace('fa-', '');
        html += '<div class="mrh-pa-icon-lib-btn" data-icon="' + icon + '" data-name="' + name + '" onclick="mrhPaAddIconFromLibrary(\'' + icon + '\')">' +
            '<span class="fa ' + icon + '"></span>' +
            '<span class="icon-name">' + name + '</span>' +
            '</div>';
    }
    grid.innerHTML = html;
    mrhPaUpdateLibrarySelection();
}

function mrhPaFilterLibrary(query) {
    var grid = document.getElementById('mrh-pa-icon-library-grid');
    if (!grid) return;
    
    var buttons = grid.querySelectorAll('.mrh-pa-icon-lib-btn');
    var q = query.toLowerCase().trim();
    var visible = 0;
    
    for (var i = 0; i < buttons.length; i++) {
        var name = buttons[i].getAttribute('data-name');
        var show = !q || name.indexOf(q) !== -1;
        buttons[i].style.display = show ? '' : 'none';
        if (show) visible++;
    }
    
    var countEl = document.getElementById('mrh-pa-icon-lib-count');
    if (countEl) countEl.textContent = visible + ' Icons' + (q ? ' gefunden' : '');
}

function mrhPaUpdateLibrarySelection() {
    var grid = document.getElementById('mrh-pa-icon-library-grid');
    if (!grid) return;
    
    var buttons = grid.querySelectorAll('.mrh-pa-icon-lib-btn');
    for (var i = 0; i < buttons.length; i++) {
        var icon = buttons[i].getAttribute('data-icon');
        var isSelected = mrhPaCurrentPictos.some(function(p) {
            var existing = (p.icon || '').replace(/^fa\s+/, '');
            return existing === icon || existing === icon.replace('fa-', '');
        });
        buttons[i].classList.toggle('selected', isSelected);
    }
}

// ============================================================
// QUICK-PICK (predefined cannabis icons)
// ============================================================
function mrhPaQuickPick(el) {
    var iconClass = el.getAttribute('data-icon');
    var color = el.getAttribute('data-color') || '#333333';
    var title = el.getAttribute('data-title') || iconClass.replace('fa-', '');
    
    // Check if already in current pictos
    var existIdx = -1;
    for (var i = 0; i < mrhPaCurrentPictos.length; i++) {
        var existing = (mrhPaCurrentPictos[i].icon || '').replace(/^fa\s+/, '');
        if (existing === iconClass) {
            existIdx = i;
            break;
        }
    }
    
    if (existIdx >= 0) {
        // Remove (toggle off)
        mrhPaCurrentPictos.splice(existIdx, 1);
    } else {
        // Add with predefined color
        mrhPaCurrentPictos.push({
            icon: iconClass,
            color: color,
            size: '1em',
            title: title
        });
    }
    
    mrhPaRenderIcons();
}

function mrhPaUpdateQuickPick() {
    var btns = document.querySelectorAll('.mrh-pa-quickpick-btn');
    for (var i = 0; i < btns.length; i++) {
        var icon = btns[i].getAttribute('data-icon');
        var isActive = mrhPaCurrentPictos.some(function(p) {
            var existing = (p.icon || '').replace(/^fa\s+/, '');
            return existing === icon;
        });
        btns[i].classList.toggle('active', isActive);
    }
}

// ============================================================
// CANNABIS CUP PREVIEW
// ============================================================
function mrhPaUpdateCupsPreview() {
    var count = parseInt(document.getElementById('mrh-pa-cups-input').value) || 0;
    var preview = document.getElementById('mrh-pa-cups-preview');
    if (!preview) return;
    preview.innerHTML = '';
    for (var i = 0; i < Math.min(count, 20); i++) {
        var span = document.createElement('span');
        span.className = 'fa fa-trophy';
        preview.appendChild(span);
    }
}

// ============================================================
// PRESET APPLICATION + AUTO-DETECT
// ============================================================
function mrhPaApplyPreset(preset) {
    document.querySelectorAll('.mrh-pa-preset-tab').forEach(function(t) { t.classList.remove('active'); });
    var tab = document.querySelector('.mrh-pa-preset-tab[data-preset="'+preset+'"]');
    if (tab) tab.classList.add('active');
    
    var panels = document.querySelectorAll('.mrh-pa-lang-panel');
    panels.forEach(function(panel) {
        var genderSel = panel.querySelector('select[data-field="gender"]');
        var flowerSel = panel.querySelector('select[data-field="flowering_type"]');
        
        if (preset === 'feminized') {
            if (genderSel) genderSel.value = 'feminized';
            if (flowerSel) flowerSel.value = 'photoperiod';
        } else if (preset === 'autoflower') {
            if (genderSel) genderSel.value = 'feminized';
            if (flowerSel) flowerSel.value = 'autoflower';
        } else if (preset === 'regular') {
            if (genderSel) genderSel.value = 'regular';
            if (flowerSel) flowerSel.value = 'photoperiod';
        } else if (preset === 'auto_regular') {
            if (genderSel) genderSel.value = 'regular';
            if (flowerSel) flowerSel.value = 'autoflower';
        }
    });
}

// ============================================================
// LANGUAGE TAB SWITCHING
// ============================================================
function mrhPaSwitchLang(langId, el) {
    document.querySelectorAll('.mrh-pa-lang-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.mrh-pa-lang-panel').forEach(function(p) { p.classList.remove('active'); });
    el.classList.add('active');
    var panel = document.getElementById('mrh-pa-lang-' + langId);
    if (panel) panel.classList.add('active');
}

// ============================================================
// CUSTOM FIELDS
// ============================================================
var mrhPaCustomCounter = <?php echo max(count($custom ?? []), 0) + 10; ?>;
function mrhPaAddCustomField(langId) {
    var container = document.getElementById('mrh-pa-custom-' + langId);
    if (!container) return;
    var idx = mrhPaCustomCounter++;
    var row = document.createElement('div');
    row.className = 'mrh-pa-field-row mrh-pa-custom-row';
    row.innerHTML = '<div class="mrh-pa-field-input" style="width:180px;flex:none;">' +
        '<input type="text" name="mrh_pa['+langId+'][custom]['+idx+'][label]" placeholder="Feldname">' +
        '</div><div class="mrh-pa-field-input">' +
        '<input type="text" name="mrh_pa['+langId+'][custom]['+idx+'][value]" placeholder="Wert">' +
        '</div><button type="button" class="mrh-pa-btn mrh-pa-btn-secondary" onclick="this.closest(\'.mrh-pa-custom-row\').remove()" title="Entfernen">&times;</button>';
    container.appendChild(row);
}

// ============================================================
// AI FILL (SINGLE PRODUCT)
// ============================================================
function mrhPaAiFill(productsId) {
    var statusEl = document.getElementById('mrh-pa-ai-status');
    if (!statusEl) return;
    statusEl.style.display = 'inline';
    statusEl.innerHTML = '<span class="fa fa-spinner fa-spin"></span> KI analysiert Beschreibung...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'mrh_product_attributes.php?action=ai_fill&products_id=' + productsId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        statusEl.innerHTML = '<span style="color:green"><span class="fa fa-check"></span> ' + 
                            (data.message || 'Erfolgreich!') + '</span>';
                        if (data.attributes) {
                            mrhPaFillFields(data.attributes);
                        }
                    } else {
                        statusEl.innerHTML = '<span style="color:red"><span class="fa fa-times"></span> ' + 
                            (data.message || 'Fehler') + '</span>';
                    }
                } catch(e) {
                    statusEl.innerHTML = '<span style="color:red">Antwort-Fehler: ' + e.message + '</span>';
                }
            } else {
                statusEl.innerHTML = '<span style="color:red">Verbindungsfehler (HTTP ' + xhr.status + ')</span>';
            }
        }
    };
    xhr.send('csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || ''));
}

// Fill form fields from AI response
function mrhPaFillFields(attrs) {
    for (var langId in attrs) {
        var langAttrs = attrs[langId];
        
        // Handle is_seed
        if (langAttrs.is_seed !== undefined) {
            var seedEl = document.getElementById('mrh_pa_is_seed');
            if (seedEl) {
                seedEl.value = langAttrs.is_seed ? '1' : '0';
                mrhPaHighlight(seedEl);
            }
        }
        
        // Handle standard fields
        for (var field in langAttrs) {
            if (field === 'custom_fields' || field === 'is_seed' || field === 'ai_confidence' || field === 'pictos' || field === 'cannabis_cups') continue;
            var el = document.getElementById('mrh_pa_' + langId + '_' + field);
            if (el) {
                el.value = langAttrs[field];
                mrhPaHighlight(el);
            }
        }
        
        // Handle custom_fields
        if (langAttrs.custom_fields && Array.isArray(langAttrs.custom_fields)) {
            var container = document.getElementById('mrh-pa-custom-' + langId);
            if (container) {
                container.innerHTML = '';
                for (var i = 0; i < langAttrs.custom_fields.length; i++) {
                    var cf = langAttrs.custom_fields[i];
                    mrhPaCustomCounter++;
                    var row = document.createElement('div');
                    row.className = 'mrh-pa-field-row mrh-pa-custom-row';
                    row.innerHTML = '<div class="mrh-pa-field-input" style="width:180px;flex:none;">' +
                        '<input type="text" name="mrh_pa['+langId+'][custom]['+i+'][label]" value="' + mrhPaEsc(cf.label || '') + '" placeholder="Feldname">' +
                        '</div><div class="mrh-pa-field-input">' +
                        '<input type="text" name="mrh_pa['+langId+'][custom]['+i+'][value]" value="' + mrhPaEsc(cf.value || '') + '" placeholder="Wert">' +
                        '</div><button type="button" class="mrh-pa-btn mrh-pa-btn-secondary" onclick="this.closest(\'.mrh-pa-custom-row\').remove()" title="Entfernen">&times;</button>';
                    container.appendChild(row);
                    row.querySelectorAll('input').forEach(function(inp) { mrhPaHighlight(inp); });
                }
            }
        }
    }
    
    // Auto-detect preset after AI fill
    mrhPaAutoDetectPreset();
}

// Auto-detect and activate the correct preset based on current field values
function mrhPaAutoDetectPreset() {
    var firstPanel = document.querySelector('.mrh-pa-lang-panel');
    if (!firstPanel) return;
    
    var genderSel = firstPanel.querySelector('select[data-field="gender"]');
    var flowerSel = firstPanel.querySelector('select[data-field="flowering_type"]');
    
    var gender = genderSel ? genderSel.value : '';
    var flower = flowerSel ? flowerSel.value : '';
    
    var preset = '';
    if (gender === 'feminized' && flower === 'autoflower') preset = 'autoflower';
    else if (gender === 'feminized' && flower === 'photoperiod') preset = 'feminized';
    else if (gender === 'regular' && flower === 'autoflower') preset = 'auto_regular';
    else if (gender === 'regular' && flower === 'photoperiod') preset = 'regular';
    else if (gender === 'regular') preset = 'regular';
    else if (gender === 'feminized') preset = 'feminized';
    
    document.querySelectorAll('.mrh-pa-preset-tab').forEach(function(t) { t.classList.remove('active'); });
    if (preset) {
        var tab = document.querySelector('.mrh-pa-preset-tab[data-preset="'+preset+'"]');
        if (tab) tab.classList.add('active');
    }
}

// Highlight helper
function mrhPaHighlight(el) {
    el.style.backgroundColor = '#ffffcc';
    setTimeout(function() { el.style.backgroundColor = ''; }, 5000);
}

// Escape HTML for safe insertion
function mrhPaEsc(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ============================================================
// INIT ON PAGE LOAD
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    mrhPaRenderIcons();
    mrhPaBuildLibrary();
    mrhPaAutoDetectPreset();
});
</script>
