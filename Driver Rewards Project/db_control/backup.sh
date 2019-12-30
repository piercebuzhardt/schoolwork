#!/bin/bash

curdir="/var/www/html/DriverRewards"

# Get configuration variables
echo "Retrieving DB Credentials from file...";
linecount=0;
instancecount=0;
names=('host' 'username' 'password' 'database');
values=();
for line in `cat $curdir/php/config.php | grep "="`; do
  if [[ $(($linecount % 3)) == 2 ]]; then
    values[$instancecount]=${line#*\"};
    values[$instancecount]=${values[$instancecount]%\"*};
    instancecount=$(($instancecount + 1));
  fi
  linecount=$(($linecount + 1));
done;
linecount=0;
for item in ${names[*]}; do
  echo "$item: ${values[$linecount]}";
  linecount=$(($linecount + 1));
done;

# Generate backup name
date=`date +%F`
name="$curdir/db_control/backups/$date.backup"

# Back up database
cd "$curdir/db_control"
echo "Backing up database to $name";
`mysqldump -h driverdb.cgbvpo0s4pjx.us-east-1.rds.amazonaws.com -u DrvrRwrds_1rtc -pTenRats10 DriverRewards4910 > $name`;
echo "Success.";

