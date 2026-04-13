<?php
/**
 * --------------------------------------------------------------
 * Modul:       MRH Product Attributes - Admin-Menue
 * Datei:       admin/includes/extra/menu/mrh_product_attributes.php
 * Version:     1.0.3
 *
 * Autoinclude: Fuegt den Menuepunkt "MRH Produkteigenschaften" im Admin hinzu
 *              unter der Kategorie "Hilfsprogramme" (BOX_HEADING_TOOLS)
 * Kompatibilitaet: modified eCommerce v2.0.7.2 rev 14622
 * --------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Menuename je nach Sprache
$_mrh_pa_menu_name = 'MRH Produkteigenschaften';
if (isset($_SESSION['language_code'])) {
    switch ($_SESSION['language_code']) {
        case 'en': $_mrh_pa_menu_name = 'MRH Product Attributes'; break;
        case 'fr': $_mrh_pa_menu_name = 'MRH Attributs Produits'; break;
        case 'es': $_mrh_pa_menu_name = 'MRH Atributos Productos'; break;
        default:   $_mrh_pa_menu_name = 'MRH Produkteigenschaften'; break;
    }
}

$add_contents[BOX_HEADING_TOOLS][$_mrh_pa_menu_name][] = array(
    'admin_access_name' => 'mrh_product_attributes',
    'filename'          => 'mrh_product_attributes.php',
    'boxname'           => $_mrh_pa_menu_name,
    'parameters'        => '',
    'ssl'               => '',
);
