# MRH Product Attributes

Autoinclude-Modul fuer **Modified Shop 3.3.0** zur strukturierten Verwaltung von Cannabis-Samen-Produkteigenschaften.

## Funktionen

- **Strukturierte Produktdaten:** Geschlecht, THC, CBD, Kreuzung, Sorte, Ertrag, Bluetezeit und weitere Felder in einer eigenen DB-Tabelle
- **Selbstinstallierend:** Tabellen werden automatisch beim ersten Admin-Aufruf angelegt (kein manuelles SQL noetig)
- **Admin-Interface:** Neuer Tab "Eigenschaften (MRH)" in der Produkt-Bearbeitungsmaske (categories.php)
- **3 Vorlagen-Tabs:** Feminisiert, Autoflowering, Regulaer — setzt Gender + Flowering Type automatisch
- **Erweiterbare Felder:** Benutzerdefinierte Felder per "+" Button hinzufuegen
- **Mehrsprachig:** DE (Pflicht), EN, FR, ES — mit automatischer KI-Uebersetzung
- **KI-Integration:** OpenAI-kompatible API zur automatischen Befuellung aus Beschreibungen
- **Migrations-Tools:** Bulk-Import aus bestehenden HTML-Tabellen (TR-Klassen-Parsing)
- **Frontend-Integration:** Smarty-Variablen fuer Badges, Mini-Tabellen und Fallback-Logik
- **Statistik-Dashboard:** Uebersicht ueber migrierte Produkte, Quellen und Fuellgrad

## Verzeichnisstruktur

```
mrh-product-attributes/
├── admin/
│   ├── mrh_product_attributes.php              # Standalone Admin-Seite (Config, Migration, Stats)
│   └── includes/extra/
│       ├── filenames/mrh_product_attributes.php # Filename-Konstante
│       ├── menu/mrh_product_attributes.php      # Admin-Menue-Eintrag (unter "Extras")
│       └── modules/
│           ├── new_product/mrh_product_attributes_tab.php        # Produkt-Edit Tab
│           └── new_product_description/mrh_product_attributes_save.php  # Save-Hook
├── includes/
│   ├── external/mrh_product_attributes/
│   │   ├── mrh_product_attributes.php           # Haupt-Klasse (DB, CRUD, Badges, Mini-Tabellen)
│   │   ├── mrh_pa_ai_handler.php                # KI-Integration (OpenAI API)
│   │   └── mrh_pa_migration.php                 # Migrations-Handler (TR-Klassen-Parsing)
│   └── extra/
│       ├── application_top/application_top_end/mrh_product_attributes_init.php  # Frontend-Init
│       ├── database_tables/mrh_product_attributes.php  # TABLE_* Konstanten
│       ├── functions/mrh_product_attributes_functions.php  # Helper-Funktionen
│       └── modules/
│           ├── product_listing_end/mrh_product_attributes_listing.php  # Listing-Integration
│           └── product_info_end/mrh_product_attributes_info.php        # Produktdetail-Integration
├── lang/
│   ├── german/extra/admin/mrh_product_attributes.php   # DE Sprachdatei
│   ├── english/extra/admin/mrh_product_attributes.php  # EN Sprachdatei
│   ├── french/extra/admin/                              # FR (TODO)
│   └── spanish/extra/admin/                             # ES (TODO)
└── docs/
    └── INSTALL.md                                       # Installationsanleitung
```

## Installation

### Automatisch (empfohlen)

1. Alle Dateien in das Shop-Verzeichnis kopieren (Verzeichnisstruktur beibehalten)
2. Admin aufrufen — Tabellen werden automatisch angelegt
3. Unter **Extras > MRH Produkteigenschaften** den API-Key konfigurieren

### Manuell

Siehe `docs/INSTALL.md` fuer detaillierte Anweisungen.

## Datenbank-Schema

### mrh_product_attributes

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| `id` | INT AUTO_INCREMENT | Primaerschluessel |
| `products_id` | INT | Produkt-ID |
| `language_id` | INT | Sprach-ID |
| `gender` | VARCHAR(32) | feminized, regular, autoflower |
| `flowering_type` | VARCHAR(32) | photoperiod, autoflower |
| `cross_genetics` | VARCHAR(512) | Kreuzung / Genetik |
| `thc` | VARCHAR(64) | THC-Gehalt |
| `cbd` | VARCHAR(64) | CBD-Gehalt |
| `type` | VARCHAR(64) | indica, sativa, hybrid, indica_dom, sativa_dom |
| `yield_indoor` | VARCHAR(128) | Ertrag Indoor |
| `yield_outdoor` | VARCHAR(128) | Ertrag Outdoor |
| `height_indoor` | VARCHAR(128) | Hoehe Indoor |
| `height_outdoor` | VARCHAR(128) | Hoehe Outdoor |
| `flowering_time` | VARCHAR(128) | Bluetezeit |
| `harvest_time` | VARCHAR(128) | Erntezeit |
| `climate` | VARCHAR(256) | Klima |
| `effect` | VARCHAR(512) | Wirkung |
| `taste` | VARCHAR(512) | Geschmack |
| `growing` | VARCHAR(64) | indoor, outdoor, greenhouse, all |
| `custom_fields` | TEXT (JSON) | Benutzerdefinierte Felder |
| `is_seed` | TINYINT(1) | 1=Samen, 0=Non-Seed |
| `data_source` | ENUM | manual, migration, ai, import |
| `ai_confidence` | DECIMAL(3,2) | KI-Vertrauenswert 0.00-1.00 |
| `fields_filled` | TINYINT(3) | Anzahl gefuellter Felder |
| `date_added` | DATETIME | Erstelldatum |
| `last_modified` | DATETIME | Letzte Aenderung |

**Unique Key:** `(products_id, language_id)` — ein Eintrag pro Produkt und Sprache.

### mrh_product_attributes_config

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| `config_key` | VARCHAR(64) PK | Konfigurationsschluessel |
| `config_value` | TEXT | Wert |
| `last_modified` | DATETIME | Letzte Aenderung |

## Migrations-Workflow

### Schritt 1: DB-Extraktion (kostenlos, schnell)

Parst die bestehenden HTML-Tabellen in `products_short_description` anhand der sprachunabhaengigen TR-Klassen:

| TR-Klasse | DB-Feld | Beschreibung |
|-----------|---------|-------------|
| `fem` | gender = feminized | Feminisiert |
| `reg` | gender = regular | Regulaer |
| `aut` | flowering_type = autoflower | Autoflowering |
| `kreuzung` | cross_genetics | Kreuzung |
| `thc` | thc | THC-Gehalt |
| `cbd_w` | cbd | CBD-Gehalt |
| `sort` | type | Sorte (Indica/Sativa) |
| `ertrag_in` | yield_indoor | Ertrag Indoor |
| `ertrag_out` | yield_outdoor | Ertrag Outdoor |
| `hoehe_in` | height_indoor | Hoehe Indoor |
| `hoehe_out` | height_outdoor | Hoehe Outdoor |
| `bluete` | flowering_time | Bluetezeit |
| `ernte` | harvest_time | Erntezeit |
| `klima` | climate | Klima |
| `wirkung` | effect | Wirkung |
| `geschmack` | taste | Geschmack |
| `anbau` | growing | Anbau |

### Schritt 2: KI-Nachbefuellung (optional, ~$1-3)

Ergaenzt Produkte mit weniger als 3 gefuellten Feldern per OpenAI API.

## Frontend-Integration (Smarty)

### Listings & Boxen

```smarty
{if $module_content[x].MRH_HAS_ATTRS}
    {$module_content[x].MRH_BADGES}
    {if $module_content[x].MRH_IS_SEED}
        {$module_content[x].MRH_MINI_TABLE}
    {/if}
{else}
    {* Fallback: Alte Kurzbeschreibung *}
    {$module_content[x].PRODUCTS_SHORT_DESCRIPTION}
{/if}
```

### Produktdetailseite

```smarty
{if $mrh_has_attrs}
    {$mrh_badges}
    {$mrh_mini_table}
{/if}
```

### PHP Helper-Funktionen

```php
// Attribute abrufen
$attrs = mrh_get_product_attributes($products_id);

// Badge HTML
$badges = mrh_get_product_badges($products_id);

// Mini-Tabelle
$table = mrh_get_product_mini_table($products_id, 0, 'listing');

// Pruefen ob Daten vorhanden
if (mrh_has_product_attributes($products_id)) { ... }
```

## Badge-Struktur

Das Modul generiert Badges in der Original-Konfigurator-Struktur:

```html
<span class="picto templatestyle">
  <span class="mrh-badge-bar">
    <span class="mrh-type-badge mrh-badge-fem" title="Feminisiert">
      <span class="fa fa-fw fa-venus"></span>
    </span>
    <span class="mrh-type-badge mrh-badge-photo" title="Photoperiodisch">
      <span class="fa fa-fw fa-sun"></span>
    </span>
  </span>
</span>
```

## Konfiguration

| Schluessel | Standard | Beschreibung |
|-----------|---------|-------------|
| `openai_api_key` | (leer) | OpenAI API-Key |
| `openai_model` | gpt-4.1-nano | KI-Modell |
| `openai_base_url` | (leer) | Alternative API-URL |
| `ai_auto_translate` | 1 | Automatische Uebersetzung EN/FR/ES |
| `min_fields_for_display` | 3 | Mindestfelder fuer Frontend-Anzeige |
| `migration_batch_size` | 100 | Produkte pro Migrations-Batch |

## Anforderungen

- Modified Shop 3.3.0+
- PHP 7.4+ (empfohlen: 8.1+)
- MySQL 5.7+ / MariaDB 10.3+
- cURL Extension (fuer KI-API)

## Lizenz

Proprietaer — Nur fuer mr-hanf.at / mr-hanf.de
