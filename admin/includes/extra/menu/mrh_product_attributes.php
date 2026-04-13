<?php
/**
 * MRH Product Attributes - Admin Menu Entry
 * Autoinclude: ~/admin/includes/extra/menu/
 * 
 * Adds a menu entry under "Hilfsprogramme/Extras" in the admin navigation.
 *
 * @package MRH_Product_Attributes
 * @version 1.0.1
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Ensure filename constant is defined
if (!defined('FILENAME_MRH_PRODUCT_ATTRIBUTES')) {
    define('FILENAME_MRH_PRODUCT_ATTRIBUTES', 'mrh_product_attributes.php');
}

// Load language file early for menu title
if (!defined('MRH_PA_MENU_TITLE')) {
    $_mrh_pa_lang_dir = DIR_FS_LANGUAGES . $_SESSION['language'] . '/extra/admin/mrh_product_attributes.php';
    if (file_exists($_mrh_pa_lang_dir)) {
        require_once($_mrh_pa_lang_dir);
    }
}

$add_contents[BOX_HEADING_TOOLS][] = array(
    'admin_access_name' => 'mrh_product_attributes',
    'filename'          => FILENAME_MRH_PRODUCT_ATTRIBUTES,
    'title'             => defined('MRH_PA_MENU_TITLE') ? MRH_PA_MENU_TITLE : 'MRH Produkteigenschaften',
    'link'              => xtc_href_link(FILENAME_MRH_PRODUCT_ATTRIBUTES),
    'access'            => 'mrh_product_attributes',
);
