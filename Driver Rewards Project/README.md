# Introduction 
The WholeSaleCrocodile Driver Rewards system is a web-based application that allows drivers to partner with sponsor companies.
Sponsors motivate drivers to drive safely and reward them with points that drivers can use to redeem items from a sponsor-specific catalog.
Our company makes its share off of a 1% transaction fee on each redeemed purchase made by drivers, so your program's success is our success as well!

# Get Started
1.	Setup and Installation Process
	1) Set up your desired webserver and database, then copy and unzip the web application .zip file to your desired site directory 
	2) Update the information php/config.php to match the necessary credentials to connect to your database and eBay API
	3) Execute db_control/./database_reset.sh
		3a) If desired, backup data can be imported into the database by running db_control/./restore_from_backup.sh
			3a.1) By default, this script selects the most recent available backup
			3a.2) You may select a specific backup by entering its absolute or relative path as a command-line argument to the script
	4) Use `crontab -e` to add the following to your cron daemon script:
		0 0 * * * php /path_to_website/php/update.php
		0 0 * * * /var/www/html/DriverRewards/db_control/./backup.sh

2.	Software Dependencies
	* All web files are written to run using HTML, PHP and CSS
	* Sponsor catalogs are composed and orders are fulfilled using the eBay API
	* Visit https://go.developer.ebay.com to sign up for your own API keys, and add this information to the php/config.php file
	* The site database and dynamic page content are served using MySQL
	* Quality-of-life scripts are written in BASH to automate some processes
	* The UNIX cron daemon is used to schedule daily tasks

# Contribute
If you would like to contribute to our web application, reach out to WholeSaleCrocodile@gmail.com! Our team is motivated and ready to grow in full-time, part-time and consulting positions!

