<?php
/**
 * MRH Product Attributes - English Admin Language File
 * Autoinclude: ~/lang/english/extra/admin/
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Menu
define('MRH_PA_MENU_TITLE', 'MRH Product Attributes');

// Page titles
define('MRH_PA_HEADING_TITLE', 'MRH Product Attributes');
define('MRH_PA_HEADING_SUBTITLE', 'Manage structured product data');

// Tabs
define('MRH_PA_TAB_ATTRIBUTES', 'Attributes');
define('MRH_PA_TAB_CONFIG', 'Settings');
define('MRH_PA_TAB_MIGRATION', 'Migration');
define('MRH_PA_TAB_STATS', 'Statistics');

// Preset tabs
define('MRH_PA_PRESET_FEMINIZED', 'Feminized');
define('MRH_PA_PRESET_AUTOFLOWER', 'Autoflowering');
define('MRH_PA_PRESET_REGULAR', 'Regular');
define('MRH_PA_PRESET_AUTO_REGULAR', 'Auto Regular');
define('MRH_PA_PRESET_CUSTOM', 'Custom');

// Field labels
define('MRH_PA_FIELD_GENDER', 'Gender');
define('MRH_PA_FIELD_FLOWERING_TYPE', 'Flowering Type');
define('MRH_PA_FIELD_CROSS', 'Cross / Genetics');
define('MRH_PA_FIELD_THC', 'THC Content');
define('MRH_PA_FIELD_CBD', 'CBD Content');
define('MRH_PA_FIELD_TYPE', 'Type (Indica/Sativa)');
define('MRH_PA_FIELD_YIELD_INDOOR', 'Yield Indoor');
define('MRH_PA_FIELD_YIELD_OUTDOOR', 'Yield Outdoor');
define('MRH_PA_FIELD_HEIGHT_INDOOR', 'Height Indoor');
define('MRH_PA_FIELD_HEIGHT_OUTDOOR', 'Height Outdoor');
define('MRH_PA_FIELD_FLOWERING_TIME', 'Flowering Time');
define('MRH_PA_FIELD_HARVEST_TIME', 'Harvest Time');
define('MRH_PA_FIELD_CLIMATE', 'Climate');
define('MRH_PA_FIELD_EFFECT', 'Effect');
define('MRH_PA_FIELD_TASTE', 'Taste');
define('MRH_PA_FIELD_GROWING', 'Growing');

// Buttons
define('MRH_PA_BUTTON_SAVE', 'Save');
define('MRH_PA_BUTTON_AI_FILL', 'Fill with AI');
define('MRH_PA_BUTTON_AI_TRANSLATE', 'AI Translation');
define('MRH_PA_BUTTON_ADD_FIELD', 'Add Field');
define('MRH_PA_BUTTON_REMOVE_FIELD', 'Remove Field');
define('MRH_PA_BUTTON_MIGRATE_ALL', 'Migrate All Products');
define('MRH_PA_BUTTON_MIGRATE_AI', 'Start AI Batch');
define('MRH_PA_BUTTON_CANCEL', 'Cancel');

// Messages
define('MRH_PA_MSG_SAVED', 'Product attributes saved successfully.');
define('MRH_PA_MSG_DELETED', 'Product attributes deleted.');
define('MRH_PA_MSG_AI_SUCCESS', 'AI fill successful. Please review the results.');
define('MRH_PA_MSG_AI_ERROR', 'AI Error: %s');
define('MRH_PA_MSG_AI_NO_KEY', 'No OpenAI API key configured. Please set it in Settings.');
define('MRH_PA_MSG_MIGRATION_STARTED', 'Migration started...');
define('MRH_PA_MSG_MIGRATION_PROGRESS', '%d of %d products processed.');
define('MRH_PA_MSG_MIGRATION_DONE', 'Migration complete. %d products migrated, %d skipped.');
define('MRH_PA_MSG_NO_DATA', 'No structured data available. Use AI fill or enter data manually.');

// Config labels
define('MRH_PA_CONFIG_API_KEY', 'OpenAI API Key');
define('MRH_PA_CONFIG_MODEL', 'AI Model');
define('MRH_PA_CONFIG_BASE_URL', 'API Base URL (optional)');
define('MRH_PA_CONFIG_AUTO_TRANSLATE', 'Auto Translation');
define('MRH_PA_CONFIG_MIN_FIELDS', 'Minimum Fields for Display');
define('MRH_PA_CONFIG_BATCH_SIZE', 'Migration Batch Size');

// Gender options
define('MRH_PA_GENDER_FEMINIZED', 'Feminized');
define('MRH_PA_GENDER_REGULAR', 'Regular');
define('MRH_PA_GENDER_AUTOFLOWER', 'Autoflowering');

// Flowering type options
define('MRH_PA_FLOWERING_PHOTOPERIOD', 'Photoperiod');
define('MRH_PA_FLOWERING_AUTOFLOWER', 'Autoflowering');

// Type options
define('MRH_PA_TYPE_INDICA', 'Indica');
define('MRH_PA_TYPE_SATIVA', 'Sativa');
define('MRH_PA_TYPE_HYBRID', 'Hybrid');
define('MRH_PA_TYPE_INDICA_DOM', 'Indica Dominant');
define('MRH_PA_TYPE_SATIVA_DOM', 'Sativa Dominant');

// Growing options
define('MRH_PA_GROWING_INDOOR', 'Indoor');
define('MRH_PA_GROWING_OUTDOOR', 'Outdoor');
define('MRH_PA_GROWING_GREENHOUSE', 'Greenhouse');
define('MRH_PA_GROWING_ALL', 'Indoor/Outdoor');

// Config descriptions
define('MRH_PA_CONFIG_API_KEY_DESC', 'API key for AI fill. Stored encrypted.');
define('MRH_PA_CONFIG_MODEL_DESC', 'Recommended: gpt-4.1-nano (fast, cheap) or gpt-4.1-mini (more accurate).');
define('MRH_PA_CONFIG_BASE_URL_DESC', 'Only change when using an alternative API provider.');
define('MRH_PA_CONFIG_AUTO_TRANSLATE_DESC', 'When enabled, EN/FR/ES are automatically translated from DE.');
define('MRH_PA_CONFIG_MIN_FIELDS_DESC', 'Minimum number of filled fields for the mini table to be displayed.');
define('MRH_PA_CONFIG_BATCH_SIZE_DESC', 'Number of products per migration batch.');

// Stats labels
define('MRH_PA_STATS_TOTAL', 'Active Products in Shop');
define('MRH_PA_STATS_WITH_ATTRS', 'Products with Attributes');
define('MRH_PA_STATS_WITH_3PLUS', 'Products with 3+ Fields');
define('MRH_PA_STATS_SOURCE_MANUAL', 'Manually Entered');
define('MRH_PA_STATS_SOURCE_MIGRATION', 'Via Migration');
define('MRH_PA_STATS_SOURCE_AI', 'Via AI');
define('MRH_PA_STATS_SOURCE_IMPORT', 'Via Import');

// Product edit
define('MRH_PA_PRODUCT_TAB', 'Attributes (MRH)');
define('MRH_PA_PRODUCT_IS_SEED', 'Is Seed Product');
define('MRH_PA_PRODUCT_IS_SEED_YES', 'Yes (Seeds)');
define('MRH_PA_PRODUCT_IS_SEED_NO', 'No (Non-Seed)');

// v1.1.0: Icon Editor / Pictos
define('MRH_PA_PICTOS_HEADING', 'Picto Icons (Badges)');
define('MRH_PA_PICTOS_QUICKPICK', 'Quick Pick');
define('MRH_PA_PICTOS_CUSTOM_ADD', 'Add Custom Icon');
define('MRH_PA_PICTOS_ICON_CLASS', 'Icon Class');
define('MRH_PA_PICTOS_TITLE', 'Title');
define('MRH_PA_PICTOS_COLOR', 'Color');
define('MRH_PA_PICTOS_SIZE', 'Size');
define('MRH_PA_PICTOS_SIZE_NORMAL', 'Normal');
define('MRH_PA_PICTOS_SIZE_MEDIUM', 'Medium');
define('MRH_PA_PICTOS_SIZE_LARGE', 'Large');
define('MRH_PA_PICTOS_SIZE_XLARGE', 'Extra Large');
define('MRH_PA_PICTOS_EMPTY', 'No icons. Choose from below or add custom ones.');
define('MRH_PA_PICTOS_ADD_BTN', 'Add');
define('MRH_PA_PICTOS_REMOVE', 'Remove');

// v1.1.0: Cannabis Cup
define('MRH_PA_CUPS_HEADING', 'Cannabis Cup Awards');
define('MRH_PA_CUPS_COUNT', 'Number of Trophies');
define('MRH_PA_CUPS_AWARD', 'Cannabis Cup Award');
define('MRH_PA_CUPS_AWARDS', 'Cannabis Cup Awards');

// v1.1.0: AI Batch
define('MRH_PA_AI_BATCH_HEADING', 'AI Batch Fill');
define('MRH_PA_AI_BATCH_DESC', 'Fills products with less than the threshold of filled fields via AI analysis.');
define('MRH_PA_AI_BATCH_SIZE', 'Batch Size');
define('MRH_PA_AI_BATCH_MIN_FIELDS', 'Min. Fields');
define('MRH_PA_AI_BATCH_CHECK', 'Check');
define('MRH_PA_AI_BATCH_START', 'Start AI Batch');
define('MRH_PA_AI_BATCH_STOP', 'Stop');
define('MRH_PA_AI_BATCH_COUNTING', 'Counting products...');
define('MRH_PA_AI_BATCH_STARTED', 'AI batch started...');
define('MRH_PA_AI_BATCH_FINISHED', 'All products processed!');
define('MRH_PA_AI_BATCH_STOPPED', 'Stopped by user.');
