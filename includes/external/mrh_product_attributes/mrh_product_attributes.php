<?php
/**
 * MRH Product Attributes - Main Module Class
 * 
 * Handles database operations, self-installation, and data access
 * for structured product attributes (gender, THC, CBD, cross, etc.)
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MrhProductAttributes {
    
    /** @var string Module version */
    const VERSION = '1.0.0';
    
    /** @var string DB table name */
    const TABLE = 'mrh_product_attributes';
    const CONFIG_TABLE = 'mrh_product_attributes_config';
    
    /**
     * Standard fields with their metadata.
     * key => [label_de, label_en, type, priority_level, field_order, tr_class]
     * priority_level: 1 = Prio, 2 = Alt-Prio (Fallback), 0 = Normal
     *
     * Prio-Felder (immer bevorzugt): Sorte, THC, CBD
     * Alt-Prio (Fallback wenn Prio leer): Bluetezeit, Ertrag Indoor, Erntezeit, Kreuzung
     */
    const STANDARD_FIELDS = [
        'gender'         => ['Geschlecht', 'Gender', 'select', 0, 1, 'fem,reg'],
        'flowering_type' => ['Bluetentyp', 'Flowering Type', 'select', 0, 2, 'aut'],
        'type'           => ['Sorte', 'Type (Indica/Sativa)', 'select', 1, 3, 'sort'],
        'thc'            => ['THC', 'THC', 'text', 1, 4, 'thc'],
        'cbd'            => ['CBD', 'CBD', 'text', 1, 5, 'cbd_w'],
        'cross_genetics'  => ['Kreuzung', 'Cross/Genetics', 'text', 2, 6, 'kreuzung'],
        'flowering_time' => ['Bluetezeit', 'Flowering Time', 'text', 2, 7, 'bluete'],
        'yield_indoor'   => ['Ertrag Indoor', 'Yield Indoor', 'text', 2, 8, 'ertrag_in'],
        'harvest_time'   => ['Erntezeit', 'Harvest Time', 'text', 2, 9, 'ernte'],
        'yield_outdoor'  => ['Ertrag Outdoor', 'Yield Outdoor', 'text', 0, 10, 'ertrag_out'],
        'height_indoor'  => ['Hoehe Indoor', 'Height Indoor', 'text', 0, 11, 'hoehe_in'],
        'height_outdoor' => ['Hoehe Outdoor', 'Height Outdoor', 'text', 0, 12, 'hoehe_out'],
        'climate'        => ['Klima', 'Climate', 'text', 0, 13, 'klima'],
        'effect'         => ['Wirkung', 'Effect', 'text', 0, 14, 'wirkung'],
        'taste'          => ['Geschmack', 'Taste', 'text', 0, 15, 'geschmack'],
        'growing'        => ['Anbau', 'Growing', 'select', 0, 16, 'anbau'],
    ];
    
    /**
     * Gender options
     */
    const GENDER_OPTIONS = [
        'feminized'  => ['Feminisiert', 'Feminized', 'Féminisée', 'Feminizada'],
        'regular'    => ['Regulaer', 'Regular', 'Régulière', 'Regular'],
        'autoflower' => ['Autoflowering', 'Autoflowering', 'Autofloraison', 'Autofloreciente'],
    ];
    
    /**
     * Flowering type options
     */
    const FLOWERING_TYPE_OPTIONS = [
        'photoperiod'  => ['Photoperiodisch', 'Photoperiod', 'Photopériode', 'Fotoperíodo'],
        'autoflower'   => ['Autoflowering', 'Autoflowering', 'Autofloraison', 'Autofloreciente'],
    ];
    
    /**
     * Type options (Indica/Sativa)
     */
    const TYPE_OPTIONS = [
        'indica'        => ['Indica', 'Indica', 'Indica', 'Indica'],
        'sativa'        => ['Sativa', 'Sativa', 'Sativa', 'Sativa'],
        'hybrid'        => ['Hybrid', 'Hybrid', 'Hybride', 'Híbrido'],
        'indica_dom'    => ['Indica-dominant', 'Indica Dominant', 'Indica dominante', 'Indica dominante'],
        'sativa_dom'    => ['Sativa-dominant', 'Sativa Dominant', 'Sativa dominante', 'Sativa dominante'],
    ];
    
    /**
     * Growing options
     */
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
     * Called on every admin page load via autoinclude.
     *
     * @return bool True if tables exist (or were just created)
     */
    public static function checkAndInstall() {
        // Check if main table exists
        $check = xtc_db_query("SHOW TABLES LIKE '" . self::TABLE . "'");
        if (xtc_db_num_rows($check) == 0) {
            self::installTables();
            return true;
        }
        
        // Check version and run migrations if needed
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
        // Main attributes table: one row per product per language
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
        
        // Config table for module settings
        xtc_db_query("CREATE TABLE IF NOT EXISTS `" . self::CONFIG_TABLE . "` (
            `config_key` VARCHAR(64) NOT NULL,
            `config_value` TEXT DEFAULT NULL,
            `last_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`config_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // Insert default config values
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
     *
     * @param string $from_version Current installed version
     */
    private static function runMigrations($from_version) {
        // Future migrations go here
        // Example:
        // if (version_compare($from_version, '1.1.0', '<')) {
        //     xtc_db_query("ALTER TABLE ...");
        // }
        
        // Update version
        xtc_db_query("UPDATE " . self::CONFIG_TABLE . " SET config_value = '" . self::VERSION . "' WHERE config_key = 'module_version'");
    }
    
    /**
     * Get the language map (code => id).
     *
     * @return array
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
     *
     * @param int $products_id
     * @param int $language_id
     * @return array|null
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
            // Decode custom_fields JSON
            if (!empty($row['custom_fields'])) {
                $row['custom_fields_decoded'] = json_decode($row['custom_fields'], true);
            }
            return $row;
        }
        
        return null;
    }
    
    /**
     * Get attributes for a product (all languages).
     *
     * @param int $products_id
     * @return array [language_id => attributes]
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
            $result[(int)$row['language_id']] = $row;
        }
        
        return $result;
    }
    
    /**
     * Save attributes for a product (single language).
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for upsert.
     *
     * @param int $products_id
     * @param int $language_id
     * @param array $data Key-value pairs of fields
     * @param string $source Data source (manual|migration|ai|import)
     * @return bool
     */
    public static function saveAttributes($products_id, $language_id, $data, $source = 'manual') {
        $products_id = (int)$products_id;
        $language_id = (int)$language_id;
        
        // Allowed DB columns
        $allowed = [
            'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
            'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
            'flowering_time', 'harvest_time', 'climate', 'effect', 'taste',
            'growing', 'custom_fields', 'is_seed', 'ai_confidence'
        ];
        
        // Filter and escape data
        $fields = ['products_id' => $products_id, 'language_id' => $language_id];
        $fields_filled = 0;
        
        foreach ($allowed as $col) {
            if (isset($data[$col])) {
                $val = $data[$col];
                if ($col === 'custom_fields' && is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $fields[$col] = xtc_db_input($val);
                if (!empty($val) && $col !== 'is_seed' && $col !== 'ai_confidence' && $col !== 'custom_fields') {
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
     *
     * @param int $products_id
     * @return bool
     */
    public static function deleteAttributes($products_id) {
        xtc_db_query("DELETE FROM " . self::TABLE . " WHERE products_id = " . (int)$products_id);
        return true;
    }
    
    /**
     * Get a config value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
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
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function setConfig($key, $value) {
        xtc_db_query("INSERT INTO " . self::CONFIG_TABLE . " (config_key, config_value) 
            VALUES ('" . xtc_db_input($key) . "', '" . xtc_db_input($value) . "') 
            ON DUPLICATE KEY UPDATE config_value = '" . xtc_db_input($value) . "'");
        return true;
    }
    
    /**
     * Count products with attributes.
     *
     * @param int $min_fields Minimum filled fields
     * @return int
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
     *
     * @return array
     */
    public static function getMigrationStats() {
        $stats = [];
        
        // Total products in shop
        $q = xtc_db_query("SELECT COUNT(*) as cnt FROM products WHERE products_status = 1");
        $row = xtc_db_fetch_array($q);
        $stats['total_active_products'] = (int)$row['cnt'];
        
        // Products with attributes
        $stats['products_with_attributes'] = self::countProducts();
        
        // Products with >= 3 fields
        $stats['products_with_3plus_fields'] = self::countProducts(3);
        
        // By source
        foreach (['manual', 'migration', 'ai', 'import'] as $source) {
            $q = xtc_db_query("SELECT COUNT(DISTINCT products_id) as cnt FROM " . self::TABLE . " WHERE data_source = '" . $source . "'");
            $row = xtc_db_fetch_array($q);
            $stats['source_' . $source] = (int)$row['cnt'];
        }
        
        // Migration status
        $stats['migration_status'] = self::getConfig('migration_status', 'idle');
        $stats['migration_last_id'] = (int)self::getConfig('migration_last_id', 0);
        
        return $stats;
    }
    
    /**
     * Build a mini-table HTML from structured attributes.
     * Used in listings, boxes, seedfinder, compare.
     *
     * LISTING/BOX context: Always exactly 3 rows.
     * - Prio-Felder (priority_level=1): Sorte, THC, CBD
     * - Alt-Prio (priority_level=2): Bluetezeit, Ertrag Indoor, Erntezeit, Kreuzung
     * - If a Prio field is empty, fill with next available Alt-Prio field
     * - If still empty, show placeholder '—'
     *
     * DETAIL context: Show all filled fields (no 3-row limit).
     *
     * @param array $attrs Attributes row from DB
     * @param string $context 'listing'|'box'|'seedfinder'|'compare'|'detail'
     * @return string HTML
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
            
            foreach ($all_fields as $field) {
                $db_field = $field;
                $label_key = ($field === 'cross_genetics') ? 'cross' : $field;
                
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
        // Prio fields in order: Sorte, THC, CBD
        $prio_fields = [
            ['key' => 'type',  'label_key' => 'type'],
            ['key' => 'thc',   'label_key' => 'thc'],
            ['key' => 'cbd',   'label_key' => 'cbd'],
        ];
        
        // Alt-Prio fields (fallback pool): Bluetezeit, Ertrag Indoor, Erntezeit, Kreuzung
        $alt_prio_fields = [
            ['key' => 'flowering_time',  'label_key' => 'flowering_time'],
            ['key' => 'yield_indoor',    'label_key' => 'yield_indoor'],
            ['key' => 'harvest_time',    'label_key' => 'harvest_time'],
            ['key' => 'cross_genetics',  'label_key' => 'cross'],
        ];
        
        // Build the 3 rows
        $rows = [];
        $alt_idx = 0; // Pointer into alt-prio pool
        
        foreach ($prio_fields as $pf) {
            $db_field = $pf['key'];
            $label_key = $pf['label_key'];
            $value_raw = $attrs[$db_field] ?? '';
            
            // If prio field is empty, try alt-prio fields
            if (empty(trim($value_raw))) {
                $found_alt = false;
                while ($alt_idx < count($alt_prio_fields)) {
                    $af = $alt_prio_fields[$alt_idx];
                    $alt_val = $attrs[$af['key']] ?? '';
                    $alt_idx++;
                    if (!empty(trim($alt_val))) {
                        // Use this alt-prio field instead
                        $db_field = $af['key'];
                        $label_key = $af['label_key'];
                        $value_raw = $alt_val;
                        $found_alt = true;
                        break;
                    }
                }
                
                if (!$found_alt) {
                    // No alt-prio available: show placeholder
                    $label = isset($field_labels[$pf['label_key']]) ? $field_labels[$pf['label_key']][0] : ucfirst($pf['key']);
                    $tr_class = isset($field_labels[$pf['label_key']]) ? $field_labels[$pf['label_key']][5] : '';
                    $rows[] = '<tr class="' . htmlspecialchars($tr_class) . '"><td>' . htmlspecialchars($label) . '</td><td>&mdash;</td></tr>';
                    continue;
                }
            }
            
            // Render the row
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
     *
     * @param string $field
     * @param string $value
     * @return string
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
     * Uses the original configurator structure:
     * <span class="picto templatestyle">
     *   <span class="mrh-badge-bar">
     *     <span class="mrh-type-badge mrh-badge-fem" title="Feminisiert">
     *       <span class="fa fa-fw fa-venus"></span>
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
        
        // Gender badge
        if ($gender === 'feminized') {
            $badges[] = self::badgeSpan('fem', 'fa-venus', self::translateSelectValue('gender', 'feminized'));
        } elseif ($gender === 'regular') {
            $badges[] = self::badgeSpan('reg', 'fa-mars-and-venus', self::translateSelectValue('gender', 'regular'));
        } elseif ($gender === 'autoflower') {
            $badges[] = self::badgeSpan('fem', 'fa-venus', self::translateSelectValue('gender', 'autoflower'));
        }
        
        // Flowering type badge
        if ($flowering === 'autoflower') {
            $badges[] = self::badgeSpan('auto', 'fa-bolt', self::translateSelectValue('flowering_type', 'autoflower'));
        } elseif ($flowering === 'photoperiod') {
            $badges[] = self::badgeSpan('photo', 'fa-sun', self::translateSelectValue('flowering_type', 'photoperiod'));
        }
        
        if (empty($badges)) return '';
        
        return '<span class="picto templatestyle"><span class="mrh-badge-bar">' 
            . implode('', $badges) 
            . '</span></span>';
    }
    
    /**
     * Build a single badge span.
     *
     * @param string $type Badge type class suffix (fem|reg|auto|photo)
     * @param string $icon Font Awesome icon class
     * @param string $title Title/tooltip text
     * @return string HTML
     */
    private static function badgeSpan($type, $icon, $title) {
        return '<span class="mrh-type-badge mrh-badge-' . $type . '" title="' . htmlspecialchars($title) . '">'
            . '<span class="fa fa-fw ' . $icon . '"></span>'
            . '</span>';
    }
    
    /**
     * Check if a product is a seed product.
     * Uses categories_id to determine (seeds are in specific categories).
     *
     * @param int $products_id
     * @return bool
     */
    public static function isSeedProduct($products_id) {
        // First check if we have explicit data
        $q = xtc_db_query("SELECT is_seed FROM " . self::TABLE . " 
            WHERE products_id = " . (int)$products_id . " LIMIT 1");
        if (xtc_db_num_rows($q) > 0) {
            $row = xtc_db_fetch_array($q);
            return (bool)$row['is_seed'];
        }
        
        // Fallback: check if short description contains a seed table
        // This will be used during migration
        return null; // Unknown
    }
}
