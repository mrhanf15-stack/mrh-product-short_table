<?php
/**
 * MRH Product Attributes - Categories Module Plugin
 * 
 * This is a categoriesModules plugin that hooks into the product save process.
 * It saves structured product attributes (gender, THC, CBD, growing, etc.)
 * when a product is inserted or updated via categories.php.
 *
 * Hook used: insert_product_end($products_id)
 *   - Called AFTER all language descriptions have been saved
 *   - $_POST data is still available at this point
 *
 * @package MRH_Product_Attributes
 * @version 1.8.0
 */

class mrh_product_attributes_cat {

    function __construct()
    {
        $this->code = 'mrh_product_attributes_cat';
        $this->title = 'MRH Product Attributes Save';
        $this->description = 'Saves MRH product attributes (gender, THC, CBD, growing, pictos, etc.) when a product is saved.';
        $this->name = 'MODULE_CATEGORIES_' . strtoupper($this->code);
        $this->enabled = defined($this->name . '_STATUS') && constant($this->name . '_STATUS') == 'true' ? true : false;
        $this->sort_order = defined($this->name . '_SORT_ORDER') ? constant($this->name . '_SORT_ORDER') : '99';
    }

    function check()
    {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $this->name . "_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        define($this->name . '_STATUS_TITLE', 'MRH Product Attributes aktivieren');
        define($this->name . '_STATUS_DESC', 'Speichert MRH Produkteigenschaften beim Artikelspeichern.');
        define($this->name . '_SORT_ORDER_TITLE', 'Sortierreihenfolge');
        define($this->name . '_SORT_ORDER_DESC', 'Reihenfolge der Ausfuehrung');

        return array(
            $this->name . '_STATUS',
            $this->name . '_SORT_ORDER',
        );
    }

    function install()
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('" . $this->name . "_STATUS', 'true', 6, 1, 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('" . $this->name . "_SORT_ORDER', '99', 6, 2, now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '" . $this->name . "_%'");
    }

    /**
     * Hook: insert_product_end
     * Called after all language descriptions have been saved.
     * $_POST is still available here.
     */
    function insert_product_end($products_id)
    {
        if (!isset($_POST['mrh_pa']) || empty($_POST['mrh_pa']['products_id'])) {
            return;
        }

        // Load module class
        if (!class_exists('MrhProductAttributes')) {
            $mrh_pa_class = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_product_attributes.php';
            if (file_exists($mrh_pa_class)) {
                require_once($mrh_pa_class);
            }
        }

        if (!class_exists('MrhProductAttributes')) {
            return;
        }

        $mrh_pa_pid = (int)$_POST['mrh_pa']['products_id'];
        $mrh_pa_is_seed = isset($_POST['mrh_pa']['is_seed']) ? (int)$_POST['mrh_pa']['is_seed'] : 1;

        // Pictos (global, not per language) — JSON string from hidden input
        $mrh_pa_pictos = null;
        if (isset($_POST['mrh_pa']['pictos'])) {
            $mrh_pa_pictos_raw = $_POST['mrh_pa']['pictos'];
            $mrh_pa_pictos_decoded = json_decode($mrh_pa_pictos_raw, true);
            if (is_array($mrh_pa_pictos_decoded)) {
                $mrh_pa_pictos = [];
                foreach ($mrh_pa_pictos_decoded as $p) {
                    if (!empty($p['icon'])) {
                        $mrh_pa_pictos[] = [
                            'icon'  => preg_replace('/[^a-zA-Z0-9\-\.\/\:_ ]/', '', $p['icon'] ?? ''),
                            'color' => preg_replace('/[^a-zA-Z0-9#]/', '', $p['color'] ?? '#333333'),
                            'size'  => preg_replace('/[^a-zA-Z0-9.px]/', '', $p['size'] ?? '16px'),
                            'title' => mb_substr(strip_tags($p['title'] ?? ''), 0, 100),
                        ];
                    }
                }
            }
        }

        // Cannabis Cups (global, not per language)
        $mrh_pa_cups = isset($_POST['mrh_pa']['cannabis_cups']) ? max(0, min(99, (int)$_POST['mrh_pa']['cannabis_cups'])) : 0;

        // Process each language
        foreach ($_POST['mrh_pa'] as $lang_key => $lang_data) {
            // Skip non-language keys (products_id, is_seed, pictos, cannabis_cups)
            if (!is_numeric($lang_key)) continue;

            $lang_id = (int)$lang_key;

            // Build data array
            $data = [
                'is_seed' => $mrh_pa_is_seed,
            ];

            // Standard fields
            $standard_fields = [
                'gender', 'flowering_type', 'cross_genetics', 'thc', 'cbd', 'type',
                'yield_indoor', 'yield_outdoor', 'height_indoor', 'height_outdoor',
                'flowering_time', 'harvest_time', 'climate', 'effect', 'taste', 'growing'
            ];

            foreach ($standard_fields as $field) {
                if (isset($lang_data[$field])) {
                    // Field present in POST → save its value (may be empty string = cleared)
                    $val = trim($lang_data[$field]);
                    $data[$field] = ($val !== '') ? $val : null;
                } else {
                    // Field NOT in POST → was removed/hidden via × button → set to NULL
                    $data[$field] = null;
                }
            }

            // Custom fields
            if (isset($lang_data['custom']) && is_array($lang_data['custom'])) {
                $custom_fields = [];
                foreach ($lang_data['custom'] as $cf) {
                    if (!empty($cf['label']) || !empty($cf['value'])) {
                        $custom_fields[] = [
                            'label' => trim($cf['label'] ?? ''),
                            'value' => trim($cf['value'] ?? ''),
                        ];
                    }
                }
                if (!empty($custom_fields)) {
                    $data['custom_fields'] = $custom_fields;
                }
            }

            // Pictos (same for all languages)
            if ($mrh_pa_pictos !== null) {
                $data['pictos'] = $mrh_pa_pictos;
            }

            // Cannabis Cups (same for all languages)
            $data['cannabis_cups'] = $mrh_pa_cups;

            // Save
            MrhProductAttributes::saveAttributes($mrh_pa_pid, $lang_id, $data, 'manual');
        }
    }
}
