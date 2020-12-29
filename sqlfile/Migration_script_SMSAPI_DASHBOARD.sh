#!/bin/bash
## CONFIG EDIT FROM HERE ##
## NAME OF DATABASE IN OPERATION STAGING
DATABASE=SMS_API_V2
## -----

## NAME OF NEW DATABASE SMS API DASHBOARD
NEW_DB=SMSAPI_DASHBOARD
## -----

## EDIT MYSQL USER HERE
USER=root
## -----

FILE_TABLES=LIST_TABLES.txt
FILE_STRUCTURE=TABLES_DATA.sql

## EDIT CONFIG END HERE ##
date
echo ''
echo '1. CREATE NEW SCHEMA SMS API DASHBOARD'
echo ''
mysql -u$USER -p -e "DROP SCHEMA IF EXISTS $NEW_DB ; CREATE SCHEMA $NEW_DB;"
echo ''
echo '2. GET LIST OF TABLES'
echo ''
mysql -u$USER -p -N information_schema -e "select table_name from information_schema.tables where table_schema = '$DATABASE' and table_name like 'AD%\_%' order by 1 ;" > $FILE_TABLES
echo ''
echo '3. GET TABLES STRUCTURE DATA FROM SMS API V2'
echo ''
mysqldump -u$USER -p $DATABASE `cat $FILE_TABLES` > $FILE_STRUCTURE
echo ''
echo '4. ADD LIST TABLES INTO NEW DATABASE'
echo ''
mysql -u$USER -p $NEW_DB < $FILE_STRUCTURE
echo ''
echo '5. ADD NEW COLUMN IN AD_REPORT TABLE'
echo ''
mysql -u$USER -p -N $NEW_DB -e "ALTER TABLE AD_REPORT ADD COLUMN pid INT(11) NOT NULL DEFAULT '0' AFTER generate_status;"
echo ''
echo 'SCRIPT END'

