<?php
/**
 * MRH Product Attributes - German Admin Language File
 * Autoinclude: ~/lang/german/extra/admin/
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Menu
define('MRH_PA_MENU_TITLE', 'MRH Produkteigenschaften');

// Page titles
define('MRH_PA_HEADING_TITLE', 'MRH Produkteigenschaften');
define('MRH_PA_HEADING_SUBTITLE', 'Strukturierte Produktdaten verwalten');

// Tabs
define('MRH_PA_TAB_ATTRIBUTES', 'Eigenschaften');
define('MRH_PA_TAB_CONFIG', 'Einstellungen');
define('MRH_PA_TAB_MIGRATION', 'Migration');
define('MRH_PA_TAB_STATS', 'Statistik');

// Preset tabs
define('MRH_PA_PRESET_FEMINIZED', 'Feminisiert');
define('MRH_PA_PRESET_AUTOFLOWER', 'Autoflowering');
define('MRH_PA_PRESET_REGULAR', 'Regulaer');
define('MRH_PA_PRESET_AUTO_REGULAR', 'Auto Regulaer');
define('MRH_PA_PRESET_CUSTOM', 'Benutzerdefiniert');

// Field labels
define('MRH_PA_FIELD_GENDER', 'Geschlecht');
define('MRH_PA_FIELD_FLOWERING_TYPE', 'Bluetentyp');
define('MRH_PA_FIELD_CROSS', 'Kreuzung / Genetik');
define('MRH_PA_FIELD_THC', 'THC-Gehalt');
define('MRH_PA_FIELD_CBD', 'CBD-Gehalt');
define('MRH_PA_FIELD_TYPE', 'Sorte (Indica/Sativa)');
define('MRH_PA_FIELD_YIELD_INDOOR', 'Ertrag Indoor');
define('MRH_PA_FIELD_YIELD_OUTDOOR', 'Ertrag Outdoor');
define('MRH_PA_FIELD_HEIGHT_INDOOR', 'Hoehe Indoor');
define('MRH_PA_FIELD_HEIGHT_OUTDOOR', 'Hoehe Outdoor');
define('MRH_PA_FIELD_FLOWERING_TIME', 'Bluetezeit');
define('MRH_PA_FIELD_HARVEST_TIME', 'Erntezeit');
define('MRH_PA_FIELD_CLIMATE', 'Klima');
define('MRH_PA_FIELD_EFFECT', 'Wirkung');
define('MRH_PA_FIELD_TASTE', 'Geschmack');
define('MRH_PA_FIELD_GROWING', 'Anbau');

// Gender options
define('MRH_PA_GENDER_FEMINIZED', 'Feminisiert');
define('MRH_PA_GENDER_REGULAR', 'Regulaer');
define('MRH_PA_GENDER_AUTOFLOWER', 'Autoflowering');

// Flowering type options
define('MRH_PA_FLOWERING_PHOTOPERIOD', 'Photoperiodisch');
define('MRH_PA_FLOWERING_AUTOFLOWER', 'Autoflowering');

// Type options
define('MRH_PA_TYPE_INDICA', 'Indica');
define('MRH_PA_TYPE_SATIVA', 'Sativa');
define('MRH_PA_TYPE_HYBRID', 'Hybrid');
define('MRH_PA_TYPE_INDICA_DOM', 'Indica-dominant');
define('MRH_PA_TYPE_SATIVA_DOM', 'Sativa-dominant');

// Growing options
define('MRH_PA_GROWING_INDOOR', 'Indoor');
define('MRH_PA_GROWING_OUTDOOR', 'Outdoor');
define('MRH_PA_GROWING_GREENHOUSE', 'Gewaechshaus');
define('MRH_PA_GROWING_ALL', 'Indoor/Outdoor');

// Buttons
define('MRH_PA_BUTTON_SAVE', 'Speichern');
define('MRH_PA_BUTTON_AI_FILL', 'Mit KI befuellen');
define('MRH_PA_BUTTON_AI_TRANSLATE', 'KI-Uebersetzung');
define('MRH_PA_BUTTON_ADD_FIELD', 'Feld hinzufuegen');
define('MRH_PA_BUTTON_REMOVE_FIELD', 'Feld entfernen');
define('MRH_PA_BUTTON_MIGRATE_ALL', 'Alle Produkte migrieren');
define('MRH_PA_BUTTON_MIGRATE_AI', 'Unvollstaendige per KI ergaenzen');
define('MRH_PA_BUTTON_CANCEL', 'Abbrechen');

// Messages
define('MRH_PA_MSG_SAVED', 'Produkteigenschaften erfolgreich gespeichert.');
define('MRH_PA_MSG_DELETED', 'Produkteigenschaften geloescht.');
define('MRH_PA_MSG_AI_SUCCESS', 'KI-Befuellung erfolgreich. Bitte Ergebnisse pruefen.');
define('MRH_PA_MSG_AI_ERROR', 'KI-Fehler: %s');
define('MRH_PA_MSG_AI_NO_KEY', 'Kein OpenAI API-Key konfiguriert. Bitte unter Einstellungen hinterlegen.');
define('MRH_PA_MSG_MIGRATION_STARTED', 'Migration gestartet...');
define('MRH_PA_MSG_MIGRATION_PROGRESS', '%d von %d Produkten verarbeitet.');
define('MRH_PA_MSG_MIGRATION_DONE', 'Migration abgeschlossen. %d Produkte migriert, %d uebersprungen.');
define('MRH_PA_MSG_NO_DATA', 'Keine strukturierten Daten vorhanden. Nutzen Sie die KI-Befuellung oder geben Sie die Daten manuell ein.');

// Config labels
define('MRH_PA_CONFIG_API_KEY', 'OpenAI API-Key');
define('MRH_PA_CONFIG_API_KEY_DESC', 'API-Key fuer die KI-Befuellung. Wird verschluesselt gespeichert.');
define('MRH_PA_CONFIG_MODEL', 'KI-Modell');
define('MRH_PA_CONFIG_MODEL_DESC', 'Empfohlen: gpt-4.1-nano (schnell, guenstig) oder gpt-4.1-mini (genauer).');
define('MRH_PA_CONFIG_BASE_URL', 'API Base-URL (optional)');
define('MRH_PA_CONFIG_BASE_URL_DESC', 'Nur aendern bei Nutzung eines alternativen API-Providers.');
define('MRH_PA_CONFIG_AUTO_TRANSLATE', 'Automatische Uebersetzung');
define('MRH_PA_CONFIG_AUTO_TRANSLATE_DESC', 'Wenn aktiviert, werden EN/FR/ES automatisch aus DE uebersetzt.');
define('MRH_PA_CONFIG_MIN_FIELDS', 'Mindestfelder fuer Anzeige');
define('MRH_PA_CONFIG_MIN_FIELDS_DESC', 'Mindestanzahl gefuellter Felder, damit die Mini-Tabelle angezeigt wird.');
define('MRH_PA_CONFIG_BATCH_SIZE', 'Migrations-Batchgroesse');
define('MRH_PA_CONFIG_BATCH_SIZE_DESC', 'Anzahl Produkte pro Migrations-Durchlauf.');

// Stats labels
define('MRH_PA_STATS_TOTAL', 'Aktive Produkte im Shop');
define('MRH_PA_STATS_WITH_ATTRS', 'Produkte mit Eigenschaften');
define('MRH_PA_STATS_WITH_3PLUS', 'Produkte mit 3+ Feldern');
define('MRH_PA_STATS_SOURCE_MANUAL', 'Manuell erfasst');
define('MRH_PA_STATS_SOURCE_MIGRATION', 'Per Migration');
define('MRH_PA_STATS_SOURCE_AI', 'Per KI');
define('MRH_PA_STATS_SOURCE_IMPORT', 'Per Import');

// Product edit (categories.php integration)
define('MRH_PA_PRODUCT_TAB', 'Eigenschaften (MRH)');
define('MRH_PA_PRODUCT_IS_SEED', 'Ist Saatgut-Produkt');
define('MRH_PA_PRODUCT_IS_SEED_YES', 'Ja (Samen)');
define('MRH_PA_PRODUCT_IS_SEED_NO', 'Nein (Non-Seed)');
