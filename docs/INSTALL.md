# Installation: MRH Product Attributes

## Voraussetzungen

- Modified Shop 3.3.0 oder hoeher
- PHP 7.4+ (empfohlen: 8.1+)
- MySQL 5.7+ / MariaDB 10.3+
- cURL PHP-Extension (fuer KI-Funktionen)
- FTP/SSH-Zugang zum Server

## Schritt 1: Dateien hochladen

Kopieren Sie alle Dateien aus diesem Repository in Ihr Shop-Verzeichnis.
Die Verzeichnisstruktur entspricht exakt der Shop-Struktur:

```bash
# Per SSH (empfohlen):
cd /pfad/zum/shop/

# Oder per curl vom GitHub-Repo:
GH="https://raw.githubusercontent.com/mrhanf15-stack/mrh-product-attributes/main"

# Admin-Dateien
curl -sSL "$GH/admin/mrh_product_attributes.php" -o admin/mrh_product_attributes.php
curl -sSL "$GH/admin/includes/extra/filenames/mrh_product_attributes.php" --create-dirs -o admin/includes/extra/filenames/mrh_product_attributes.php
curl -sSL "$GH/admin/includes/extra/menu/mrh_product_attributes.php" --create-dirs -o admin/includes/extra/menu/mrh_product_attributes.php
curl -sSL "$GH/admin/includes/extra/modules/new_product/mrh_product_attributes_tab.php" --create-dirs -o admin/includes/extra/modules/new_product/mrh_product_attributes_tab.php
curl -sSL "$GH/admin/includes/extra/modules/new_product_description/mrh_product_attributes_save.php" --create-dirs -o admin/includes/extra/modules/new_product_description/mrh_product_attributes_save.php

# Modul-Klassen
curl -sSL "$GH/includes/external/mrh_product_attributes/mrh_product_attributes.php" --create-dirs -o includes/external/mrh_product_attributes/mrh_product_attributes.php
curl -sSL "$GH/includes/external/mrh_product_attributes/mrh_pa_ai_handler.php" -o includes/external/mrh_product_attributes/mrh_pa_ai_handler.php
curl -sSL "$GH/includes/external/mrh_product_attributes/mrh_pa_migration.php" -o includes/external/mrh_product_attributes/mrh_pa_migration.php

# Autoinclude-Dateien
curl -sSL "$GH/includes/extra/application_top/application_top_end/mrh_product_attributes_init.php" --create-dirs -o includes/extra/application_top/application_top_end/mrh_product_attributes_init.php
curl -sSL "$GH/includes/extra/database_tables/mrh_product_attributes.php" --create-dirs -o includes/extra/database_tables/mrh_product_attributes.php
curl -sSL "$GH/includes/extra/functions/mrh_product_attributes_functions.php" --create-dirs -o includes/extra/functions/mrh_product_attributes_functions.php
curl -sSL "$GH/includes/extra/modules/product_listing_end/mrh_product_attributes_listing.php" --create-dirs -o includes/extra/modules/product_listing_end/mrh_product_attributes_listing.php
curl -sSL "$GH/includes/extra/modules/product_info_end/mrh_product_attributes_info.php" --create-dirs -o includes/extra/modules/product_info_end/mrh_product_attributes_info.php

# Sprachdateien
curl -sSL "$GH/lang/german/extra/admin/mrh_product_attributes.php" --create-dirs -o lang/german/extra/admin/mrh_product_attributes.php
curl -sSL "$GH/lang/english/extra/admin/mrh_product_attributes.php" --create-dirs -o lang/english/extra/admin/mrh_product_attributes.php
```

## Schritt 2: Admin aufrufen

Rufen Sie eine beliebige Admin-Seite auf. Das Modul erkennt automatisch, dass die Datenbank-Tabellen fehlen, und legt sie an:

- `mrh_product_attributes` (Haupttabelle)
- `mrh_product_attributes_config` (Konfiguration)

## Schritt 3: Konfiguration

1. Navigieren Sie zu **Extras > MRH Produkteigenschaften**
2. Wechseln Sie zum Tab **Einstellungen**
3. Tragen Sie Ihren **OpenAI API-Key** ein (optional, nur fuer KI-Funktionen)
4. Waehlen Sie das gewuenschte **KI-Modell** (Standard: gpt-4.1-nano)
5. Speichern

## Schritt 4: Migration (optional)

1. Wechseln Sie zum Tab **Migration**
2. Klicken Sie auf **Alle Produkte migrieren**
3. Warten Sie, bis die Fortschrittsanzeige 100% erreicht
4. Optional: Klicken Sie auf **Unvollstaendige per KI ergaenzen**

## Schritt 5: Cache leeren

```bash
rm -rf templates_c/*
curl -s "https://mr-hanf.at/opcache_reset.php?token=MrHanf2024Reset"
```

## Deinstallation

1. Alle Modul-Dateien loeschen (siehe Verzeichnisstruktur in README.md)
2. Optional: Datenbank-Tabellen loeschen:
   ```sql
   DROP TABLE IF EXISTS mrh_product_attributes;
   DROP TABLE IF EXISTS mrh_product_attributes_config;
   ```
3. Cache leeren

## Fehlerbehebung

### Tabellen werden nicht angelegt
- Pruefen Sie die MySQL-Berechtigungen (CREATE TABLE muss erlaubt sein)
- Pruefen Sie die PHP-Fehlerlogs

### KI-Befuellung funktioniert nicht
- Pruefen Sie den API-Key unter Einstellungen
- Pruefen Sie ob cURL aktiviert ist: `php -m | grep curl`
- Pruefen Sie die Firewall (ausgehende HTTPS-Verbindungen muessen erlaubt sein)

### Admin-Tab wird nicht angezeigt
- Pruefen Sie ob die Datei `admin/includes/extra/modules/new_product/mrh_product_attributes_tab.php` existiert
- Leeren Sie den Template-Cache
