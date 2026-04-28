<?php
/**
 * MRH Product Attributes - Save Hook (Product Description)
 * Autoinclude: ~/admin/includes/extra/modules/new_product_description/
 * 
 * DEPRECATED: This file is NO LONGER USED for saving attributes.
 * 
 * The new_product_description autoinclude directory is only loaded during
 * form RENDERING (inside the language loop in new_product.php), NOT during
 * the actual product SAVE process. Therefore, this hook was never triggered
 * when the user clicked "Save".
 * 
 * The save logic has been moved to the categoriesModules plugin:
 *   admin/includes/modules/categories/mrh_product_attributes_cat.php
 * 
 * This plugin hooks into insert_product_end($products_id), which is called
 * by the categories class AFTER all language descriptions have been saved,
 * while $_POST data is still available.
 * 
 * This file is kept as a placeholder to prevent errors if referenced elsewhere.
 *
 * @package MRH_Product_Attributes
 * @version 1.8.0 (deprecated)
 * @see admin/includes/modules/categories/mrh_product_attributes_cat.php
 */

if (!defined('_VALID_XTC')) { return; }

// Save logic moved to categoriesModules plugin: mrh_product_attributes_cat.php
// This file intentionally left empty.
