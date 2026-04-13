<?php
/**
 * MRH Product Attributes - categories.php Product Edit Hook
 * Autoinclude: ~/admin/includes/extra/modules/new_product/
 * 
 * Injects the "Eigenschaften (MRH)" tab into the product edit form.
 * This hook runs inside the product edit form in categories.php.
 *
 * Features:
 * - 4 Preset buttons (Feminisiert | Autoflowering | Regulaer | Auto Regulaer)
 * - Language tabs with all standard fields
 * - FontAwesome Icon Editor (picker, color, size)
 * - Cannabis Cup trophy count
 * - AI fill button (single product)
 * - Auto-preset detection from loaded data
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
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

// Common FA icons for cannabis products (for the quick-pick grid)
$mrh_pa_common_icons = [
    ['icon' => 'fa-medkit', 'title' => 'Medical', 'color' => '#ff6666'],
    ['icon' => 'fa-tachometer', 'title' => 'Autoflowering', 'color' => '#54B80D'],
    ['icon' => 'fa-leaf', 'title' => 'CBD-reich', 'color' => '#00b894'],
    ['icon' => 'fa-fire', 'title' => 'Hoher THC', 'color' => '#d63031'],
    ['icon' => 'fa-star', 'title' => 'Bestseller', 'color' => '#f39c12'],
    ['icon' => 'fa-diamond', 'title' => 'Premium', 'color' => '#00cec9'],
    ['icon' => 'fa-bolt', 'title' => 'Schnelle Bluete', 'color' => '#e17055'],
    ['icon' => 'fa-shield', 'title' => 'Resistent', 'color' => '#636e72'],
    ['icon' => 'fa-sun-o', 'title' => 'Outdoor', 'color' => '#fdcb6e'],
    ['icon' => 'fa-home', 'title' => 'Indoor', 'color' => '#6c5ce7'],
    ['icon' => 'fa-female', 'title' => 'Feminisiert', 'color' => '#e84393'],
    ['icon' => 'fa-pagelines', 'title' => 'Organic', 'color' => '#27ae60'],
    ['icon' => 'fa-snowflake-o', 'title' => 'Kaltresistent', 'color' => '#74b9ff'],
    ['icon' => 'fa-thermometer-full', 'title' => 'Hitzeresistent', 'color' => '#e74c3c'],
    ['icon' => 'fa-eye', 'title' => 'Besonders', 'color' => '#9b59b6'],
    ['icon' => 'fa-heart', 'title' => 'Beliebt', 'color' => '#e74c3c'],
];
?>

<!-- MRH Product Attributes Tab v1.1.0 -->
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

/* === Icon Editor Styles === */
#mrh-pa-container .mrh-pa-icon-section {
    margin-top: 15px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 6px;
}
#mrh-pa-container .mrh-pa-icon-section h4 {
    margin: 0 0 12px 0; font-size: 14px; color: #2c3e50;
}
#mrh-pa-container .mrh-pa-icon-list {
    display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; min-height: 40px;
    padding: 8px; background: #f8f9fa; border-radius: 4px; border: 1px dashed #dee2e6;
}
#mrh-pa-container .mrh-pa-icon-item {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px;
    background: #fff; border: 1px solid #ddd; border-radius: 20px; font-size: 12px;
    cursor: default; transition: all 0.2s;
}
#mrh-pa-container .mrh-pa-icon-item:hover { border-color: #27ae60; }
#mrh-pa-container .mrh-pa-icon-item .icon-preview { font-size: 16px; }
#mrh-pa-container .mrh-pa-icon-item .icon-title { font-weight: 500; }
#mrh-pa-container .mrh-pa-icon-item .icon-remove {
    cursor: pointer; color: #e74c3c; font-weight: 700; margin-left: 4px;
    width: 16px; height: 16px; text-align: center; line-height: 16px;
    border-radius: 50%; font-size: 14px;
}
#mrh-pa-container .mrh-pa-icon-item .icon-remove:hover { background: #fde8e8; }

/* Quick-pick grid */
#mrh-pa-container .mrh-pa-icon-quickpick {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 6px;
    margin-bottom: 12px;
}
#mrh-pa-container .mrh-pa-icon-quickpick-btn {
    display: flex; align-items: center; gap: 6px; padding: 6px 8px;
    background: #fff; border: 1px solid #e0e0e0; border-radius: 4px;
    cursor: pointer; font-size: 11px; transition: all 0.15s;
}
#mrh-pa-container .mrh-pa-icon-quickpick-btn:hover { border-color: #27ae60; background: #f0fdf4; }
#mrh-pa-container .mrh-pa-icon-quickpick-btn.selected { border-color: #27ae60; background: #d4edda; }
#mrh-pa-container .mrh-pa-icon-quickpick-btn .fa { font-size: 14px; }

/* Custom icon add row */
#mrh-pa-container .mrh-pa-icon-add-row {
    display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
}
#mrh-pa-container .mrh-pa-icon-add-row input[type="text"] {
    width: 140px; padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;
}
#mrh-pa-container .mrh-pa-icon-add-row input[type="color"] {
    width: 36px; height: 30px; padding: 0; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;
}
#mrh-pa-container .mrh-pa-icon-add-row select {
    padding: 5px 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px; background: #fff;
}

/* Cannabis Cup section */
#mrh-pa-container .mrh-pa-cups-section {
    margin-top: 15px; padding: 15px; background: #fffbf0; border: 1px solid #f0c040; border-radius: 6px;
}
#mrh-pa-container .mrh-pa-cups-section h4 {
    margin: 0 0 10px 0; font-size: 14px; color: #8a6d3b;
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
        <!-- ICON EDITOR (Pictos) - Global (not per language) -->
        <!-- ============================================================ -->
        <div class="mrh-pa-icon-section">
            <h4><span class="fa fa-picture-o"></span> Picto-Icons (Badges)</h4>
            
            <!-- Current icons list -->
            <div class="mrh-pa-icon-list" id="mrh-pa-icon-list">
                <?php if (empty($mrh_pa_pictos)): ?>
                    <span style="color:#999;font-size:12px;" id="mrh-pa-icon-empty">Keine Icons. Waehlen Sie unten aus oder fuegen Sie eigene hinzu.</span>
                <?php endif; ?>
            </div>
            
            <!-- Quick-pick grid -->
            <div style="margin-bottom:8px;font-size:12px;font-weight:600;color:#555;">Schnellauswahl:</div>
            <div class="mrh-pa-icon-quickpick" id="mrh-pa-icon-quickpick">
                <?php foreach ($mrh_pa_common_icons as $ci): ?>
                    <div class="mrh-pa-icon-quickpick-btn" 
                         data-icon="<?php echo $ci['icon']; ?>"
                         data-title="<?php echo htmlspecialchars($ci['title']); ?>"
                         data-color="<?php echo $ci['color']; ?>"
                         onclick="mrhPaToggleQuickIcon(this)">
                        <span class="fa <?php echo $ci['icon']; ?>" style="color:<?php echo $ci['color']; ?>"></span>
                        <?php echo htmlspecialchars($ci['title']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Custom icon add -->
            <div style="margin-top:10px;font-size:12px;font-weight:600;color:#555;margin-bottom:6px;">Eigenes Icon hinzufuegen:</div>
            <div class="mrh-pa-icon-add-row">
                <input type="text" id="mrh-pa-icon-custom-class" placeholder="fa-icon-name" title="FontAwesome Klasse (z.B. fa-star)">
                <input type="text" id="mrh-pa-icon-custom-title" placeholder="Titel" title="Anzeigename">
                <input type="color" id="mrh-pa-icon-custom-color" value="#333333" title="Farbe">
                <select id="mrh-pa-icon-custom-size" title="Groesse">
                    <option value="1em">Normal</option>
                    <option value="1.2em">Mittel</option>
                    <option value="1.5em">Gross</option>
                    <option value="2em">Sehr gross</option>
                </select>
                <button type="button" class="mrh-pa-btn mrh-pa-btn-primary" onclick="mrhPaAddCustomIcon()" style="padding:5px 12px;font-size:12px;">
                    <span class="fa fa-plus"></span> Hinzufuegen
                </button>
            </div>
            
            <!-- Hidden input to store pictos JSON -->
            <input type="hidden" name="mrh_pa[pictos]" id="mrh-pa-pictos-json" value="<?php echo htmlspecialchars(json_encode($mrh_pa_pictos)); ?>">
        </div>
        
        <!-- ============================================================ -->
        <!-- CANNABIS CUP TROPHIES -->
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
// PICTOS / ICON EDITOR
// ============================================================
var mrhPaCurrentPictos = <?php echo json_encode($mrh_pa_pictos); ?> || [];

function mrhPaRenderIcons() {
    var list = document.getElementById('mrh-pa-icon-list');
    var emptyMsg = document.getElementById('mrh-pa-icon-empty');
    list.innerHTML = '';
    
    if (mrhPaCurrentPictos.length === 0) {
        list.innerHTML = '<span style="color:#999;font-size:12px;" id="mrh-pa-icon-empty">Keine Icons. Waehlen Sie unten aus oder fuegen Sie eigene hinzu.</span>';
    }
    
    for (var i = 0; i < mrhPaCurrentPictos.length; i++) {
        var p = mrhPaCurrentPictos[i];
        var item = document.createElement('div');
        item.className = 'mrh-pa-icon-item';
        item.innerHTML = '<span class="icon-preview fa ' + mrhPaEsc(p.icon.replace('fa ', '')) + '" style="color:' + mrhPaEsc(p.color || '#333') + ';font-size:' + mrhPaEsc(p.size || '1em') + '"></span>' +
            '<span class="icon-title">' + mrhPaEsc(p.title || '') + '</span>' +
            '<span class="icon-remove" data-idx="' + i + '" onclick="mrhPaRemoveIcon(' + i + ')" title="Entfernen">&times;</span>';
        list.appendChild(item);
    }
    
    // Update hidden JSON field
    document.getElementById('mrh-pa-pictos-json').value = JSON.stringify(mrhPaCurrentPictos);
    
    // Update quickpick selected states
    document.querySelectorAll('.mrh-pa-icon-quickpick-btn').forEach(function(btn) {
        var icon = btn.getAttribute('data-icon');
        var isSelected = mrhPaCurrentPictos.some(function(p) { 
            return p.icon === icon || p.icon === 'fa ' + icon; 
        });
        btn.classList.toggle('selected', isSelected);
    });
}

function mrhPaToggleQuickIcon(btn) {
    var icon = btn.getAttribute('data-icon');
    var title = btn.getAttribute('data-title');
    var color = btn.getAttribute('data-color');
    
    // Check if already added
    var existIdx = -1;
    for (var i = 0; i < mrhPaCurrentPictos.length; i++) {
        if (mrhPaCurrentPictos[i].icon === icon || mrhPaCurrentPictos[i].icon === 'fa ' + icon) {
            existIdx = i;
            break;
        }
    }
    
    if (existIdx >= 0) {
        // Remove
        mrhPaCurrentPictos.splice(existIdx, 1);
    } else {
        // Add
        mrhPaCurrentPictos.push({icon: icon, color: color, size: '1em', title: title});
    }
    
    mrhPaRenderIcons();
}

function mrhPaAddCustomIcon() {
    var iconClass = document.getElementById('mrh-pa-icon-custom-class').value.trim();
    var title = document.getElementById('mrh-pa-icon-custom-title').value.trim();
    var color = document.getElementById('mrh-pa-icon-custom-color').value;
    var size = document.getElementById('mrh-pa-icon-custom-size').value;
    
    if (!iconClass) { alert('Bitte Icon-Klasse eingeben (z.B. fa-star)'); return; }
    if (!iconClass.startsWith('fa-')) iconClass = 'fa-' + iconClass;
    if (!title) title = iconClass.replace('fa-', '');
    
    mrhPaCurrentPictos.push({icon: iconClass, color: color, size: size, title: title});
    mrhPaRenderIcons();
    
    // Clear inputs
    document.getElementById('mrh-pa-icon-custom-class').value = '';
    document.getElementById('mrh-pa-icon-custom-title').value = '';
}

function mrhPaRemoveIcon(idx) {
    mrhPaCurrentPictos.splice(idx, 1);
    mrhPaRenderIcons();
}

// Init icons on page load
document.addEventListener('DOMContentLoaded', function() {
    mrhPaRenderIcons();
});

// ============================================================
// CANNABIS CUP PREVIEW
// ============================================================
function mrhPaUpdateCupsPreview() {
    var count = parseInt(document.getElementById('mrh-pa-cups-input').value) || 0;
    var preview = document.getElementById('mrh-pa-cups-preview');
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
    document.getElementById('mrh-pa-lang-' + langId).classList.add('active');
}

// ============================================================
// CUSTOM FIELDS
// ============================================================
var mrhPaCustomCounter = <?php echo max(count($custom ?? []), 0) + 10; ?>;
function mrhPaAddCustomField(langId) {
    var container = document.getElementById('mrh-pa-custom-' + langId);
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
</script>
