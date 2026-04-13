<?php
/**
 * MRH Product Attributes - French Admin Language File
 * Autoinclude: ~/lang/french/extra/admin/
 *
 * @package MRH_Product_Attributes
 * @version 1.1.0
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Menu
define('MRH_PA_MENU_TITLE', 'MRH Propriétés produit');

// Page titles
define('MRH_PA_HEADING_TITLE', 'MRH Propriétés produit');
define('MRH_PA_HEADING_SUBTITLE', 'Gérer les données produit structurées');

// Tabs
define('MRH_PA_TAB_ATTRIBUTES', 'Propriétés');
define('MRH_PA_TAB_CONFIG', 'Paramètres');
define('MRH_PA_TAB_MIGRATION', 'Migration');
define('MRH_PA_TAB_STATS', 'Statistiques');

// Preset tabs
define('MRH_PA_PRESET_FEMINIZED', 'Féminisée');
define('MRH_PA_PRESET_AUTOFLOWER', 'Autofloraison');
define('MRH_PA_PRESET_REGULAR', 'Régulière');
define('MRH_PA_PRESET_AUTO_REGULAR', 'Auto Régulière');
define('MRH_PA_PRESET_CUSTOM', 'Personnalisé');

// Field labels
define('MRH_PA_FIELD_GENDER', 'Genre');
define('MRH_PA_FIELD_FLOWERING_TYPE', 'Type de floraison');
define('MRH_PA_FIELD_CROSS', 'Croisement / Génétique');
define('MRH_PA_FIELD_THC', 'Teneur en THC');
define('MRH_PA_FIELD_CBD', 'Teneur en CBD');
define('MRH_PA_FIELD_TYPE', 'Variété (Indica/Sativa)');
define('MRH_PA_FIELD_YIELD_INDOOR', 'Rendement intérieur');
define('MRH_PA_FIELD_YIELD_OUTDOOR', 'Rendement extérieur');
define('MRH_PA_FIELD_HEIGHT_INDOOR', 'Hauteur intérieur');
define('MRH_PA_FIELD_HEIGHT_OUTDOOR', 'Hauteur extérieur');
define('MRH_PA_FIELD_FLOWERING_TIME', 'Période de floraison');
define('MRH_PA_FIELD_HARVEST_TIME', 'Période de récolte');
define('MRH_PA_FIELD_CLIMATE', 'Climat');
define('MRH_PA_FIELD_EFFECT', 'Effet');
define('MRH_PA_FIELD_TASTE', 'Goût');
define('MRH_PA_FIELD_GROWING', 'Culture');

// Gender options
define('MRH_PA_GENDER_FEMINIZED', 'Féminisée');
define('MRH_PA_GENDER_REGULAR', 'Régulière');
define('MRH_PA_GENDER_AUTOFLOWER', 'Autofloraison');

// Flowering type options
define('MRH_PA_FLOWERING_PHOTOPERIOD', 'Photopériodique');
define('MRH_PA_FLOWERING_AUTOFLOWER', 'Autofloraison');

// Type options
define('MRH_PA_TYPE_INDICA', 'Indica');
define('MRH_PA_TYPE_SATIVA', 'Sativa');
define('MRH_PA_TYPE_HYBRID', 'Hybride');
define('MRH_PA_TYPE_INDICA_DOM', 'Indica-dominante');
define('MRH_PA_TYPE_SATIVA_DOM', 'Sativa-dominante');

// Growing options
define('MRH_PA_GROWING_INDOOR', 'Intérieur');
define('MRH_PA_GROWING_OUTDOOR', 'Extérieur');
define('MRH_PA_GROWING_GREENHOUSE', 'Serre');
define('MRH_PA_GROWING_ALL', 'Intérieur/Extérieur');

// Buttons
define('MRH_PA_BUTTON_SAVE', 'Enregistrer');
define('MRH_PA_BUTTON_AI_FILL', 'Remplir par IA');
define('MRH_PA_BUTTON_AI_TRANSLATE', 'Traduction IA');
define('MRH_PA_BUTTON_ADD_FIELD', 'Ajouter un champ');
define('MRH_PA_BUTTON_REMOVE_FIELD', 'Supprimer le champ');
define('MRH_PA_BUTTON_MIGRATE_ALL', 'Migrer tous les produits');
define('MRH_PA_BUTTON_MIGRATE_AI', 'Démarrer le lot IA');
define('MRH_PA_BUTTON_CANCEL', 'Annuler');

// Messages
define('MRH_PA_MSG_SAVED', 'Propriétés du produit enregistrées avec succès.');
define('MRH_PA_MSG_DELETED', 'Propriétés du produit supprimées.');
define('MRH_PA_MSG_AI_SUCCESS', 'Remplissage IA réussi. Veuillez vérifier les résultats.');
define('MRH_PA_MSG_AI_ERROR', 'Erreur IA : %s');
define('MRH_PA_MSG_AI_NO_KEY', 'Aucune clé API OpenAI configurée. Veuillez la saisir dans les paramètres.');
define('MRH_PA_MSG_MIGRATION_STARTED', 'Migration démarrée...');
define('MRH_PA_MSG_MIGRATION_PROGRESS', '%d sur %d produits traités.');
define('MRH_PA_MSG_MIGRATION_DONE', 'Migration terminée. %d produits migrés, %d ignorés.');
define('MRH_PA_MSG_NO_DATA', 'Aucune donnée structurée disponible. Utilisez le remplissage IA ou saisissez les données manuellement.');

// Config labels
define('MRH_PA_CONFIG_API_KEY', 'Clé API OpenAI');
define('MRH_PA_CONFIG_API_KEY_DESC', 'Clé API pour le remplissage IA. Stockée de manière chiffrée.');
define('MRH_PA_CONFIG_MODEL', 'Modèle IA');
define('MRH_PA_CONFIG_MODEL_DESC', 'Recommandé : gpt-4.1-nano (rapide, économique) ou gpt-4.1-mini (plus précis).');
define('MRH_PA_CONFIG_BASE_URL', 'URL de base API (optionnel)');
define('MRH_PA_CONFIG_BASE_URL_DESC', 'Ne modifier que si vous utilisez un fournisseur API alternatif.');
define('MRH_PA_CONFIG_AUTO_TRANSLATE', 'Traduction automatique');
define('MRH_PA_CONFIG_AUTO_TRANSLATE_DESC', 'Si activé, EN/FR/ES sont automatiquement traduits depuis DE.');
define('MRH_PA_CONFIG_MIN_FIELDS', 'Champs minimum pour affichage');
define('MRH_PA_CONFIG_MIN_FIELDS_DESC', 'Nombre minimum de champs remplis pour afficher le mini-tableau.');
define('MRH_PA_CONFIG_BATCH_SIZE', 'Taille du lot de migration');
define('MRH_PA_CONFIG_BATCH_SIZE_DESC', 'Nombre de produits par cycle de migration.');

// Stats labels
define('MRH_PA_STATS_TOTAL', 'Produits actifs dans la boutique');
define('MRH_PA_STATS_WITH_ATTRS', 'Produits avec propriétés');
define('MRH_PA_STATS_WITH_3PLUS', 'Produits avec 3+ champs');
define('MRH_PA_STATS_SOURCE_MANUAL', 'Saisis manuellement');
define('MRH_PA_STATS_SOURCE_MIGRATION', 'Par migration');
define('MRH_PA_STATS_SOURCE_AI', 'Par IA');
define('MRH_PA_STATS_SOURCE_IMPORT', 'Par import');

// Product edit (categories.php integration)
define('MRH_PA_PRODUCT_TAB', 'Propriétés (MRH)');
define('MRH_PA_PRODUCT_IS_SEED', 'Est un produit semence');
define('MRH_PA_PRODUCT_IS_SEED_YES', 'Oui (Graines)');
define('MRH_PA_PRODUCT_IS_SEED_NO', 'Non (Non-semence)');

// v1.1.0: Icon Editor / Pictos
define('MRH_PA_PICTOS_HEADING', 'Icônes Picto (Badges)');
define('MRH_PA_PICTOS_QUICKPICK', 'Sélection rapide');
define('MRH_PA_PICTOS_CUSTOM_ADD', 'Ajouter une icône personnalisée');
define('MRH_PA_PICTOS_ICON_CLASS', 'Classe icône');
define('MRH_PA_PICTOS_TITLE', 'Titre');
define('MRH_PA_PICTOS_COLOR', 'Couleur');
define('MRH_PA_PICTOS_SIZE', 'Taille');
define('MRH_PA_PICTOS_SIZE_NORMAL', 'Normal');
define('MRH_PA_PICTOS_SIZE_MEDIUM', 'Moyen');
define('MRH_PA_PICTOS_SIZE_LARGE', 'Grand');
define('MRH_PA_PICTOS_SIZE_XLARGE', 'Très grand');
define('MRH_PA_PICTOS_EMPTY', 'Aucune icône. Choisissez ci-dessous ou ajoutez des personnalisées.');
define('MRH_PA_PICTOS_ADD_BTN', 'Ajouter');
define('MRH_PA_PICTOS_REMOVE', 'Supprimer');

// v1.1.0: Cannabis Cup
define('MRH_PA_CUPS_HEADING', 'Récompenses Cannabis Cup');
define('MRH_PA_CUPS_COUNT', 'Nombre de trophées');
define('MRH_PA_CUPS_AWARD', 'Prix Cannabis Cup');
define('MRH_PA_CUPS_AWARDS', 'Prix Cannabis Cup');

// v1.1.0: AI Batch
define('MRH_PA_AI_BATCH_HEADING', 'Remplissage IA par lot');
define('MRH_PA_AI_BATCH_DESC', 'Remplit les produits avec moins de champs remplis que le seuil via analyse IA.');
define('MRH_PA_AI_BATCH_SIZE', 'Taille du lot');
define('MRH_PA_AI_BATCH_MIN_FIELDS', 'Champs min.');
define('MRH_PA_AI_BATCH_CHECK', 'Vérifier');
define('MRH_PA_AI_BATCH_START', 'Démarrer le lot IA');
define('MRH_PA_AI_BATCH_STOP', 'Arrêter');
define('MRH_PA_AI_BATCH_COUNTING', 'Comptage des produits...');
define('MRH_PA_AI_BATCH_STARTED', 'Lot IA démarré...');
define('MRH_PA_AI_BATCH_FINISHED', 'Tous les produits traités !');
define('MRH_PA_AI_BATCH_STOPPED', 'Arrêté par l\'utilisateur.');
