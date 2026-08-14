#!/bin/bash
# Script to clone a ZERP database while wiping out all transactions, items, and master data.
# It keeps users, chart of accounts, system configurations, roles, taxes, and locations.

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <Source_Database> <New_Database>"
    echo "Example: $0 zerp_10TZ120093 zerp_clean"
    exit 1
fi

SOURCE_DB=$1
DEST_DB=$2

# List of tables to EXCLUDE data from (they will be empty in the new DB)
EXCLUDE_DATA=(
    "audittrail" "banktrans" "bom" "cogsglpostings" "contractcharges" "contractreqts"
    "contracts" "custallocns" "custbranch" "custcontacts" "custnotes" "custitem"
    "debtorsmaster" "debtortrans" "debtortranstaxes" "discountmatrix" "edi_orders_seg_groups"
    "edi_orders_segs" "fixedassets" "fixedassettasks" "fixedassettrans" "gltrans"
    "grns" "invoices" "locstock" "loctransfers" "loctransfercancellations"
    "mrpdemands" "mrpplannedorders" "mrprequirements" "mrpsupplies" "offers"
    "orderdeliverydifferenceslog" "payments" "pcashdetails" "pcashdetailtaxes" "pcexpenses"
    "pcreceipts" "pctabexpenses" "pickinglistdetails" "pickinglists" "pickreq" "pickreqdetails"
    "pickserialdetails" "prices" "purchdata" "purchorderauth" "purchorderdetails" "purchorders"
    "qasamples" "qatests" "recurringsalesorders" "recurrsalesorderdetails" "salesanalysis"
    "salesglpostings" "salesorderdetails" "salesorders" "sampleresults" "shipmentcharges"
    "shipments" "stockcheckfreeze" "stockcounts" "stockitemnotes" "stockitemproperties"
    "stockmaster" "stockmoves" "stockmovestaxes" "stockrequest" "stockrequestitems"
    "stockserialitems" "stockserialmoves" "students" "suppallocs" "suppinvstogrn"
    "suppliercontacts" "supplierdiscounts" "suppliers" "supptrans" "supptranstaxes"
    "tenderitems" "tenders" "tendersuppliers" "timesheets" "woitems" "worequirements"
    "workorders" "woserialnos" "saris_sync_log" "zerp_sync_log" "efd_registration_data"
    "ReceiptAck" "gltotals" "salesanalysis" "lastcostrollup"
)

# Build ignore arguments
IGNORE_ARGS=""
for table in "${EXCLUDE_DATA[@]}"; do
    IGNORE_ARGS+="--ignore-table=$SOURCE_DB.$table "
done

echo "Starting clean clone from $SOURCE_DB to $DEST_DB..."

echo "1. Exporting exact database schema..."
sudo mysqldump $SOURCE_DB --no-data > /tmp/schema.sql

echo "2. Exporting setup and configuration data ONLY..."
sudo mysqldump $SOURCE_DB --no-create-info $IGNORE_ARGS > /tmp/data.sql

echo "3. Creating new database $DEST_DB..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DEST_DB;"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DEST_DB.* TO 'zerp_user'@'localhost';"

echo "4. Importing schema into new database..."
sudo mysql $DEST_DB < /tmp/schema.sql

echo "5. Importing configuration data into new database..."
# Temporarily disable foreign key checks during import
sudo mysql -e "SET FOREIGN_KEY_CHECKS=0; SOURCE /tmp/data.sql; SET FOREIGN_KEY_CHECKS=1;" $DEST_DB

echo "Cleaning up temporary files..."
rm /tmp/schema.sql /tmp/data.sql

echo ""
echo "✅ Success! The new database '$DEST_DB' has been created as a clean clone!"
echo "It has all your users, chart of accounts, and settings, but zero transactions."
