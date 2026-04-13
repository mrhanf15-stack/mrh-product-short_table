<?php
/**
 * MRH Product Attributes - Spanish Admin Language File
 * Autoinclude: ~/lang/spanish/extra/admin/
 *
 * @package MRH_Product_Attributes
 * @version 1.0.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Menu
define('MRH_PA_MENU_TITLE', 'MRH Propiedades del producto');

// Page titles
define('MRH_PA_HEADING_TITLE', 'MRH Propiedades del producto');
define('MRH_PA_HEADING_SUBTITLE', 'Gestionar datos de producto estructurados');

// Tabs
define('MRH_PA_TAB_ATTRIBUTES', 'Propiedades');
define('MRH_PA_TAB_CONFIG', 'Configuración');
define('MRH_PA_TAB_MIGRATION', 'Migración');
define('MRH_PA_TAB_STATS', 'Estadísticas');

// Preset tabs
define('MRH_PA_PRESET_FEMINIZED', 'Feminizada');
define('MRH_PA_PRESET_AUTOFLOWER', 'Autofloreciente');
define('MRH_PA_PRESET_REGULAR', 'Regular');
define('MRH_PA_PRESET_AUTO_REGULAR', 'Auto Regular');
define('MRH_PA_PRESET_CUSTOM', 'Personalizado');

// Field labels
define('MRH_PA_FIELD_GENDER', 'Género');
define('MRH_PA_FIELD_FLOWERING_TYPE', 'Tipo de floración');
define('MRH_PA_FIELD_CROSS', 'Cruce / Genética');
define('MRH_PA_FIELD_THC', 'Contenido de THC');
define('MRH_PA_FIELD_CBD', 'Contenido de CBD');
define('MRH_PA_FIELD_TYPE', 'Variedad (Indica/Sativa)');
define('MRH_PA_FIELD_YIELD_INDOOR', 'Rendimiento interior');
define('MRH_PA_FIELD_YIELD_OUTDOOR', 'Rendimiento exterior');
define('MRH_PA_FIELD_HEIGHT_INDOOR', 'Altura interior');
define('MRH_PA_FIELD_HEIGHT_OUTDOOR', 'Altura exterior');
define('MRH_PA_FIELD_FLOWERING_TIME', 'Período de floración');
define('MRH_PA_FIELD_HARVEST_TIME', 'Período de cosecha');
define('MRH_PA_FIELD_CLIMATE', 'Clima');
define('MRH_PA_FIELD_EFFECT', 'Efecto');
define('MRH_PA_FIELD_TASTE', 'Sabor');
define('MRH_PA_FIELD_GROWING', 'Cultivo');

// Gender options
define('MRH_PA_GENDER_FEMINIZED', 'Feminizada');
define('MRH_PA_GENDER_REGULAR', 'Regular');
define('MRH_PA_GENDER_AUTOFLOWER', 'Autofloreciente');

// Flowering type options
define('MRH_PA_FLOWERING_PHOTOPERIOD', 'Fotoperiódica');
define('MRH_PA_FLOWERING_AUTOFLOWER', 'Autofloreciente');

// Type options
define('MRH_PA_TYPE_INDICA', 'Indica');
define('MRH_PA_TYPE_SATIVA', 'Sativa');
define('MRH_PA_TYPE_HYBRID', 'Híbrido');
define('MRH_PA_TYPE_INDICA_DOM', 'Indica-dominante');
define('MRH_PA_TYPE_SATIVA_DOM', 'Sativa-dominante');

// Growing options
define('MRH_PA_GROWING_INDOOR', 'Interior');
define('MRH_PA_GROWING_OUTDOOR', 'Exterior');
define('MRH_PA_GROWING_GREENHOUSE', 'Invernadero');
define('MRH_PA_GROWING_ALL', 'Interior/Exterior');

// Buttons
define('MRH_PA_BUTTON_SAVE', 'Guardar');
define('MRH_PA_BUTTON_AI_FILL', 'Rellenar con IA');
define('MRH_PA_BUTTON_AI_TRANSLATE', 'Traducción IA');
define('MRH_PA_BUTTON_ADD_FIELD', 'Añadir campo');
define('MRH_PA_BUTTON_REMOVE_FIELD', 'Eliminar campo');
define('MRH_PA_BUTTON_MIGRATE_ALL', 'Migrar todos los productos');
define('MRH_PA_BUTTON_MIGRATE_AI', 'Completar incompletos con IA');
define('MRH_PA_BUTTON_CANCEL', 'Cancelar');

// Messages
define('MRH_PA_MSG_SAVED', 'Propiedades del producto guardadas con éxito.');
define('MRH_PA_MSG_DELETED', 'Propiedades del producto eliminadas.');
define('MRH_PA_MSG_AI_SUCCESS', 'Relleno con IA exitoso. Por favor, revise los resultados.');
define('MRH_PA_MSG_AI_ERROR', 'Error de IA: %s');
define('MRH_PA_MSG_AI_NO_KEY', 'No hay clave API de OpenAI configurada. Por favor, introdúzcala en la configuración.');
define('MRH_PA_MSG_MIGRATION_STARTED', 'Migración iniciada...');
define('MRH_PA_MSG_MIGRATION_PROGRESS', '%d de %d productos procesados.');
define('MRH_PA_MSG_MIGRATION_DONE', 'Migración completada. %d productos migrados, %d omitidos.');
define('MRH_PA_MSG_NO_DATA', 'No hay datos estructurados disponibles. Utilice el relleno con IA o introduzca los datos manualmente.');

// Config labels
define('MRH_PA_CONFIG_API_KEY', 'Clave API OpenAI');
define('MRH_PA_CONFIG_API_KEY_DESC', 'Clave API para el relleno con IA. Se almacena de forma cifrada.');
define('MRH_PA_CONFIG_MODEL', 'Modelo de IA');
define('MRH_PA_CONFIG_MODEL_DESC', 'Recomendado: gpt-4.1-nano (rápido, económico) o gpt-4.1-mini (más preciso).');
define('MRH_PA_CONFIG_BASE_URL', 'URL base de API (opcional)');
define('MRH_PA_CONFIG_BASE_URL_DESC', 'Solo modificar si utiliza un proveedor de API alternativo.');
define('MRH_PA_CONFIG_AUTO_TRANSLATE', 'Traducción automática');
define('MRH_PA_CONFIG_AUTO_TRANSLATE_DESC', 'Si está activado, EN/FR/ES se traducen automáticamente desde DE.');
define('MRH_PA_CONFIG_MIN_FIELDS', 'Campos mínimos para visualización');
define('MRH_PA_CONFIG_MIN_FIELDS_DESC', 'Número mínimo de campos rellenados para mostrar la mini-tabla.');
define('MRH_PA_CONFIG_BATCH_SIZE', 'Tamaño del lote de migración');
define('MRH_PA_CONFIG_BATCH_SIZE_DESC', 'Número de productos por ciclo de migración.');

// Stats labels
define('MRH_PA_STATS_TOTAL', 'Productos activos en la tienda');
define('MRH_PA_STATS_WITH_ATTRS', 'Productos con propiedades');
define('MRH_PA_STATS_WITH_3PLUS', 'Productos con 3+ campos');
define('MRH_PA_STATS_SOURCE_MANUAL', 'Introducidos manualmente');
define('MRH_PA_STATS_SOURCE_MIGRATION', 'Por migración');
define('MRH_PA_STATS_SOURCE_AI', 'Por IA');
define('MRH_PA_STATS_SOURCE_IMPORT', 'Por importación');

// Product edit (categories.php integration)
define('MRH_PA_PRODUCT_TAB', 'Propiedades (MRH)');
define('MRH_PA_PRODUCT_IS_SEED', 'Es producto de semillas');
define('MRH_PA_PRODUCT_IS_SEED_YES', 'Sí (Semillas)');
define('MRH_PA_PRODUCT_IS_SEED_NO', 'No (No-semilla)');
