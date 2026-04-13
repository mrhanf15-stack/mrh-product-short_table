<?php
/**
 * MRH Product Attributes - categories.php Product Edit Hook
 * Autoinclude: ~/admin/includes/extra/modules/new_product/
 * 
 * Injects the "Eigenschaften (MRH)" tab into the product edit form.
 * This hook runs inside the product edit form in categories.php.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
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
// priority: 'prio' = Hauptpriorität (Sorte, THC, CBD)
//           'alt'  = Alternative Priorität / Fallback (Bluetezeit, Ertrag Indoor, Erntezeit, Kreuzung)
//           false  = Normal
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
?>

<!-- MRH Product Attributes Tab -->
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
    display: flex; gap: 5px; margin-bottom: 15px; 
}
#mrh-pa-container .mrh-pa-preset-tab {
    padding: 8px 16px; border: 1px solid #ccc; border-radius: 4px;
    background: #fff; cursor: pointer; font-size: 13px; transition: all 0.2s;
}
#mrh-pa-container .mrh-pa-preset-tab:hover { background: #e8f5e9; }
#mrh-pa-container .mrh-pa-preset-tab.active { 
    background: #27ae60; color: #fff; border-color: #27ae60; 
}
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
#mrh-pa-container .mrh-pa-field-label { 
    width: 180px; font-weight: 600; font-size: 13px; flex-shrink: 0; 
}
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
</style>

<div id="mrh-pa-container">
    <div class="mrh-pa-header">
        <h3><i class="fa fa-leaf"></i> <?php echo defined('MRH_PA_PRODUCT_TAB') ? MRH_PA_PRODUCT_TAB : 'Eigenschaften (MRH)'; ?></h3>
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
        
        <!-- Preset Tabs (Feminized/Auto/Regular) -->
        <div class="mrh-pa-preset-tabs">
            <div class="mrh-pa-preset-tab" data-preset="feminized" onclick="mrhPaApplyPreset('feminized')">
                <i class="fa fa-venus"></i> <?php echo defined('MRH_PA_PRESET_FEMINIZED') ? MRH_PA_PRESET_FEMINIZED : 'Feminisiert'; ?>
            </div>
            <div class="mrh-pa-preset-tab" data-preset="autoflower" onclick="mrhPaApplyPreset('autoflower')">
                <i class="fa fa-bolt"></i> <?php echo defined('MRH_PA_PRESET_AUTOFLOWER') ? MRH_PA_PRESET_AUTOFLOWER : 'Autoflowering'; ?>
            </div>
            <div class="mrh-pa-preset-tab" data-preset="regular" onclick="mrhPaApplyPreset('regular')">
                <i class="fa fa-mars-and-venus"></i> <?php echo defined('MRH_PA_PRESET_REGULAR') ? MRH_PA_PRESET_REGULAR : 'Regulaer'; ?>
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
        
        <!-- Action Buttons -->
        <div class="mrh-pa-actions">
            <button type="button" class="mrh-pa-btn mrh-pa-btn-ai" onclick="mrhPaAiFill(<?php echo $mrh_pa_products_id; ?>)">
                <i class="fa fa-magic"></i> <?php echo defined('MRH_PA_BUTTON_AI_FILL') ? MRH_PA_BUTTON_AI_FILL : 'Mit KI befuellen'; ?>
            </button>
            <div id="mrh-pa-ai-status" style="display:none;font-size:12px;color:#666;align-self:center;"></div>
        </div>
        
        <!-- Hidden field for product ID -->
        <input type="hidden" name="mrh_pa[products_id]" value="<?php echo $mrh_pa_products_id; ?>">
        
        <?php endif; ?>
    </div>
</div>

<script>
// Preset application
function mrhPaApplyPreset(preset) {
    // Highlight active preset tab
    document.querySelectorAll('.mrh-pa-preset-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.mrh-pa-preset-tab[data-preset="'+preset+'"]').classList.add('active');
    
    // Get first language panel's selects
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
        }
    });
}

// Language tab switching
function mrhPaSwitchLang(langId, el) {
    document.querySelectorAll('.mrh-pa-lang-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.mrh-pa-lang-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('mrh-pa-lang-' + langId).classList.add('active');
}

// Add custom field
var mrhPaCustomCounter = <?php echo max(count($custom ?? []), 0); ?>;
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

// AI Fill (AJAX)
function mrhPaAiFill(productsId) {
    var statusEl = document.getElementById('mrh-pa-ai-status');
    statusEl.style.display = 'inline';
    statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> KI analysiert Beschreibung...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'mrh_product_attributes.php?action=ai_fill&products_id=' + productsId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        statusEl.innerHTML = '<span style="color:green"><i class="fa fa-check"></i> ' + 
                            (data.message || 'Erfolgreich!') + '</span>';
                        // Fill form fields with AI results
                        if (data.attributes) {
                            mrhPaFillFields(data.attributes);
                        }
                    } else {
                        statusEl.innerHTML = '<span style="color:red"><i class="fa fa-times"></i> ' + 
                            (data.message || 'Fehler') + '</span>';
                    }
                } catch(e) {
                    statusEl.innerHTML = '<span style="color:red">Antwort-Fehler</span>';
                }
            } else {
                statusEl.innerHTML = '<span style="color:red">Verbindungsfehler</span>';
            }
        }
    };
    xhr.send('csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || ''));
}

// Fill form fields from AI response
function mrhPaFillFields(attrs) {
    for (var langId in attrs) {
        var langAttrs = attrs[langId];
        for (var field in langAttrs) {
            var el = document.getElementById('mrh_pa_' + langId + '_' + field);
            if (el) {
                el.value = langAttrs[field];
                el.style.backgroundColor = '#ffffcc'; // Highlight AI-filled fields
                setTimeout(function(e) { e.style.backgroundColor = ''; }.bind(null, el), 3000);
            }
        }
    }
}
</script>
