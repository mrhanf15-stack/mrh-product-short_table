<?php
/**
 * MRH Product Attributes - English Admin Language File
 * Autoinclude: ~/lang/english/extra/admin/
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
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
define('MRH_PA_BUTTON_MIGRATE_AI', 'AI Fill Incomplete');
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

// Product edit
define('MRH_PA_PRODUCT_TAB', 'Attributes (MRH)');
define('MRH_PA_PRODUCT_IS_SEED', 'Is Seed Product');
define('MRH_PA_PRODUCT_IS_SEED_YES', 'Yes (Seeds)');
define('MRH_PA_PRODUCT_IS_SEED_NO', 'No (Non-Seed)');
