@ECHO OFF
wp plugin deactivate --all && wp site empty --yes && wp plugin activate cache && wp db export tests/_data/dump.sql