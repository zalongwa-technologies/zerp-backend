#!/bin/bash

# Script to automate adding a new company to the ERP
# Usage: ./add_company.sh <database_name> "Company Display Name"

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <database_name> \"Company Display Name\""
    echo "Example: $0 zerp_company_abc \"ABC Corporation Ltd\""
    exit 1
fi

DB_NAME=$1
COMPANY_NAME=$2
COMPANIES_DIR="companies"
TEMPLATE_DIR="$COMPANIES_DIR/weberpdemo" # Using weberpdemo as the template

# 1. Check if companies directory exists
if [ ! -d "$COMPANIES_DIR" ]; then
    echo "Error: Must be run from the root of the zerp-backend project."
    exit 1
fi

# 2. Check if the new company directory already exists
NEW_COMPANY_DIR="$COMPANIES_DIR/$DB_NAME"
if [ -d "$NEW_COMPANY_DIR" ]; then
    echo "Error: Company directory $NEW_COMPANY_DIR already exists."
    exit 1
fi

echo "Creating new company: $COMPANY_NAME (DB: $DB_NAME)"

# 3. Create the new directory
mkdir "$NEW_COMPANY_DIR"

# 4. Create the Companies.php configuration file
cat <<EOF > "$NEW_COMPANY_DIR/Companies.php"
<?php
\$CompanyName['$DB_NAME'] = '$COMPANY_NAME';
EOF

# 5. Copy necessary asset directories if template exists
if [ -d "$TEMPLATE_DIR" ]; then
    echo "Copying asset directories from template..."
    
    # Standard WebERP asset directories
    for dir in part_pics reportwriter FormDesigns EDI_Incoming_Orders EDI_Sent; do
        if [ -d "$TEMPLATE_DIR/$dir" ]; then
            cp -r "$TEMPLATE_DIR/$dir" "$NEW_COMPANY_DIR/"
            echo " - Copied $dir"
        else
            mkdir "$NEW_COMPANY_DIR/$dir"
            echo " - Created empty $dir"
        fi
    done
else
    echo "Warning: Template directory $TEMPLATE_DIR not found. Creating empty asset directories."
    mkdir -p "$NEW_COMPANY_DIR/"{part_pics,reportwriter,FormDesigns,EDI_Incoming_Orders,EDI_Sent}
fi

# 6. Set basic permissions (adjust owner based on your web server, e.g., www-data)
chmod -R 775 "$NEW_COMPANY_DIR"

echo ""
echo "✅ Company directory setup complete!"
echo "Next Steps:"
echo "1. Create the MySQL database: CREATE DATABASE $DB_NAME;"
echo "2. Import your base SQL schema into the '$DB_NAME' database."
echo "3. Ensure your web server (e.g., www-data or apache) has write permissions to $NEW_COMPANY_DIR"
echo "   Command: sudo chown -R www-data:www-data $NEW_COMPANY_DIR"
