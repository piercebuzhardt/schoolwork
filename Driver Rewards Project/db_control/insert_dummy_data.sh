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
done

# Insert Data
cd "$curdir/db_control"
echo "Resetting DB to blank slate";

`mysql -h ${values[0]} -u ${values[1]} -p${values[2]} ${values[3]} < "$curdir/db_control/generate_dummy_data.sql"`;
echo "Success.";

