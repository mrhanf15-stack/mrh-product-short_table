<?php
/**
 * MRH Product Attributes - Frontend Initialization
 * Autoinclude: ~/includes/extra/application_top/application_top_end/
 * 
 * Loads the module class on every frontend page.
 * This file is auto-included by Modified Shop 3.3.0.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

if (!defined('TABLE_CONFIGURATION')) { return; }

// Load the main module class
if (!class_exists('MrhProductAttributes')) {
    $mrh_pa_class = DIR_FS_CATALOG . 'includes/external/mrh_product_attributes/mrh_product_attributes.php';
    if (file_exists($mrh_pa_class)) {
        require_once($mrh_pa_class);
    }
}
