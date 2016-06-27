#!/bin/bash

# Figure out name of Sulu app, save into variable
NAME=$(grep -oP -m 1 '<name>\K(.+)(?=<\/name>)' app/Resources/webspaces/sulu.io.xml)

# Figure out new vendor folder name
VENDORPATH="/home/vagrant/vendors/sulu-$NAME/"

# Create new vendor folder
mkdir -p $VENDORPATH

# Change vendor in 'composer.json' unless 'vendor-dir' already exists
if [[ -z $(grep "vendor-dir" composer.json) ]]; then
	sed -i "s@\"config\": {@\"config\": {\n\t\"vendor-dir\": \"$VENDORPATH\",@g" composer.json
	sed -i "s@\"bin-dir\": \"bin\"@\"bin-dir\": \"vendor/bin\"@g" composer.json
else
	echo "[Safety block] config.vendor-dir value in composer.json already defined. Remove line and try running the script again. Other commands will still execute."
fi

# Change app/autoload.php's assumption of where the vendor folder is
LOADER="\$loader = require __DIR__.'/../vendor/autoload.php';"
NEWLOADER="/*\n\[app/autoload.php fix\] Commented out by bin/sulu/vendorfix.sh\n\$loader = require __DIR__ . '/../vendor/autoload.php';\n*/\n\$loader = require \"$VENDORPATH/autoload.php\";"

if [[ -z $(grep '\[app/autoload.php fix\]' app/autoload.php) ]]; then
	sed -i "s@$LOADER@$NEWLOADER@g" app/autoload.php
else
	echo "[Safety block] app/autoload.php file already modified for custom vendor location. Not changing it."
fi

# Add new vendor location to sulu.yml
YMLFILE='app/config/sulu.yml';
if [[ -z $(grep "\[app/config/sulu.yml fix\]" app/config/sulu.yml) ]]; then
	sed -i "s@%kernel.root_dir%/../vendor/@$VENDORPATH@g" $YMLFILE
	echo "#[app/config/sulu.yml fix] Vendor location changed by bin/sulu/vendorfix.sh - not doing any more changes." >> $YMLFILE
else
	echo "[Safety block] app/config/sulu.yml already modified for custom vendor location. Not changing it."
fi

# Remove current vendor folder
echo "Removing current vendor folder.\n"
rm -rf vendor

# Install dependencies in newly configured location
echo "Starting Composer installation.\n"
composer install
composer update
app/console cache:clear
