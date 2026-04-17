#!/bin/bash
###############################################################################
# MRH Product Attributes - Deploy Script
# Kopiert alle relevanten Dateien vom GitHub-Repo auf den Server
# und leert Cache + OPcache.
#
# Nutzung: bash deploy.sh
# Oder:    chmod +x deploy.sh && ./deploy.sh
###############################################################################

SHOP="/home/www/doc/28856/dcp288560004/mr-hanf.at/www"
GH_RAW="https://raw.githubusercontent.com/mrhanf15-stack/mrh-product-short_table/main"

echo "=== MRH Product Attributes Deploy ==="
echo ""

# 1. Hauptklasse
echo "[1/6] Hauptklasse..."
curl -sSL "$GH_RAW/includes/external/mrh_product_attributes/mrh_product_attributes.php" \
  -o "$SHOP/includes/external/mrh_product_attributes/mrh_product_attributes.php"

# 2. Migration
echo "[2/6] Migration..."
curl -sSL "$GH_RAW/includes/external/mrh_product_attributes/mrh_pa_migration.php" \
  -o "$SHOP/includes/external/mrh_product_attributes/mrh_pa_migration.php"

# 3. Helper-Funktionen (mit Legacy-Badge-Parser)
echo "[3/6] Helper-Funktionen..."
curl -sSL "$GH_RAW/includes/extra/functions/mrh_product_attributes_functions.php" \
  -o "$SHOP/includes/extra/functions/mrh_product_attributes_functions.php"

# 4. Listing-Autoinclude
echo "[4/6] Listing-Autoinclude..."
curl -sSL "$GH_RAW/includes/extra/modules/product_listing_content_ready/mrh_product_attributes_listing.php" \
  -o "$SHOP/includes/extra/modules/product_listing_content_ready/mrh_product_attributes_listing.php"

# 5. Detail-Autoinclude
echo "[5/6] Detail-Autoinclude..."
curl -sSL "$GH_RAW/includes/extra/modules/product_info_end/mrh_product_attributes_info.php" \
  -o "$SHOP/includes/extra/modules/product_info_end/mrh_product_attributes_info.php"

# 6. Admin-Tab (Produkt-Bearbeitung)
echo "[6/6] Admin-Tab..."
curl -sSL "$GH_RAW/admin/includes/extra/modules/new_product/mrh_product_attributes_tab.php" \
  -o "$SHOP/admin_q9wKj6Ds/includes/extra/modules/new_product/mrh_product_attributes_tab.php"

echo ""
echo "=== Cache leeren ==="

# Smarty Template-Cache leeren
rm -rf "$SHOP/templates_c/"*
echo "Smarty Cache geleert."

# OPcache leeren (PFLICHT!)
curl -s -u "Alex:19649541BNZUUHJHBBHZi" "https://mr-hanf.at/opcache_reset.php?token=MrHanf2024Reset" > /dev/null
echo "OPcache geleert."

echo ""
echo "=== Deploy abgeschlossen! ==="
echo "Bitte Seite neu laden und testen."
