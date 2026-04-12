<?php
/**
 * MRH Product Attributes - Admin Menu Entry
 * Autoinclude: ~/admin/includes/extra/menu/
 * 
 * Adds a menu entry under "Extras" in the admin navigation.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$add_contents[BOX_HEADING_TOOLS][] = array(
    'admin_access_name' => 'mrh_product_attributes',
    'filename'          => FILENAME_MRH_PRODUCT_ATTRIBUTES,
    'title'             => defined('MRH_PA_MENU_TITLE') ? MRH_PA_MENU_TITLE : 'MRH Produkteigenschaften',
    'link'              => xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES),
    'access'            => 'mrh_product_attributes',
);
