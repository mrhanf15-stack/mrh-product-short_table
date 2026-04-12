<?php
/**
 * MRH Product Attributes - Admin Page
 * 
 * Standalone admin page for:
 * - Module configuration (API key, model, settings)
 * - Migration tools (bulk import from short descriptions)
 * - Statistics dashboard
 * - AI batch processing
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
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

if ($action === 'ai_fill' && isset($_GET['products_id'])) {
    // AI Fill for a single product
    header('Content-Type: application/json; charset=utf-8');
    
    $products_id = (int)$_GET['products_id'];
    
    if (!class_exists('MrhProductAttributes')) {
        echo json_encode(['success' => false, 'message' => 'Module not loaded']);
        exit;
    }
    
    // Load AI handler
    $ai_handler = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_pa_ai_handler.php';
    if (!file_exists($ai_handler)) {
        echo json_encode(['success' => false, 'message' => 'AI handler not found']);
        exit;
    }
    require_once($ai_handler);
    
    $api_key = MrhProductAttributes::getConfig('openai_api_key');
    if (empty($api_key)) {
        echo json_encode(['success' => false, 'message' => MRH_PA_MSG_AI_NO_KEY]);
        exit;
    }
    
    $result = MrhPaAiHandler::fillProduct($products_id, $api_key);
    echo json_encode($result);
    exit;
}

if ($action === 'migrate_batch') {
    // Migration batch processing
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

if ($action === 'migrate_reset') {
    header('Content-Type: application/json; charset=utf-8');
    MrhProductAttributes::setConfig('migration_last_id', '0');
    MrhProductAttributes::setConfig('migration_status', 'idle');
    echo json_encode(['success' => true, 'message' => 'Migration reset']);
    exit;
}

if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save configuration
    $config_fields = ['openai_api_key', 'openai_model', 'openai_base_url', 'ai_auto_translate', 'min_fields_for_display', 'migration_batch_size'];
    foreach ($config_fields as $key) {
        if (isset($_POST[$key])) {
            MrhProductAttributes::setConfig($key, $_POST[$key]);
        }
    }
    $messageStack->add_session(MRH_PA_MSG_SAVED, 'success');
    xtc_redirect(xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES));
}

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
    <title><?php echo MRH_PA_HEADING_TITLE; ?></title>
    <?php require(DIR_WS_INCLUDES . 'head.php'); ?>
    <style>
        .mrh-pa-admin { max-width: 1200px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .mrh-pa-admin h1 { font-size: 24px; color: #2c3e50; margin-bottom: 5px; }
        .mrh-pa-admin h1 i { color: #27ae60; }
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
        
        .mrh-pa-progress { width: 100%; height: 24px; background: #e0e0e0; border-radius: 12px; overflow: hidden; margin: 10px 0; }
        .mrh-pa-progress-bar { height: 100%; background: #27ae60; border-radius: 12px; transition: width 0.3s; text-align: center; color: #fff; font-size: 12px; line-height: 24px; }
        
        #mrh-pa-migration-log { max-height: 300px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px; line-height: 1.6; }
        #mrh-pa-migration-log .success { color: #4ec9b0; }
        #mrh-pa-migration-log .error { color: #f44747; }
        #mrh-pa-migration-log .info { color: #569cd6; }
    </style>
</head>
<body>
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<div class="mrh-pa-admin">
    <h1><i class="fa fa-leaf"></i> <?php echo MRH_PA_HEADING_TITLE; ?></h1>
    <p class="subtitle"><?php echo MRH_PA_HEADING_SUBTITLE; ?> &mdash; v<?php echo MrhProductAttributes::VERSION; ?></p>
    
    <div class="mrh-pa-tabs">
        <a href="?tab=stats" class="<?php echo $active_tab === 'stats' ? 'active' : ''; ?>">
            <i class="fa fa-chart-bar"></i> <?php echo MRH_PA_TAB_STATS; ?>
        </a>
        <a href="?tab=config" class="<?php echo $active_tab === 'config' ? 'active' : ''; ?>">
            <i class="fa fa-cog"></i> <?php echo MRH_PA_TAB_CONFIG; ?>
        </a>
        <a href="?tab=migration" class="<?php echo $active_tab === 'migration' ? 'active' : ''; ?>">
            <i class="fa fa-database"></i> <?php echo MRH_PA_TAB_MIGRATION; ?>
        </a>
    </div>
    
    <!-- Stats Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'stats' ? 'active' : ''; ?>" id="panel-stats">
        <div class="mrh-pa-card">
            <h3><i class="fa fa-chart-pie"></i> Uebersicht</h3>
            <div class="mrh-pa-stats-grid" id="mrh-pa-stats-grid">
                <div class="mrh-pa-stat"><div class="number" id="stat-total">-</div><div class="label"><?php echo MRH_PA_STATS_TOTAL; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-with-attrs">-</div><div class="label"><?php echo MRH_PA_STATS_WITH_ATTRS; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-3plus">-</div><div class="label"><?php echo MRH_PA_STATS_WITH_3PLUS; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-manual">-</div><div class="label"><?php echo MRH_PA_STATS_SOURCE_MANUAL; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-migration">-</div><div class="label"><?php echo MRH_PA_STATS_SOURCE_MIGRATION; ?></div></div>
                <div class="mrh-pa-stat"><div class="number" id="stat-ai">-</div><div class="label"><?php echo MRH_PA_STATS_SOURCE_AI; ?></div></div>
            </div>
        </div>
    </div>
    
    <!-- Config Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'config' ? 'active' : ''; ?>" id="panel-config">
        <div class="mrh-pa-card">
            <h3><i class="fa fa-cog"></i> <?php echo MRH_PA_TAB_CONFIG; ?></h3>
            <form method="post" action="<?php echo xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES, 'action=save_config'); ?>">
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_API_KEY; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="password" name="openai_api_key" 
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('openai_api_key', '')); ?>"
                               placeholder="sk-...">
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_API_KEY_DESC; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_MODEL; ?></div>
                    <div class="mrh-pa-config-input">
                        <select name="openai_model">
                            <?php 
                            $current_model = MrhProductAttributes::getConfig('openai_model', 'gpt-4.1-nano');
                            $models = ['gpt-4.1-nano' => 'gpt-4.1-nano (schnell, guenstig)', 'gpt-4.1-mini' => 'gpt-4.1-mini (genauer)', 'gemini-2.5-flash' => 'Gemini 2.5 Flash'];
                            foreach ($models as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo $current_model === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_MODEL_DESC; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_BASE_URL; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="text" name="openai_base_url" 
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('openai_base_url', '')); ?>"
                               placeholder="https://api.openai.com/v1">
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_BASE_URL_DESC; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_AUTO_TRANSLATE; ?></div>
                    <div class="mrh-pa-config-input">
                        <select name="ai_auto_translate">
                            <option value="1" <?php echo MrhProductAttributes::getConfig('ai_auto_translate', '1') === '1' ? 'selected' : ''; ?>>Ja</option>
                            <option value="0" <?php echo MrhProductAttributes::getConfig('ai_auto_translate', '1') === '0' ? 'selected' : ''; ?>>Nein</option>
                        </select>
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_AUTO_TRANSLATE_DESC; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_MIN_FIELDS; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="number" name="min_fields_for_display" min="1" max="10"
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('min_fields_for_display', '3')); ?>">
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_MIN_FIELDS_DESC; ?></div>
                    </div>
                </div>
                
                <div class="mrh-pa-config-row">
                    <div class="mrh-pa-config-label"><?php echo MRH_PA_CONFIG_BATCH_SIZE; ?></div>
                    <div class="mrh-pa-config-input">
                        <input type="number" name="migration_batch_size" min="10" max="500"
                               value="<?php echo htmlspecialchars(MrhProductAttributes::getConfig('migration_batch_size', '100')); ?>">
                        <div class="mrh-pa-config-desc"><?php echo MRH_PA_CONFIG_BATCH_SIZE_DESC; ?></div>
                    </div>
                </div>
                
                <div style="margin-top:20px;">
                    <button type="submit" class="mrh-pa-btn mrh-pa-btn-primary">
                        <i class="fa fa-save"></i> <?php echo MRH_PA_BUTTON_SAVE; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Migration Panel -->
    <div class="mrh-pa-panel <?php echo $active_tab === 'migration' ? 'active' : ''; ?>" id="panel-migration">
        <div class="mrh-pa-card">
            <h3><i class="fa fa-database"></i> Schritt 1: DB-Extraktion (TR-Klassen-Parsing)</h3>
            <p>Extrahiert Produkteigenschaften aus den bestehenden HTML-Tabellen in der Kurzbeschreibung. Nutzt die sprachunabhaengigen TR-Klassen (fem, reg, aut, kreuzung, cbd_w, etc.).</p>
            <p><strong>Kosten:</strong> Keine (reine DB-Verarbeitung)</p>
            
            <div class="mrh-pa-progress" id="migration-progress-container" style="display:none;">
                <div class="mrh-pa-progress-bar" id="migration-progress-bar" style="width:0%">0%</div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:15px;">
                <button class="mrh-pa-btn mrh-pa-btn-primary" onclick="mrhPaStartMigration()">
                    <i class="fa fa-play"></i> <?php echo MRH_PA_BUTTON_MIGRATE_ALL; ?>
                </button>
                <button class="mrh-pa-btn mrh-pa-btn-danger" onclick="mrhPaResetMigration()">
                    <i class="fa fa-undo"></i> Migration zuruecksetzen
                </button>
            </div>
            
            <div id="mrh-pa-migration-log" style="margin-top:15px;display:none;"></div>
        </div>
        
        <div class="mrh-pa-card">
            <h3><i class="fa fa-magic"></i> Schritt 2: KI-Nachbefuellung</h3>
            <p>Ergaenzt Produkte mit weniger als 3 gefuellten Feldern per KI-Analyse der Beschreibung.</p>
            <p><strong>Kosten:</strong> ~$1-3 fuer 10.000 Produkte mit gpt-4.1-nano</p>
            
            <div class="mrh-pa-progress" id="ai-progress-container" style="display:none;">
                <div class="mrh-pa-progress-bar" id="ai-progress-bar" style="width:0%">0%</div>
            </div>
            
            <button class="mrh-pa-btn mrh-pa-btn-info" onclick="mrhPaStartAiBatch()" style="margin-top:15px;">
                <i class="fa fa-magic"></i> <?php echo MRH_PA_BUTTON_MIGRATE_AI; ?>
            </button>
        </div>
    </div>
</div>

<script>
// Load stats on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('panel-stats').classList.contains('active')) {
        loadStats();
    }
});

function loadStats() {
    fetch('mrh_product_attributes.php?action=stats')
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total_active_products || 0;
            document.getElementById('stat-with-attrs').textContent = data.products_with_attributes || 0;
            document.getElementById('stat-3plus').textContent = data.products_with_3plus_fields || 0;
            document.getElementById('stat-manual').textContent = data.source_manual || 0;
            document.getElementById('stat-migration').textContent = data.source_migration || 0;
            document.getElementById('stat-ai').textContent = data.source_ai || 0;
        })
        .catch(e => console.error('Stats error:', e));
}

// Migration
var migrationRunning = false;
function mrhPaStartMigration() {
    if (migrationRunning) return;
    migrationRunning = true;
    
    document.getElementById('migration-progress-container').style.display = 'block';
    var log = document.getElementById('mrh-pa-migration-log');
    log.style.display = 'block';
    log.innerHTML = '<div class="info">[Start] Migration gestartet...</div>';
    
    processMigrationBatch();
}

function processMigrationBatch() {
    fetch('mrh_product_attributes.php?action=migrate_batch')
        .then(r => r.json())
        .then(data => {
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
                loadStats();
            } else {
                // Continue with next batch
                setTimeout(processMigrationBatch, 500);
            }
        })
        .catch(e => {
            document.getElementById('mrh-pa-migration-log').innerHTML += '<div class="error">[Fehler] ' + e.message + '</div>';
            migrationRunning = false;
        });
}

function mrhPaResetMigration() {
    if (confirm('Migration wirklich zuruecksetzen? Bereits migrierte Daten bleiben erhalten.')) {
        fetch('mrh_product_attributes.php?action=migrate_reset')
            .then(r => r.json())
            .then(data => {
                document.getElementById('migration-progress-bar').style.width = '0%';
                document.getElementById('migration-progress-bar').textContent = '0%';
                alert('Migration zurueckgesetzt.');
            });
    }
}

function mrhPaStartAiBatch() {
    alert('KI-Batch-Verarbeitung wird in Phase 2 implementiert. Bitte zuerst den API-Key unter Einstellungen konfigurieren.');
}
</script>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
