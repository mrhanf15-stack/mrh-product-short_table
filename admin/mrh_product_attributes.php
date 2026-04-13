<?php
/**
 * MRH Product Attributes - Admin Page
 * 
 * Standalone admin page for:
 * - Module configuration (API key, model, settings)
 * - Migration tools (bulk import from short descriptions)
 * - Statistics dashboard
 * - AI batch processing (real implementation)
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
 */

require('includes/application_top.php');

// Load module class
if (!class_exists('MrhProductAttributes')) {
    $mrh_pa_class = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_product_attributes.php';
    if (file_exists($mrh_pa_class)) {
        require_once($mrh_pa_class);
    }
}

// Self-install
if (class_exists('MrhProductAttributes')) {
    MrhProductAttributes::checkAndInstall();
}

// Handle AJAX actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ============================================================
// AJAX: Save product attributes (from product edit tab)
// ============================================================
if ($action === 'save_product' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!class_exists('MrhProductAttributes')) {
        echo json_encode(['success' => false, 'message' => 'Module not loaded']);
        exit;
    }
    
    // Parse JSON body
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    
    if (!$payload || empty($payload['products_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing products_id']);
        exit;
    }
    
    $products_id = (int)$payload['products_id'];
    $is_seed = isset($payload['is_seed']) ? (int)$payload['is_seed'] : 1;
    
    // Pictos (global)
    $pictos = null;
    if (isset($payload['pictos']) && is_array($payload['pictos'])) {
        $pictos = [];
        foreach ($payload['pictos'] as $p) {
            if (!empty($p['icon'])) {
                $style_val = $p['style'] ?? 'solid';
                if (!in_array($style_val, ['solid', 'regular', 'brands'])) $style_val = 'solid';
                $pictos[] = [
                    'icon'  => preg_replace('/[^a-zA-Z0-9\-\.\/\:_ ]/', '', $p['icon'] ?? ''),
                    'color' => preg_replace('/[^a-zA-Z0-9#]/', '', $p['color'] ?? '#333333'),
                    'size'  => preg_replace('/[^a-zA-Z0-9.px]/', '', $p['size'] ?? '16px'),
                    'title' => mb_substr(strip_tags($p['title'] ?? ''), 0, 100),
                    'style' => $style_val,
                ];
            }
        }
    }
    
    // Cannabis Cups
    $cups = isset($payload['cannabis_cups']) ? max(0, min(99, (int)$payload['cannabis_cups'])) : 0;
    
    // Field order (optional)
    $field_order = null;
    if (isset($payload['field_order']) && is_array($payload['field_order'])) {
        $field_order = array_values(array_filter($payload['field_order'], function($v) {
            return preg_match('/^[a-z_]+$/', $v);
        }));
    }
    
    // Standard fields
    $standard_fields = [
        'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
        'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
        'flowering_time', 'harvest_time', 'climate', 'effect', 'taste', 'growing'
    ];
    
    $saved_langs = 0;
    
    // Process each language
    if (isset($payload['languages']) && is_array($payload['languages'])) {
        foreach ($payload['languages'] as $lang_id => $lang_data) {
            $lang_id = (int)$lang_id;
            if ($lang_id <= 0) continue;
            
            $data = ['is_seed' => $is_seed];
            
            foreach ($standard_fields as $field) {
                if (isset($lang_data[$field])) {
                    $data[$field] = trim($lang_data[$field]);
                }
            }
            
            // Custom fields (with dedup: skip labels that match standard fields)
            if (isset($lang_data['custom']) && is_array($lang_data['custom'])) {
                // Build dedup label list from standard fields
                $std_labels_lower = [];
                foreach (MrhProductAttributes::STANDARD_FIELDS as $sf_meta) {
                    $std_labels_lower[] = mb_strtolower(trim($sf_meta[0])); // DE
                    $std_labels_lower[] = mb_strtolower(trim($sf_meta[1])); // EN
                }
                $std_labels_lower = array_merge($std_labels_lower, [
                    'geschlecht', 'gender', 'sorte', 'indica/sativa', 'sorte (indica/sativa)',
                    'thc-gehalt', 'thc gehalt', 'cbd-gehalt', 'cbd gehalt',
                    'kreuzung / genetik', 'genetik', 'cross/genetics',
                    'bluetezeit', 'bluetezeit indoor', 'erntezeitpunkt',
                    'hoehe indoor', 'hoehe outdoor', 'bluetentyp',
                    'geschmack & aroma', 'effekt', 'eigenschaften', 'aroma',
                ]);
                $std_labels_lower = array_unique($std_labels_lower);
                
                $custom_fields = [];
                foreach ($lang_data['custom'] as $cf) {
                    $label = trim($cf['label'] ?? '');
                    $value = trim($cf['value'] ?? '');
                    if (empty($label) && empty($value)) continue;
                    // Skip if label matches a standard field
                    if (in_array(mb_strtolower($label), $std_labels_lower)) continue;
                    $custom_fields[] = ['label' => $label, 'value' => $value];
                }
                if (!empty($custom_fields)) {
                    $data['custom_fields'] = $custom_fields;
                } else {
                    $data['custom_fields'] = '[]'; // Clear duplicates
                }
            }
            
            // Pictos (same for all languages)
            if ($pictos !== null) {
                $data['pictos'] = $pictos;
            }
            
            // Cannabis Cups
            $data['cannabis_cups'] = $cups;
            
            MrhProductAttributes::saveAttributes($products_id, $lang_id, $data, 'manual');
            $saved_langs++;
        }
    }
    
    // Save field order if provided
    if ($field_order !== null) {
        MrhProductAttributes::setConfig('field_order_' . $products_id, json_encode($field_order));
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $saved_langs . ' Sprache(n) gespeichert.',
        'products_id' => $products_id,
        'languages_saved' => $saved_langs
    ]);
    exit;
}

// ============================================================
// AJAX: AI Fill (single product)
// ============================================================
if ($action === 'ai_fill' && isset($_GET['products_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $products_id = (int)$_GET['products_id'];
    
    if (!class_exists('MrhProductAttributes')) {
        echo json_encode(['success' => false, 'message' => 'Module not loaded']);
        exit;
    }
    
    $ai_handler = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_pa_ai_handler.php';
    if (!file_exists($ai_handler)) {
        echo json_encode(['success' => false, 'message' => 'AI handler not found']);
        exit;
    }
    require_once($ai_handler);
    
    $api_key = MrhProductAttributes::getConfig('openai_api_key');
    if (empty($api_key)) {
        echo json_encode(['success' => false, 'message' => defined('MRH_PA_MSG_AI_NO_KEY') ? MRH_PA_MSG_AI_NO_KEY : 'Kein API-Key konfiguriert.']);
        exit;
    }
    
    $result = MrhPaAiHandler::fillProduct($products_id, $api_key);
    echo json_encode($result);
    exit;
}

// ============================================================
// AJAX: Migration batch
// ============================================================
if ($action === 'migrate_batch') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!class_exists('MrhProductAttributes')) {
        echo json_encode(['success' => false, 'message' => 'Module not loaded']);
        exit;
    }
    
    $migration_handler = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_pa_migration.php';
    if (!file_exists($migration_handler)) {
        echo json_encode(['success' => false, 'message' => 'Migration handler not found']);
        exit;
    }
    require_once($migration_handler);
    
    $result = MrhPaMigration::processBatch();
    echo json_encode($result);
    exit;
}

// ============================================================
// AJAX: Migration reset
// ============================================================
if ($action === 'migrate_reset') {
    header('Content-Type: application/json; charset=utf-8');
    MrhProductAttributes::setConfig('migration_last_id', '0');
    MrhProductAttributes::setConfig('migration_status', 'idle');
    echo json_encode(['success' => true, 'message' => 'Migration reset']);
    exit;
}

// ============================================================
// AJAX: AI Batch processing (real implementation)
// ============================================================
if ($action === 'ai_batch') {
    header('Content-Type: application/json; charset=utf-8');
    
    if (!class_exists('MrhProductAttributes')) {
        echo json_encode(['success' => false, 'message' => 'Module not loaded']);
        exit;
    }
    
    $api_key = MrhProductAttributes::getConfig('openai_api_key');
    if (empty($api_key)) {
        echo json_encode(['success' => false, 'message' => defined('MRH_PA_MSG_AI_NO_KEY') ? MRH_PA_MSG_AI_NO_KEY : 'Kein API-Key konfiguriert.']);
        exit;
    }
    
    $ai_handler = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_pa_ai_handler.php';
    if (!file_exists($ai_handler)) {
        echo json_encode(['success' => false, 'message' => 'AI handler not found']);
        exit;
    }
    require_once($ai_handler);
    
    $batch_size = isset($_GET['batch_size']) ? max(1, min(50, (int)$_GET['batch_size'])) : 10;
    $min_fields = isset($_GET['min_fields']) ? max(1, min(10, (int)$_GET['min_fields'])) : 3;
    
    $result = MrhPaAiHandler::processBatchAi($api_key, $batch_size, $min_fields);
    
    // Add total counts for progress calculation
    $total_incomplete_q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt 
        FROM " . MrhProductAttributes::TABLE . " 
        WHERE fields_filled < " . (int)$min_fields);
    $total_incomplete_row = xtc_db_fetch_array($total_incomplete_q);
    $result['remaining'] = (int)$total_incomplete_row['cnt'];
    
    $total_q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt FROM " . MrhProductAttributes::TABLE);
    $total_row = xtc_db_fetch_array($total_q);
    $result['total_with_attrs'] = (int)$total_row['cnt'];
    
    echo json_encode($result);
    exit;
}

// ============================================================
// AJAX: AI Batch count (how many products need AI)
// ============================================================
if ($action === 'ai_batch_count') {
    header('Content-Type: application/json; charset=utf-8');
    
    $min_fields = isset($_GET['min_fields']) ? max(1, min(10, (int)$_GET['min_fields'])) : 3;
    
    // Products with attributes but less than min_fields filled
    $q1 = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt 
        FROM " . MrhProductAttributes::TABLE . " 
        WHERE fields_filled < " . (int)$min_fields);
    $r1 = xtc_db_fetch_array($q1);
    
    // Active products WITHOUT any attributes at all
    $q2 = xtc_db_query("SELECT COUNT(*) as cnt FROM products p 
        WHERE p.products_status = 1 
        AND p.products_id NOT IN (SELECT DISTINCT products_id FROM " . MrhProductAttributes::TABLE . ")");
    $r2 = xtc_db_fetch_array($q2);
    
    echo json_encode([
        'incomplete' => (int)$r1['cnt'],
        'no_attrs' => (int)$r2['cnt'],
        'total_need_ai' => (int)$r1['cnt'] + (int)$r2['cnt'],
    ]);
    exit;
}

// ============================================================
// POST: Save configuration
// ============================================================
if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config_fields = ['openai_api_key', 'openai_model', 'openai_base_url', 'ai_auto_translate', 'min_fields_for_display', 'migration_batch_size'];
    foreach ($config_fields as $key) {
        if (isset($_POST[$key])) {
            MrhProductAttributes::setConfig($key, $_POST[$key]);
        }
    }
    $messageStack->add_session(defined('MRH_PA_MSG_SAVED') ? MRH_PA_MSG_SAVED : 'Gespeichert.', 'success');
    xtc_redirect(xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES));
}

// ============================================================
// AJAX: Stats
// ============================================================
if ($action === 'stats') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(MrhProductAttributes::getMigrationStats());
    exit;
}

// Page display
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'stats';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo defined('MRH_PA_HEADING_TITLE') ? MRH_PA_HEADING_TITLE : 'MRH Produkteigenschaften'; ?></title>
    <?php require(DIR_WS_INCLUDES . 'head.php'); ?>
    <style>
        .mrh-pa-admin { max-width: 1200px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .mrh-pa-admin h1 { font-size: 24px; color: #2c3e50; margin-bottom: 5px; }
        .mrh-pa-admin h1 .fa { color: #27ae60; }
        .mrh-pa-admin .subtitle { color: #7f8c8d; font-size: 14px; margin-bottom: 20px; }
        
        .mrh-pa-tabs { display: flex; gap: 0; border-bottom: 3px solid #27ae60; margin-bottom: 20px; }
        .mrh-pa-tabs a { 
            padding: 10px 20px; text-decoration: none; color: #555; font-weight: 600;
            border: 1px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0;
            transition: all 0.2s;
        }
        .mrh-pa-tabs a:hover { background: #f0fdf4; }
        .mrh-pa-tabs a.active { background: #27ae60; color: #fff; }
        
        .mrh-pa-panel { display: none; }
        .mrh-pa-panel.active { display: block; }
        
        .mrh-pa-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .mrh-pa-card h3 { margin-top: 0; color: #2c3e50; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .mrh-pa-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .mrh-pa-stat { background: #f8f9fa; border-radius: 8px; padding: 20px; text-align: center; }
        .mrh-pa-stat .number { font-size: 32px; font-weight: 700; color: #27ae60; }
        .mrh-pa-stat .label { font-size: 13px; color: #666; margin-top: 5px; }
        
        .mrh-pa-config-row { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 15px; padding: 10px; border-bottom: 1px solid #f0f0f0; }
        .mrh-pa-config-label { width: 200px; font-weight: 600; font-size: 13px; padding-top: 8px; }
        .mrh-pa-config-input { flex: 1; }
        .mrh-pa-config-input input, .mrh-pa-config-input select { 
            width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; box-sizing: border-box;
        }
        .mrh-pa-config-desc { font-size: 11px; color: #999; margin-top: 4px; }
        
        .mrh-pa-btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .mrh-pa-btn-primary { background: #27ae60; color: #fff; }
        .mrh-pa-btn-primary:hover { background: #219a52; }
        .mrh-pa-btn-danger { background: #e74c3c; color: #fff; }
        .mrh-pa-btn-danger:hover { background: #c0392b; }
        .mrh-pa-btn-info { background: #3498db; color: #fff; }
        .mrh-pa-btn-info:hover { background: #2980b9; }
        .mrh-pa-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .mrh-pa-progress { width: 100%; height: 24px; background: #e0e0e0; border-radius: 12px; overflow: hidden; margin: 10px 0; }
        .mrh-pa-progress-bar { height: 100%; background: #27ae60; border-radius: 12px; transition: width 0.3s; text-align: center; color: #fff; font-size: 12px; line-height: 24px; }
        .mrh-pa-progress-bar.ai { background: #3498db; }
        
        .mrh-pa-log { max-height: 300px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px; line-height: 1.6; }
        .mrh-pa-log .success { color: #4ec9b0; }
        .mrh-pa-log .error { color: #f44747; }
        .mrh-pa-log .info { color: #569cd6; }
        .mrh-pa-log .warn { color: #dcdcaa; }
        
        .mrh-pa-batch-config { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; flex-wrap: wrap; }
        .mrh-pa-batch-config label { font-size: 13px; font-weight: 600; }
        .mrh-pa-batch-config input, .mrh-pa-batch-config select { 
            padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; 
        }
        .mrh-pa-batch-config input[type="number"] { width: 80px; }
    </style>
</head>
<body>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="mrh-pa-admin">
    <h1><span class="fa fa-leaf"></span> <?php echo defined('MRH_PA_HEADING_TITLE') ? MRH_PA_HEADING_TITLE : 'MRH Produkteigenschaften'; ?></h1>
    <p class="subtitle"><?php echo defined('MRH_PA_HEADING_SUBTITLE') ? MRH_PA_HEADING_SUBTITLE : 'Strukturierte Produktdaten verwalten'; ?> &mdash; v<?php echo MrhProductAttributes::VERSION; ?></p>
    
    <div class="mrh-pa-tabs">
        <a href="?tab=stats" class="<?php echo $active_tab === 'stats' ? 'active' : ''; ?>">
            <span class="fa fa-chart-bar"></span> <?php echo defined('MRH_PA_TAB_STATS') ? MRH_PA_TAB_STATS : 'Statistik'; ?>
        </a>
        <a href="?tab=config" class="<?php echo $active_tab === 'config' ? 'active' : ''; ?>">
            <span class="fa fa-cog"></span> <?php echo defined('MRH_PA_TAB_CONFIG') ? MRH_PA_TAB_CONFIG : 'Einstellungen'; ?>
        </a>
        <a href="?tab=migration" class="<?php echo $active_tab === 'migration' ? 'active' : ''; ?>">
            <span class="fa fa-database"></span> <?php echo defined('MRH_PA_TAB_MIGRATION') ? MRH_PA_TAB_MIGRATION : 'Migration'; ?>
        </a>
    </div>
    
    <!-- Stats Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'stats' ? 'active' : ''; ?>" id="panel-stats">
        <div class="mrh-pa-card">
            <h3><span class="fa fa-chart-pie"></span> Uebersicht</h3>
            <div class="mrh-pa-stats-grid" id="mrh-pa-stats-grid">
                <div class="mrh-pa-stat"><div class="number" id="stat-total">-</div><div class="label"><?php echo defined('MRH_PA_STATS_TOTAL') ? MRH_PA_STATS_TOTAL : 'Aktive Produkte'; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-with-attrs">-</div><div class="label"><?php echo defined('MRH_PA_STATS_WITH_ATTRS') ? MRH_PA_STATS_WITH_ATTRS : 'Mit Eigenschaften'; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-3plus">-</div><div class="label"><?php echo defined('MRH_PA_STATS_WITH_3PLUS') ? MRH_PA_STATS_WITH_3PLUS : 'Mit 3+ Feldern'; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-manual">-</div><div class="label"><?php echo defined('MRH_PA_STATS_SOURCE_MANUAL') ? MRH_PA_STATS_SOURCE_MANUAL : 'Manuell'; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-migration">-</div><div class="label"><?php echo defined('MRH_PA_STATS_SOURCE_MIGRATION') ? MRH_PA_STATS_SOURCE_MIGRATION : 'Migration'; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-ai">-</div><div class="label"><?php echo defined('MRH_PA_STATS_SOURCE_AI') ? MRH_PA_STATS_SOURCE_AI : 'KI'; ?></div></div>
            </div>
        </div>
    </div>
    
    <!-- Config Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'config' ? 'active' : ''; ?>" id="panel-config">
        <div class="mrh-pa-card">
            <h3><span class="fa fa-cog"></span> <?php echo defined('MRH_PA_TAB_CONFIG') ? MRH_PA_TAB_CONFIG : 'Einstellungen'; ?></h3>
            <form method="post" action="<?php echo xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES, 'action=save_config'); ?>">
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_API_KEY') ? MRH_PA_CONFIG_API_KEY : 'OpenAI API-Key'; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="password" name="openai_api_key" 
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('openai_api_key', '')); ?>"
                               placeholder="sk-...">
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_API_KEY_DESC') ? MRH_PA_CONFIG_API_KEY_DESC : 'API-Key fuer KI-Befuellung.'; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_MODEL') ? MRH_PA_CONFIG_MODEL : 'KI-Modell'; ?></div>
                    <div class="mrh-pa-config-input">
                        <select name="openai_model">
                            <?php 
                            $current_model = MrhProductAttributes::getConfig('openai_model', 'gpt-4.1-nano');
                            $models = ['gpt-4.1-nano' => 'gpt-4.1-nano (schnell, guenstig)', 'gpt-4.1-mini' => 'gpt-4.1-mini (genauer)', 'gemini-2.5-flash' => 'Gemini 2.5 Flash'];
                            foreach ($models as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo $current_model === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_MODEL_DESC') ? MRH_PA_CONFIG_MODEL_DESC : 'Empfohlen: gpt-4.1-nano'; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_BASE_URL') ? MRH_PA_CONFIG_BASE_URL : 'API Base-URL'; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="text" name="openai_base_url" 
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('openai_base_url', '')); ?>"
                               placeholder="https://api.openai.com/v1">
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_BASE_URL_DESC') ? MRH_PA_CONFIG_BASE_URL_DESC : 'Nur bei alternativem Provider aendern.'; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_AUTO_TRANSLATE') ? MRH_PA_CONFIG_AUTO_TRANSLATE : 'Auto-Uebersetzung'; ?></div>
                    <div class="mrh-pa-config-input">
                        <select name="ai_auto_translate">
                            <option value="1" <?php echo MrhProductAttributes::getConfig('ai_auto_translate', '1') === '1' ? 'selected' : ''; ?>>Ja</option>
                            <option value="0" <?php echo MrhProductAttributes::getConfig('ai_auto_translate', '1') === '0' ? 'selected' : ''; ?>>Nein</option>
                        </select>
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_AUTO_TRANSLATE_DESC') ? MRH_PA_CONFIG_AUTO_TRANSLATE_DESC : 'EN/FR/ES automatisch uebersetzen.'; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_MIN_FIELDS') ? MRH_PA_CONFIG_MIN_FIELDS : 'Mindestfelder'; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="number" name="min_fields_for_display" min="1" max="10"
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('min_fields_for_display', '3')); ?>">
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_MIN_FIELDS_DESC') ? MRH_PA_CONFIG_MIN_FIELDS_DESC : 'Mindestanzahl fuer Anzeige.'; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo defined('MRH_PA_CONFIG_BATCH_SIZE') ? MRH_PA_CONFIG_BATCH_SIZE : 'Batchgroesse'; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="number" name="migration_batch_size" min="10" max="500"
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('migration_batch_size', '100')); ?>">
                        <div class="mrh-pa-config-desc"><?php echo defined('MRH_PA_CONFIG_BATCH_SIZE_DESC') ? MRH_PA_CONFIG_BATCH_SIZE_DESC : 'Produkte pro Batch.'; ?></div>
                    </div>
                </div>
                
                <div style="margin-top:20px;">
                    <button type="submit" class="mrh-pa-btn mrh-pa-btn-primary">
                        <span class="fa fa-save"></span> <?php echo defined('MRH_PA_BUTTON_SAVE') ? MRH_PA_BUTTON_SAVE : 'Speichern'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Migration Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'migration' ? 'active' : ''; ?>" id="panel-migration">
        
        <!-- Step 1: DB Migration -->
        <div class="mrh-pa-card">
            <h3><span class="fa fa-database"></span> Schritt 1: DB-Extraktion (TR-Klassen + Picto-Parsing)</h3>
            <p>Extrahiert Produkteigenschaften aus den bestehenden HTML-Tabellen in der Kurzbeschreibung. Parst TR-Klassen (fem, reg, aut, etc.), FontAwesome Picto-Icons und Cannabis Cup Pokale.</p>
            <p><strong>Kosten:</strong> Keine (reine DB-Verarbeitung)</p>
            
            <div class="mrh-pa-progress" id="migration-progress-container" style="display:none;">
                <div class="mrh-pa-progress-bar" id="migration-progress-bar" style="width:0%">0%</div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:15px;">
                <button class="mrh-pa-btn mrh-pa-btn-primary" id="btn-start-migration" onclick="mrhPaStartMigration()">
                    <span class="fa fa-play"></span> <?php echo defined('MRH_PA_BUTTON_MIGRATE_ALL') ? MRH_PA_BUTTON_MIGRATE_ALL : 'Alle Produkte migrieren'; ?>
                </button>
                <button class="mrh-pa-btn mrh-pa-btn-danger" onclick="mrhPaResetMigration()">
                    <span class="fa fa-undo"></span> Migration zuruecksetzen
                </button>
            </div>
            
            <div class="mrh-pa-log" id="mrh-pa-migration-log" style="margin-top:15px;display:none;"></div>
        </div>
        
        <!-- Step 2: AI Batch -->
        <div class="mrh-pa-card">
            <h3><span class="fa fa-magic"></span> Schritt 2: KI-Nachbefuellung</h3>
            <p>Ergaenzt Produkte mit weniger als dem Schwellenwert gefuellter Felder per KI-Analyse der Beschreibung. Verarbeitet automatisch in konfigurierbaren Batches.</p>
            <p><strong>Kosten:</strong> ~$1-3 fuer 10.000 Produkte mit gpt-4.1-nano</p>
            
            <!-- Batch configuration -->
            <div class="mrh-pa-batch-config">
                <label>Batch-Groesse:</label>
                <input type="number" id="ai-batch-size" value="10" min="1" max="50" title="Produkte pro API-Anfrage">
                
                <label>Min. Felder:</label>
                <input type="number" id="ai-min-fields" value="3" min="1" max="10" title="Produkte mit weniger als X Feldern werden befuellt">
                
                <button class="mrh-pa-btn mrh-pa-btn-info" id="btn-check-ai" onclick="mrhPaCheckAiBatch()" style="padding:6px 14px;font-size:13px;">
                    <span class="fa fa-search"></span> Pruefen
                </button>
            </div>
            
            <div id="ai-batch-info" style="font-size:13px;color:#666;margin-bottom:10px;display:none;"></div>
            
            <div class="mrh-pa-progress" id="ai-progress-container" style="display:none;">
                <div class="mrh-pa-progress-bar ai" id="ai-progress-bar" style="width:0%">0%</div>
            </div>
            
            <div style="display:flex;gap:10px;">
                <button class="mrh-pa-btn mrh-pa-btn-info" id="btn-start-ai" onclick="mrhPaStartAiBatch()">
                    <span class="fa fa-magic"></span> <?php echo defined('MRH_PA_BUTTON_MIGRATE_AI') ? MRH_PA_BUTTON_MIGRATE_AI : 'KI-Batch starten'; ?>
                </button>
                <button class="mrh-pa-btn mrh-pa-btn-danger" id="btn-stop-ai" onclick="mrhPaStopAiBatch()" style="display:none;">
                    <span class="fa fa-stop"></span> Stoppen
                </button>
            </div>
            
            <div class="mrh-pa-log" id="mrh-pa-ai-log" style="margin-top:15px;display:none;"></div>
        </div>
    </div>
</div>

<script>
// ============================================================
// STATS
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('panel-stats').classList.contains('active')) {
        loadStats();
    }
});

function loadStats() {
    fetch('mrh_product_attributes.php?action=stats')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('stat-total').textContent = data.total_active_products || 0;
            document.getElementById('stat-with-attrs').textContent = data.products_with_attributes || 0;
            document.getElementById('stat-3plus').textContent = data.products_with_3plus_fields || 0;
            document.getElementById('stat-manual').textContent = data.source_manual || 0;
            document.getElementById('stat-migration').textContent = data.source_migration || 0;
            document.getElementById('stat-ai').textContent = data.source_ai || 0;
        })
        .catch(function(e) { console.error('Stats error:', e); });
}

// ============================================================
// MIGRATION (Step 1)
// ============================================================
var migrationRunning = false;
function mrhPaStartMigration() {
    if (migrationRunning) return;
    migrationRunning = true;
    document.getElementById('btn-start-migration').disabled = true;
    
    document.getElementById('migration-progress-container').style.display = 'block';
    var log = document.getElementById('mrh-pa-migration-log');
    log.style.display = 'block';
    log.innerHTML = '<div class="info">[Start] Migration gestartet (TR-Klassen + Picto-Parsing)...</div>';
    
    processMigrationBatch();
}

function processMigrationBatch() {
    fetch('mrh_product_attributes.php?action=migrate_batch')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var log = document.getElementById('mrh-pa-migration-log');
            var bar = document.getElementById('migration-progress-bar');
            
            if (data.processed !== undefined) {
                var pct = data.total > 0 ? Math.round((data.processed_total / data.total) * 100) : 0;
                bar.style.width = pct + '%';
                bar.textContent = pct + '% (' + data.processed_total + '/' + data.total + ')';
                
                log.innerHTML += '<div class="success">[Batch] ' + data.processed + ' verarbeitet, ' + 
                    data.migrated + ' migriert, ' + data.skipped + ' uebersprungen</div>';
                log.scrollTop = log.scrollHeight;
            }
            
            if (data.done) {
                log.innerHTML += '<div class="info">[Fertig] Migration abgeschlossen!</div>';
                migrationRunning = false;
                document.getElementById('btn-start-migration').disabled = false;
                loadStats();
            } else {
                setTimeout(processMigrationBatch, 500);
            }
        })
        .catch(function(e) {
            document.getElementById('mrh-pa-migration-log').innerHTML += '<div class="error">[Fehler] ' + e.message + '</div>';
            migrationRunning = false;
            document.getElementById('btn-start-migration').disabled = false;
        });
}

function mrhPaResetMigration() {
    if (confirm('Migration wirklich zuruecksetzen? Bereits migrierte Daten bleiben erhalten.')) {
        fetch('mrh_product_attributes.php?action=migrate_reset')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('migration-progress-bar').style.width = '0%';
                document.getElementById('migration-progress-bar').textContent = '0%';
                alert('Migration zurueckgesetzt.');
            });
    }
}

// ============================================================
// AI BATCH (Step 2) - REAL IMPLEMENTATION
// ============================================================
var aiBatchRunning = false;
var aiBatchTotalProcessed = 0;
var aiBatchTotalSuccess = 0;
var aiBatchTotalFailed = 0;
var aiBatchInitialCount = 0;

function mrhPaCheckAiBatch() {
    var minFields = document.getElementById('ai-min-fields').value;
    var infoEl = document.getElementById('ai-batch-info');
    infoEl.style.display = 'block';
    infoEl.innerHTML = '<span class="fa fa-spinner fa-spin"></span> Zaehle Produkte...';
    
    fetch('mrh_product_attributes.php?action=ai_batch_count&min_fields=' + minFields)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            infoEl.innerHTML = '<strong>' + data.incomplete + '</strong> Produkte mit &lt;' + minFields + ' Feldern, ' +
                '<strong>' + data.no_attrs + '</strong> ohne jegliche Attribute. ' +
                '<strong>Gesamt: ' + data.total_need_ai + '</strong> Produkte benoetigen KI-Befuellung.';
            aiBatchInitialCount = data.total_need_ai;
        })
        .catch(function(e) {
            infoEl.innerHTML = '<span style="color:red">Fehler: ' + e.message + '</span>';
        });
}

function mrhPaStartAiBatch() {
    if (aiBatchRunning) return;
    aiBatchRunning = true;
    aiBatchTotalProcessed = 0;
    aiBatchTotalSuccess = 0;
    aiBatchTotalFailed = 0;
    
    document.getElementById('btn-start-ai').disabled = true;
    document.getElementById('btn-stop-ai').style.display = 'inline-block';
    document.getElementById('ai-progress-container').style.display = 'block';
    
    var log = document.getElementById('mrh-pa-ai-log');
    log.style.display = 'block';
    log.innerHTML = '<div class="info">[Start] KI-Batch gestartet...</div>';
    
    // First check count
    var minFields = document.getElementById('ai-min-fields').value;
    fetch('mrh_product_attributes.php?action=ai_batch_count&min_fields=' + minFields)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            aiBatchInitialCount = data.incomplete || data.total_need_ai || 100;
            log.innerHTML += '<div class="info">[Info] ' + aiBatchInitialCount + ' Produkte zu verarbeiten</div>';
            processAiBatch();
        })
        .catch(function(e) {
            aiBatchInitialCount = 100;
            processAiBatch();
        });
}

function processAiBatch() {
    if (!aiBatchRunning) {
        finishAiBatch('Gestoppt durch Benutzer.');
        return;
    }
    
    var batchSize = document.getElementById('ai-batch-size').value;
    var minFields = document.getElementById('ai-min-fields').value;
    
    fetch('mrh_product_attributes.php?action=ai_batch&batch_size=' + batchSize + '&min_fields=' + minFields)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success && data.success !== undefined && !data.processed) {
                // Error response
                var log = document.getElementById('mrh-pa-ai-log');
                log.innerHTML += '<div class="error">[Fehler] ' + (data.message || 'Unbekannter Fehler') + '</div>';
                finishAiBatch('Fehler aufgetreten.');
                return;
            }
            
            aiBatchTotalProcessed += (data.processed || 0);
            aiBatchTotalSuccess += (data.success || 0);
            aiBatchTotalFailed += (data.failed || 0);
            
            // Update progress
            var bar = document.getElementById('ai-progress-bar');
            var pct = aiBatchInitialCount > 0 ? Math.min(100, Math.round((aiBatchTotalProcessed / aiBatchInitialCount) * 100)) : 0;
            bar.style.width = pct + '%';
            bar.textContent = pct + '% (' + aiBatchTotalProcessed + '/' + aiBatchInitialCount + ')';
            
            // Log
            var log = document.getElementById('mrh-pa-ai-log');
            if (data.processed > 0) {
                log.innerHTML += '<div class="success">[Batch] ' + data.processed + ' verarbeitet (' + 
                    (data.success || 0) + ' OK, ' + (data.failed || 0) + ' Fehler) | Verbleibend: ' + 
                    (data.remaining || '?') + '</div>';
            }
            log.scrollTop = log.scrollHeight;
            
            if (data.done) {
                finishAiBatch('Alle Produkte verarbeitet!');
            } else if (!aiBatchRunning) {
                finishAiBatch('Gestoppt durch Benutzer.');
            } else {
                // Continue with next batch (1s delay to avoid rate limiting)
                setTimeout(processAiBatch, 1000);
            }
        })
        .catch(function(e) {
            var log = document.getElementById('mrh-pa-ai-log');
            log.innerHTML += '<div class="error">[Netzwerk-Fehler] ' + e.message + '</div>';
            // Retry after 5 seconds
            log.innerHTML += '<div class="warn">[Retry] Versuche erneut in 5 Sekunden...</div>';
            setTimeout(processAiBatch, 5000);
        });
}

function finishAiBatch(msg) {
    aiBatchRunning = false;
    document.getElementById('btn-start-ai').disabled = false;
    document.getElementById('btn-stop-ai').style.display = 'none';
    
    var log = document.getElementById('mrh-pa-ai-log');
    log.innerHTML += '<div class="info">[Fertig] ' + msg + ' Gesamt: ' + aiBatchTotalProcessed + 
        ' verarbeitet (' + aiBatchTotalSuccess + ' OK, ' + aiBatchTotalFailed + ' Fehler)</div>';
    log.scrollTop = log.scrollHeight;
    
    loadStats();
}

function mrhPaStopAiBatch() {
    aiBatchRunning = false;
    var log = document.getElementById('mrh-pa-ai-log');
    log.innerHTML += '<div class="warn">[Stop] Batch wird nach aktuellem Durchlauf gestoppt...</div>';
}
</script>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
