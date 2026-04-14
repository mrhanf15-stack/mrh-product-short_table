<?php
/**
 * MRH Product Attributes - Main Module Class
 * 
 * Handles database operations, self-installation, and data access
 * for structured product attributes (gender, THC, CBD, cross, etc.)
 *
 * @package MRH_Product_Attributes
 * @version 1.5.0
 */

if (!defined('TABLE_CONFIGURATION')) { return; }

class MrhProductAttributes {
    
    /** @var string Module version */
    const VERSION = '1.8.0';
    
    /** @var string DB table name */
    const TABLE = 'mrh_product_attributes';
    const CONFIG_TABLE = 'mrh_product_attributes_config';
    
    /**
     * Standard fields with their metadata.
     * key => [label_de, label_en, type, priority_level, field_order, tr_class]
     * priority_level: 1 = Prio, 2 = Alt-Prio (Fallback), 0 = Normal
     */
    const STANDARD_FIELDS = [
        'gender'         => ['Geschlecht', 'Gender', 'select', 0, 1, 'fem,reg'],
        'flowering_type' => ['Bluetentyp', 'Flowering Type', 'select', 0, 2, 'aut'],
        'growing'        => ['Anbau', 'Growing', 'select', 0, 3, 'anbau'],
        'type'           => ['Sorte', 'Type (Indica/Sativa)', 'select', 1, 4, 'sort'],
        'thc'            => ['THC', 'THC', 'text', 1, 5, 'thc'],
        'cbd'            => ['CBD', 'CBD', 'text', 1, 6, 'cbd_w'],
        'cross_genetics'  => ['Kreuzung', 'Cross/Genetics', 'text', 2, 7, 'kreuzung'],
        'flowering_time' => ['Bluetezeit', 'Flowering Time', 'text', 2, 8, 'bluete'],
        'yield_indoor'   => ['Ertrag Indoor', 'Yield Indoor', 'text', 2, 9, 'ertrag_in'],
        'harvest_time'   => ['Erntezeit', 'Harvest Time', 'text', 2, 10, 'ernte'],
        'yield_outdoor'  => ['Ertrag Outdoor', 'Yield Outdoor', 'text', 0, 11, 'ertrag_out'],
        'height_indoor'  => ['Hoehe Indoor', 'Height Indoor', 'text', 0, 12, 'hoehe_in'],
        'height_outdoor' => ['Hoehe Outdoor', 'Height Outdoor', 'text', 0, 13, 'hoehe_out'],
        'climate'        => ['Klima', 'Climate', 'text', 0, 14, 'klima'],
        'effect'         => ['Wirkung', 'Effect', 'text', 0, 15, 'wirkung'],
        'taste'          => ['Geschmack', 'Taste', 'text', 0, 16, 'geschmack'],
    ];
    
    const GENDER_OPTIONS = [
        'feminized'  => ['Feminisiert', 'Feminized', 'Féminisée', 'Feminizada'],
        'regular'    => ['Regulaer', 'Regular', 'Régulière', 'Regular'],
        'autoflower' => ['Autoflowering', 'Autoflowering', 'Autofloraison', 'Autofloreciente'],
    ];
    
    const FLOWERING_TYPE_OPTIONS = [
        'photoperiod'  => ['Photoperiodisch', 'Photoperiod', 'Photopériode', 'Fotoperíodo'],
        'autoflower'   => ['Autoflowering', 'Autoflowering', 'Autofloraison', 'Autofloreciente'],
    ];
    
    const TYPE_OPTIONS = [
        'indica'        => ['Indica', 'Indica', 'Indica', 'Indica'],
        'sativa'        => ['Sativa', 'Sativa', 'Sativa', 'Sativa'],
        'hybrid'        => ['Hybrid', 'Hybrid', 'Hybride', 'Híbrido'],
        'indica_dom'    => ['Indica-dominant', 'Indica Dominant', 'Indica dominante', 'Indica dominante'],
        'sativa_dom'    => ['Sativa-dominant', 'Sativa Dominant', 'Sativa dominante', 'Sativa dominante'],
    ];
    
    const GROWING_OPTIONS = [
        'indoor'     => ['Indoor', 'Indoor', 'Intérieur', 'Interior'],
        'outdoor'    => ['Outdoor', 'Outdoor', 'Extérieur', 'Exterior'],
        'greenhouse' => ['Gewaechshaus', 'Greenhouse', 'Serre', 'Invernadero'],
        'all'        => ['Indoor/Outdoor', 'Indoor/Outdoor', 'Intérieur/Extérieur', 'Interior/Exterior'],
    ];
    
    /** @var array Language map: language_code => language_id */
    private static $language_map = null;
    
    /**
     * Check if the module tables exist and install if needed.
     */
    public static function checkAndInstall() {
        $check = xtc_db_query("SHOW TABLES LIKE '" . self::TABLE . "'");
        if (xtc_db_num_rows($check) == 0) {
            self::installTables();
            return true;
        }
        
        $config_check = xtc_db_query("SHOW TABLES LIKE '" . self::CONFIG_TABLE . "'");
        if (xtc_db_num_rows($config_check) > 0) {
            $version_q = xtc_db_query("SELECT config_value FROM " . self::CONFIG_TABLE . " WHERE config_key = 'module_version'");
            if (xtc_db_num_rows($version_q) > 0) {
                $row = xtc_db_fetch_array($version_q);
                if (version_compare($row['config_value'], self::VERSION, '<')) {
                    self::runMigrations($row['config_value']);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Install the database tables.
     */
    private static function installTables() {
        xtc_db_query("CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `products_id` INT(11) NOT NULL,
            `language_id` INT(11) NOT NULL DEFAULT 1,
            `gender` VARCHAR(32) DEFAULT NULL COMMENT 'feminized|regular|autoflower',
            `flowering_type` VARCHAR(32) DEFAULT NULL COMMENT 'photoperiod|autoflower',
            `cross_genetics` VARCHAR(512) DEFAULT NULL COMMENT 'Kreuzung / Genetics',
            `thc` VARCHAR(64) DEFAULT NULL,
            `cbd` VARCHAR(64) DEFAULT NULL,
            `type` VARCHAR(64) DEFAULT NULL COMMENT 'indica|sativa|hybrid|indica_dom|sativa_dom',
            `yield_indoor` VARCHAR(128) DEFAULT NULL,
            `yield_outdoor` VARCHAR(128) DEFAULT NULL,
            `height_indoor` VARCHAR(128) DEFAULT NULL,
            `height_outdoor` VARCHAR(128) DEFAULT NULL,
            `flowering_time` VARCHAR(128) DEFAULT NULL,
            `harvest_time` VARCHAR(128) DEFAULT NULL,
            `climate` VARCHAR(256) DEFAULT NULL,
            `effect` VARCHAR(512) DEFAULT NULL,
            `taste` VARCHAR(512) DEFAULT NULL,
            `growing` VARCHAR(64) DEFAULT NULL COMMENT 'indoor|outdoor|greenhouse|all',
            `pictos` TEXT DEFAULT NULL COMMENT 'JSON: [{icon,color,size,title}]',
            `cannabis_cups` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of Cannabis Cup awards',
            `custom_fields` TEXT DEFAULT NULL COMMENT 'JSON: additional user-defined fields',
            `is_seed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=seed product, 0=non-seed',
            `data_source` ENUM('manual','migration','ai','import') NOT NULL DEFAULT 'manual',
            `ai_confidence` DECIMAL(3,2) DEFAULT NULL COMMENT 'AI confidence score 0.00-1.00',
            `fields_filled` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of filled fields',
            `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `product_language` (`products_id`, `language_id`),
            KEY `idx_products_id` (`products_id`),
            KEY `idx_gender` (`gender`),
            KEY `idx_is_seed` (`is_seed`),
            KEY `idx_fields_filled` (`fields_filled`),
            KEY `idx_data_source` (`data_source`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        xtc_db_query("CREATE TABLE IF NOT EXISTS `" . self::CONFIG_TABLE . "` (
            `config_key` VARCHAR(64) NOT NULL,
            `config_value` TEXT DEFAULT NULL,
            `last_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`config_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        xtc_db_query("INSERT INTO `" . self::CONFIG_TABLE . "` (`config_key`, `config_value`) VALUES
            ('module_version', '" . self::VERSION . "'),
            ('openai_api_key', ''),
            ('openai_model', 'gpt-4.1-nano'),
            ('openai_base_url', ''),
            ('ai_auto_translate', '1'),
            ('primary_language_id', '1'),
            ('min_fields_for_display', '3'),
            ('migration_batch_size', '100'),
            ('migration_last_id', '0'),
            ('migration_status', 'idle')
        ON DUPLICATE KEY UPDATE `config_key` = `config_key`;");
    }
    
    /**
     * Run database migrations between versions.
     */
    private static function runMigrations($from_version) {
        // v1.1.0: Add pictos JSON + cannabis_cups fields
        if (version_compare($from_version, '1.1.0', '<')) {
            $col_check = xtc_db_query("SHOW COLUMNS FROM " . self::TABLE . " LIKE 'pictos'");
            if (xtc_db_num_rows($col_check) == 0) {
                xtc_db_query("ALTER TABLE " . self::TABLE . " ADD COLUMN `pictos` TEXT DEFAULT NULL COMMENT 'JSON: [{icon,color,size,title}]' AFTER `growing`");
            }
            $col_check2 = xtc_db_query("SHOW COLUMNS FROM " . self::TABLE . " LIKE 'cannabis_cups'");
            if (xtc_db_num_rows($col_check2) == 0) {
                xtc_db_query("ALTER TABLE " . self::TABLE . " ADD COLUMN `cannabis_cups` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Number of Cannabis Cup awards' AFTER `pictos`");
            }
        }
        
        xtc_db_query("UPDATE " . self::CONFIG_TABLE . " SET config_value = '" . self::VERSION . "' WHERE config_key = 'module_version'");
    }
    
    /**
     * Get the language map (code => id).
     */
    public static function getLanguageMap() {
        if (self::$language_map === null) {
            self::$language_map = [];
            $q = xtc_db_query("SELECT languages_id, code, name, directory FROM languages ORDER BY sort_order");
            while ($row = xtc_db_fetch_array($q)) {
                self::$language_map[$row['code']] = $row;
            }
        }
        return self::$language_map;
    }
    
    /**
     * Get attributes for a product (single language).
     */
    public static function getAttributes($products_id, $language_id = 0) {
        if ($language_id == 0) {
            $language_id = (int)$_SESSION['languages_id'];
        }
        
        $q = xtc_db_query("SELECT * FROM " . self::TABLE . " 
            WHERE products_id = " . (int)$products_id . " 
            AND language_id = " . (int)$language_id . " 
            LIMIT 1");
        
        if (xtc_db_num_rows($q) > 0) {
            $row = xtc_db_fetch_array($q);
            if (!empty($row['custom_fields'])) {
                $row['custom_fields_decoded'] = json_decode($row['custom_fields'], true);
            }
            if (!empty($row['pictos'])) {
                $row['pictos_decoded'] = json_decode($row['pictos'], true);
            }
            return $row;
        }
        
        return null;
    }
    
    /**
     * Get attributes for a product (all languages).
     */
    public static function getAllLanguageAttributes($products_id) {
        $result = [];
        $q = xtc_db_query("SELECT * FROM " . self::TABLE . " 
            WHERE products_id = " . (int)$products_id . " 
            ORDER BY language_id");
        
        while ($row = xtc_db_fetch_array($q)) {
            if (!empty($row['custom_fields'])) {
                $row['custom_fields_decoded'] = json_decode($row['custom_fields'], true);
            }
            if (!empty($row['pictos'])) {
                $row['pictos_decoded'] = json_decode($row['pictos'], true);
            }
            $result[(int)$row['language_id']] = $row;
        }
        
        return $result;
    }
    
    /**
     * Save attributes for a product (single language).
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert.
     */
    public static function saveAttributes($products_id, $language_id, $data, $source = 'manual') {
        $products_id = (int)$products_id;
        $language_id = (int)$language_id;
        
        $allowed = [
            'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
            'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
            'flowering_time', 'harvest_time', 'climate', 'effect', 'taste',
            'growing', 'pictos', 'cannabis_cups', 'custom_fields', 'is_seed', 'ai_confidence'
        ];
        
        $fields = ['products_id' => $products_id, 'language_id' => $language_id];
        $fields_filled = 0;
        
        foreach ($allowed as $col) {
            if (isset($data[$col])) {
                $val = $data[$col];
                if ($col === 'custom_fields' && is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                if ($col === 'pictos' && is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $fields[$col] = xtc_db_input($val);
                if (!empty($val) && $col !== 'is_seed' && $col !== 'ai_confidence' && $col !== 'custom_fields' && $col !== 'pictos' && $col !== 'cannabis_cups') {
                    $fields_filled++;
                }
            }
        }
        
        // Count custom fields
        if (!empty($data['custom_fields'])) {
            $custom = is_array($data['custom_fields']) ? $data['custom_fields'] : json_decode($data['custom_fields'], true);
            if (is_array($custom)) {
                foreach ($custom as $cf) {
                    if (!empty($cf['value'])) {
                        $fields_filled++;
                    }
                }
            }
        }
        
        $fields['fields_filled'] = $fields_filled;
        $fields['data_source'] = xtc_db_input($source);
        
        // Build INSERT ... ON DUPLICATE KEY UPDATE
        $cols = [];
        $vals = [];
        $updates = [];
        
        foreach ($fields as $col => $val) {
            $cols[] = '`' . $col . '`';
            if (is_int($val) || is_float($val)) {
                $vals[] = $val;
                if ($col !== 'products_id' && $col !== 'language_id') {
                    $updates[] = '`' . $col . '` = ' . $val;
                }
            } else {
                $vals[] = "'" . $val . "'";
                if ($col !== 'products_id' && $col !== 'language_id') {
                    $updates[] = '`' . $col . "` = '" . $val . "'";
                }
            }
        }
        
        $sql = "INSERT INTO " . self::TABLE . " (" . implode(', ', $cols) . ") 
                VALUES (" . implode(', ', $vals) . ") 
                ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        
        xtc_db_query($sql);
        
        return true;
    }
    
    /**
     * Delete attributes for a product (all languages).
     */
    public static function deleteAttributes($products_id) {
        xtc_db_query("DELETE FROM " . self::TABLE . " WHERE products_id = " . (int)$products_id);
        return true;
    }
    
    /**
     * Get a config value.
     */
    public static function getConfig($key, $default = null) {
        $q = xtc_db_query("SELECT config_value FROM " . self::CONFIG_TABLE . " 
            WHERE config_key = '" . xtc_db_input($key) . "' LIMIT 1");
        if (xtc_db_num_rows($q) > 0) {
            $row = xtc_db_fetch_array($q);
            return $row['config_value'];
        }
        return $default;
    }
    
    /**
     * Set a config value.
     */
    public static function setConfig($key, $value) {
        xtc_db_query("INSERT INTO " . self::CONFIG_TABLE . " (config_key, config_value) 
            VALUES ('" . xtc_db_input($key) . "', '" . xtc_db_input($value) . "') 
            ON DUPLICATE KEY UPDATE config_value = '" . xtc_db_input($value) . "'");
        return true;
    }
    
    /**
     * Count products with attributes.
     */
    public static function countProducts($min_fields = 0) {
        $sql = "SELECT COUNT(DISTINCT products_id) as cnt FROM " . self::TABLE;
        if ($min_fields > 0) {
            $sql .= " WHERE fields_filled >= " . (int)$min_fields;
        }
        $q = xtc_db_query($sql);
        $row = xtc_db_fetch_array($q);
        return (int)$row['cnt'];
    }
    
    /**
     * Get migration statistics.
     */
    public static function getMigrationStats() {
        $stats = [];
        
        $q = xtc_db_query("SELECT COUNT(*) as cnt FROM products WHERE products_status = 1");
        $row = xtc_db_fetch_array($q);
        $stats['total_active_products'] = (int)$row['cnt'];
        
        $stats['products_with_attributes'] = self::countProducts();
        $stats['products_with_3plus_fields'] = self::countProducts(3);
        
        foreach (['manual', 'migration', 'ai', 'import'] as $source) {
            $q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt FROM " . self::TABLE . " WHERE data_source = '" . $source . "'");
            $row = xtc_db_fetch_array($q);
            $stats['source_' . $source] = (int)$row['cnt'];
        }
        
        $stats['migration_status'] = self::getConfig('migration_status', 'idle');
        $stats['migration_last_id'] = (int)self::getConfig('migration_last_id', 0);
        
        return $stats;
    }
    
    /**
     * Build a mini-table HTML from structured attributes.
     * 
     * LISTING/BOX context: Always exactly 3 rows.
     * DETAIL context: Show all filled fields (no 3-row limit).
     */
    public static function buildMiniTable($attrs, $context = 'listing') {
        if (empty($attrs)) return '';
        
        $field_labels = self::STANDARD_FIELDS;
        
        // DETAIL context: show all filled fields, no limit
        if ($context === 'detail' || $context === 'compare' || $context === 'seedfinder') {
            $rows = [];
            $all_fields = ['type', 'thc', 'cbd', 'cross_genetics', 'flowering_time', 
                'yield_indoor', 'harvest_time', 'yield_outdoor', 'height_indoor', 
                'height_outdoor', 'climate', 'effect', 'taste', 'growing'];
            
            // Apply custom field order if saved
            $products_id = (int)($attrs['products_id'] ?? 0);
            if ($products_id > 0) {
                $saved_order_json = self::getConfig('field_order_' . $products_id);
                if (!empty($saved_order_json)) {
                    $saved_order = json_decode($saved_order_json, true);
                    if (is_array($saved_order) && !empty($saved_order)) {
                        // Filter to only include fields that are in our all_fields list
                        // and add any missing fields at the end
                        $ordered = [];
                        foreach ($saved_order as $f) {
                            if (in_array($f, $all_fields)) {
                                $ordered[] = $f;
                            }
                        }
                        // Add any fields not in saved order
                        foreach ($all_fields as $f) {
                            if (!in_array($f, $ordered)) {
                                $ordered[] = $f;
                            }
                        }
                        $all_fields = $ordered;
                    }
                }
            }
            
            foreach ($all_fields as $field) {
                $db_field = $field;
                $label_key = $field;
                
                if (!empty($attrs[$db_field])) {
                    $value = htmlspecialchars($attrs[$db_field]);
                    if (in_array($field, ['gender', 'flowering_type', 'type', 'growing'])) {
                        $value = self::translateSelectValue($field, $attrs[$db_field]);
                    }
                    $label = isset($field_labels[$label_key]) ? $field_labels[$label_key][0] : ucfirst($field);
                    $tr_class = isset($field_labels[$label_key]) ? $field_labels[$label_key][5] : '';
                    $rows[] = '<tr class="' . htmlspecialchars($tr_class) . '"><td>' . htmlspecialchars($label) . '</td><td>' . $value . '</td></tr>';
                }
            }
            
            // Custom fields
            if (!empty($attrs['custom_fields_decoded']) && is_array($attrs['custom_fields_decoded'])) {
                foreach ($attrs['custom_fields_decoded'] as $cf) {
                    if (!empty($cf['value'])) {
                        $rows[] = '<tr class="custom"><td>' . htmlspecialchars($cf['label'] ?? '') . '</td><td>' . htmlspecialchars($cf['value']) . '</td></tr>';
                    }
                }
            }
            
            if (empty($rows)) return '';
            $class = 'mrh-attr-table mrh-attr-' . $context;
            return '<table class="' . $class . ' tebals"><tbody>' . implode('', $rows) . '</tbody></table>';
        }
        
        // LISTING/BOX context: Always exactly 3 rows
        $prio_fields = [
            ['key' => 'type',  'label_key' => 'type'],
            ['key' => 'thc',   'label_key' => 'thc'],
            ['key' => 'cbd',   'label_key' => 'cbd'],
        ];
        
        $alt_prio_fields = [
            ['key' => 'flowering_time',  'label_key' => 'flowering_time'],
            ['key' => 'yield_indoor',    'label_key' => 'yield_indoor'],
            ['key' => 'harvest_time',    'label_key' => 'harvest_time'],
            ['key' => 'cross_genetics',  'label_key' => 'cross_genetics'],
        ];
        
        $rows = [];
        $alt_idx = 0;
        
        foreach ($prio_fields as $pf) {
            $db_field = $pf['key'];
            $label_key = $pf['label_key'];
            $value_raw = $attrs[$db_field] ?? '';
            
            if (empty(trim($value_raw))) {
                $found_alt = false;
                while ($alt_idx < count($alt_prio_fields)) {
                    $af = $alt_prio_fields[$alt_idx];
                    $alt_val = $attrs[$af['key']] ?? '';
                    $alt_idx++;
                    if (!empty(trim($alt_val))) {
                        $db_field = $af['key'];
                        $label_key = $af['label_key'];
                        $value_raw = $alt_val;
                        $found_alt = true;
                        break;
                    }
                }
                
                if (!$found_alt) {
                    $label = isset($field_labels[$pf['label_key']]) ? $field_labels[$pf['label_key']][0] : ucfirst($pf['key']);
                    $tr_class = isset($field_labels[$pf['label_key']]) ? $field_labels[$pf['label_key']][5] : '';
                    $rows[] = '<tr class="' . htmlspecialchars($tr_class) . '"><td>' . htmlspecialchars($label) . '</td><td>&mdash;</td></tr>';
                    continue;
                }
            }
            
            $value = htmlspecialchars($value_raw);
            if (in_array($db_field, ['gender', 'flowering_type', 'type', 'growing'])) {
                $value = self::translateSelectValue($db_field, $value_raw);
            }
            $label = isset($field_labels[$label_key]) ? $field_labels[$label_key][0] : ucfirst($db_field);
            $tr_class = isset($field_labels[$label_key]) ? $field_labels[$label_key][5] : '';
            $rows[] = '<tr class="' . htmlspecialchars($tr_class) . '"><td>' . htmlspecialchars($label) . '</td><td>' . $value . '</td></tr>';
        }
        
        $class = 'mrh-attr-table mrh-attr-' . $context;
        return '<table class="' . $class . ' tebals"><tbody>' . implode('', $rows) . '</tbody></table>';
    }
    
    /**
     * Translate a select field value to the current language.
     */
    private static function translateSelectValue($field, $value) {
        $lang_idx = 0; // Default: German
        if (isset($_SESSION['language_code'])) {
            $codes = ['de' => 0, 'en' => 1, 'fr' => 2, 'es' => 3];
            $lang_idx = $codes[$_SESSION['language_code']] ?? 0;
        }
        
        $options_map = [
            'gender'         => self::GENDER_OPTIONS,
            'flowering_type' => self::FLOWERING_TYPE_OPTIONS,
            'type'           => self::TYPE_OPTIONS,
            'growing'        => self::GROWING_OPTIONS,
        ];
        
        if (isset($options_map[$field][$value][$lang_idx])) {
            return $options_map[$field][$value][$lang_idx];
        }
        
        return $value;
    }
    
    /**
     * Build badge HTML from structured attributes.
     * 
     * Now renders:
     * 1. Gender badge (feminized/regular)
     * 2. Flowering type badge (autoflower/photoperiod)
     * 3. Custom picto icons from DB (with color and size)
     * 4. Cannabis Cup trophy badge (with count)
     *
     * Uses the original configurator structure:
     * <span class="picto templatestyle">
     *   <span class="mrh-badge-bar">
     *     <span class="mrh-type-badge mrh-badge-xxx" title="...">
     *       <span class="fa fa-fw fa-xxx"></span>
     *     </span>
     *   </span>
     * </span>
     *
     * @param array $attrs Attributes row from DB
     * @return string HTML
     */
    public static function buildBadgeHTML($attrs) {
        if (empty($attrs)) return '';
        
        $badges = [];
        $gender = $attrs['gender'] ?? '';
        $flowering = $attrs['flowering_type'] ?? '';
        
        // 1. Gender badge – uses global badge config from DB
        if ($gender === 'feminized') {
            $fem_cfg = self::getBadgeConfig('gender_feminized');
            $show = !empty($fem_cfg['show_text']);
            if ($fem_cfg['is_svg']) {
                $badges[] = self::badgeSpanSvg('fem', $fem_cfg['icon'], self::translateSelectValue('gender', 'feminized'), $show);
            } else {
                $badges[] = self::badgeSpan('fem', $fem_cfg['icon'], self::translateSelectValue('gender', 'feminized'), $fem_cfg['style'], $show);
            }
        } elseif ($gender === 'regular') {
            $reg_cfg = self::getBadgeConfig('gender_regular');
            $show = !empty($reg_cfg['show_text']);
            if ($reg_cfg['is_svg']) {
                $badges[] = self::badgeSpanSvg('reg', $reg_cfg['icon'], self::translateSelectValue('gender', 'regular'), $show);
            } else {
                $badges[] = self::badgeSpan('reg', $reg_cfg['icon'], self::translateSelectValue('gender', 'regular'), $reg_cfg['style'], $show);
            }
        } elseif ($gender === 'autoflower') {
            $fem_cfg = self::getBadgeConfig('gender_feminized');
            $show = !empty($fem_cfg['show_text']);
            if ($fem_cfg['is_svg']) {
                $badges[] = self::badgeSpanSvg('fem', $fem_cfg['icon'], self::translateSelectValue('gender', 'autoflower'), $show);
            } else {
                $badges[] = self::badgeSpan('fem', $fem_cfg['icon'], self::translateSelectValue('gender', 'autoflower'), $fem_cfg['style'], $show);
            }
        }
        
        // 2. Flowering type badge
        //    - Autoflowering: Icon badge (fa-gauge-high)
        //    - Photoperiodisch: Sun Icon badge (fa-sun)
        if ($flowering === 'autoflower') {
            $auto_cfg = self::getBadgeConfig('flowering_autoflower');
            $show = !empty($auto_cfg['show_text']);
            $badges[] = self::badgeSpan('auto', $auto_cfg['icon'], self::translateSelectValue('flowering_type', 'autoflower'), $auto_cfg['style'], $show);
        } elseif ($flowering === 'photoperiod') {
            $photo_cfg = self::getBadgeConfig('flowering_photoperiod');
            $show = !empty($photo_cfg['show_text']);
            $badges[] = self::badgeSpan('photo', $photo_cfg['icon'], self::translateSelectValue('flowering_type', 'photoperiod'), $photo_cfg['style'], $show);
        }
        
        // 3. Custom picto icons from DB (supports FA classes and SVG paths)
        $pictos = [];
        if (!empty($attrs['pictos'])) {
            $pictos = is_array($attrs['pictos']) ? $attrs['pictos'] : (json_decode($attrs['pictos'], true) ?: []);
        } elseif (!empty($attrs['pictos_decoded'])) {
            $pictos = $attrs['pictos_decoded'];
        }
        
        if (!empty($pictos)) {
            foreach ($pictos as $picto) {
                $icon_val = $picto['icon'] ?? '';
                $color = $picto['color'] ?? '#333';
                $size = $picto['size'] ?? '16px';
                $title = $picto['title'] ?? '';
                
                if (empty($icon_val)) continue;
                
                // Check if SVG icon (starts with svg: or contains .svg)
                $is_svg = (strpos($icon_val, 'svg:') === 0 || strpos($icon_val, '.svg') !== false);
                
                // Read bgcolor and bordercolor
                $bgcolor = $picto['bgcolor'] ?? '';
                $bordercolor = $picto['bordercolor'] ?? '';
                $badge_style = '';
                if ($bgcolor && $bgcolor !== '#ffffff') {
                    $badge_style .= 'background:' . htmlspecialchars($bgcolor) . ';';
                }
                if ($bordercolor && $bordercolor !== '#dddddd') {
                    $badge_style .= 'border-color:' . htmlspecialchars($bordercolor) . ';';
                }
                
                if ($is_svg) {
                    // SVG badge
                    $svg_path = str_replace('svg:', '', $icon_val);
                    $svg_url = '/' . ltrim($svg_path, '/');
                    $size_px = intval($size) ?: 16;
                    $badges[] = '<span class="mrh-type-badge mrh-badge-picto" title="' . htmlspecialchars($title) . '"' .
                        ($badge_style ? ' style="' . $badge_style . '"' : '') . '>' .
                        '<img src="' . htmlspecialchars($svg_url) . '" alt="' . htmlspecialchars($title) . '" ' .
                        'style="width:' . $size_px . 'px;height:' . $size_px . 'px;vertical-align:middle" class="mrh-badge-svg">' .
                        ($title ? '<span class="mrh-badge-text">' . htmlspecialchars($title) . '</span>' : '') .
                        '</span>';
                } else {
                    // FontAwesome badge (FA7 format: fa-solid/fa-regular/fa-brands)
                    $icon_class = $icon_val;
                    // Normalize icon class: ensure it starts with fa-
                    if (strpos($icon_class, 'fa-') === false && strpos($icon_class, 'fa ') === false) {
                        $icon_class = 'fa-' . $icon_class;
                    }
                    // Remove legacy "fa " prefix if present
                    $icon_class = preg_replace('/^fa\s+/', '', $icon_class);
                    
                    // Skip icons that duplicate the gender/flowering badges
                    if (in_array($icon_class, ['fa-venus', 'fa-mars', 'fa-bolt', 'fa-sun'])) continue;
                    
                    // Determine FA style prefix (FA7: fa-solid, fa-regular, fa-brands)
                    $fa_style = $picto['style'] ?? 'solid';
                    $fa_prefix = 'fa-solid';
                    if ($fa_style === 'regular') $fa_prefix = 'fa-regular';
                    elseif ($fa_style === 'brands') $fa_prefix = 'fa-brands';
                    
                    $style = '';
                    if ($color && $color !== '#333333' && $color !== '#333') {
                        $style .= 'color:' . htmlspecialchars($color) . ';';
                    }
                    $size_px = intval($size) ?: 16;
                    if ($size_px !== 16) {
                        $style .= 'font-size:' . $size_px . 'px;';
                    }
                    
                    $combined_style = $style . $badge_style;
                    $badges[] = '<span class="mrh-type-badge mrh-badge-picto" title="' . htmlspecialchars($title) . '"' .
                        ($combined_style ? ' style="' . $combined_style . '"' : '') . '>' .
                        '<span class="' . $fa_prefix . ' fa-fw ' . htmlspecialchars($icon_class) . '"></span>' .
                        ($title ? '<span class="mrh-badge-text">' . htmlspecialchars($title) . '</span>' : '') .
                        '</span>';
                }
            }
        }
        
        // 4. Cannabis Cup trophy badge (max 3 trophies + number if > 3)
        $cups = (int)($attrs['cannabis_cups'] ?? 0);
        if ($cups > 0) {
            $cup_title = $cups . ' Cannabis Cup' . ($cups > 1 ? ' Awards' : ' Award');
            $cup_html = '<span class="mrh-type-badge mrh-badge-cup" title="' . htmlspecialchars($cup_title) . '">';
            $trophy_count = min($cups, 3);
            for ($t = 0; $t < $trophy_count; $t++) {
                $cup_html .= '<span class="fa-solid fa-fw fa-trophy"></span>';
            }
            if ($cups > 3) {
                $cup_html .= '<span class="mrh-cup-count">' . $cups . '</span>';
            }
            $cup_html .= '</span>';
            $badges[] = $cup_html;
        }
        
        if (empty($badges)) return '';
        
        return '<span class="picto templatestyle"><span class="mrh-badge-bar">' 
            . implode('', $badges) 
            . '</span></span>';
    }
    
    /**
     * Build a single badge span.
     */
    private static function badgeSpan($type, $icon, $title, $fa_style = 'solid', $show_text = false) {
        $fa_prefix = 'fa-solid';
        if ($fa_style === 'regular') $fa_prefix = 'fa-regular';
        elseif ($fa_style === 'brands') $fa_prefix = 'fa-brands';
        $html = '<span class="mrh-type-badge mrh-badge-' . $type . '" title="' . htmlspecialchars($title) . '">' 
            . '<span class="' . $fa_prefix . ' fa-fw ' . $icon . '"></span>';
        if ($show_text && !empty($title)) {
            $html .= '<span class="mrh-badge-label">' . htmlspecialchars($title) . '</span>';
        }
        $html .= '</span>';
        return $html;
    }
    
    /**
     * Build a single SVG badge span.
     */
    private static function badgeSpanSvg($type, $svg_path, $title, $show_text = false) {
        $svg_url = '/' . ltrim($svg_path, '/');
        $html = '<span class="mrh-type-badge mrh-badge-' . $type . '" title="' . htmlspecialchars($title) . '">' 
            . '<img src="' . htmlspecialchars($svg_url) . '" alt="' . htmlspecialchars($title) . '" style="width:14px;height:14px;vertical-align:middle" class="mrh-badge-svg">';
        if ($show_text && !empty($title)) {
            $html .= '<span class="mrh-badge-label">' . htmlspecialchars($title) . '</span>';
        }
        $html .= '</span>';
        return $html;
    }
    
    /**
     * Build a TEXT-ONLY badge (no icon).
     * Legacy method – kept for backward compatibility.
     * Photoperiodisch now uses fa-sun icon via badgeSpan().
     */
    private static function badgeTextOnly($type, $title) {
        return '<span class="mrh-type-badge mrh-badge-' . $type . ' mrh-badge-textonly" title="' . htmlspecialchars($title) . '">' 
            . '<span class="mrh-badge-label">' . htmlspecialchars($title) . '</span>'
            . '</span>';
    }
    
    /**
     * Default badge configurations.
     * These are the hardcoded defaults; they can be overridden
     * via the config table (key: badge_config_{badge_key}).
     */
    const DEFAULT_BADGE_CONFIG = [
        'gender_feminized' => [
            'icon'  => 'fa-venus',
            'style' => 'solid',
            'is_svg' => false,
            'color' => '',
            'show_text' => false,
        ],
        'gender_regular' => [
            'icon'  => 'templates/tpl_mrh_2026/img/badges/male.svg',
            'style' => 'solid',
            'is_svg' => true,
            'color' => '',
            'show_text' => false,
        ],
        'flowering_autoflower' => [
            'icon'  => 'fa-gauge-high',
            'style' => 'solid',
            'is_svg' => false,
            'color' => '',
            'show_text' => false,
        ],
        'flowering_photoperiod' => [
            'icon'  => 'fa-sun',
            'style' => 'solid',
            'is_svg' => false,
            'color' => '',
            'show_text' => false,
        ],
    ];
    
    /**
     * Get badge configuration from DB config table.
     * Falls back to DEFAULT_BADGE_CONFIG if not set.
     *
     * @param string $badge_key e.g. 'gender_feminized', 'flowering_autoflower'
     * @return array Badge config with keys: icon, style, is_svg, color
     */
    public static function getBadgeConfig($badge_key) {
        $default = self::DEFAULT_BADGE_CONFIG[$badge_key] ?? [
            'icon' => '', 'style' => 'solid', 'is_svg' => false, 'color' => ''
        ];
        
        $stored = self::getConfig('badge_config_' . $badge_key);
        if (!empty($stored)) {
            $parsed = json_decode($stored, true);
            if (is_array($parsed)) {
                return array_merge($default, $parsed);
            }
        }
        
        return $default;
    }
    
    /**
     * Save badge configuration to DB config table.
     *
     * @param string $badge_key e.g. 'gender_feminized'
     * @param array $config Badge config array
     */
    public static function saveBadgeConfig($badge_key, $config) {
        return self::setConfig('badge_config_' . $badge_key, json_encode($config));
    }
    
    /**
     * Get all badge configurations (for admin display).
     * Returns merged defaults + any DB overrides.
     */
    public static function getAllBadgeConfigs() {
        $configs = [];
        foreach (self::DEFAULT_BADGE_CONFIG as $key => $default) {
            $configs[$key] = self::getBadgeConfig($key);
        }
        return $configs;
    }
    
    /**
     * Check if a product is a seed product.
     */
    public static function isSeedProduct($products_id) {
        $q = xtc_db_query("SELECT is_seed FROM " . self::TABLE . " 
            WHERE products_id = " . (int)$products_id . " LIMIT 1");
        if (xtc_db_num_rows($q) > 0) {
            $row = xtc_db_fetch_array($q);
            return (bool)$row['is_seed'];
        }
        
        return null; // Unknown
    }
}
