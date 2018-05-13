#!/usr/bin/env php 
<?php

# Exit Code
const SUCCESS_EXIT = 0;
const ARGUMENT_ERROR_EXIT = 1;
const FILE_ERROR_EXIT = 2;
const DATA_ERROR_EXIT = 3;
const SQL_ERROR_EXIT = 4;
const TABLE_NAME = "user";

# Remove php error and warning message
error_reporting(NULL);

#######################
# Argument definition #
#######################
# u MySQL user name
# p MySQL password
# h MySQL host name
$shortOptions = "u:p:h:";
# file : specify the filename
# create_table : only create the MySQL table
# dry_run : don't alter the database
# help : display the help
$longOptions = [
	"file:",
	"create_table",
	"dry_run",
	"help",
];

# Get the argument from the command line
$arguments = getopt($shortOptions, $longOptions);

########################
# Initialize variables #
########################
$filename = "users.csv"; # Default filename
$alterTable = true;
$mysql['username'] = "catalyst";
$mysql['password'] = "test";
$mysql['hostname'] = "192.168.56.2";
$mysql['databaseName'] = "catalyst";
$data = [];

#######################
# Check the arguments #
#######################
# Check if we have to display the help
if (array_key_exists('help', $arguments)) {
	# Display the help

	print("Usage: php user_upload.php [OPTIONS]
--------------------------------------------------------------------------------
PHP script uploading user data from csv file to MySQL database
--------------------------------------------------------------------------------
Options:        
  dry_run        : Don't change the data into the database
  create_table   : Don't put data into the database
                   Just create or update the table
                   (default table name is 'user')
  file filename  : Name of file which have to be parsed (default is users.csv)
  help           : Display this help
  u user         : MySQL user (default is catalyst)
  p password     : MySQL password (default is test)
  h hostname     : MySQL hostname (default is 192.168.56.2)
--------------------------------------------------------------------------------
Exit code:
  0 : Success
  2 : File error
  3 : Data error
  4 : SQL error
");
	# Stop the script without error
	exit(SUCCESS_EXIT);
}

# Check if we specified an other MySQL username
if (array_key_exists('u', $arguments)) {

	# Check if we are running the script with several 'u' (MySQL username) option
	if (gettype($arguments['u']) != 'string') {

		printError("multiple MySQL username specified");
		# Stop the script with error
		exit (ARGUMENT_ERROR_EXIT);
	}
	# Change the filename
	$mysql['username'] = $arguments['u'];
}

# Check if we specified an other MySQL hostname
if (array_key_exists('p', $arguments)) {

	# Check if we are running the script with several 'p' (MySQL password) option
	if (gettype($arguments['p']) != 'string') {

		printError("multiple MySQL password specified");
		# Stop the script with error
		exit (ARGUMENT_ERROR_EXIT);
	}
	# Change the filename
	$mysql['password'] = $arguments['p'];
}

# Check if we specified an other MySQL hostname
if (array_key_exists('h', $arguments)) {

	# Check if we are running the script with several 'h' (MySQL hostname) option
	if (gettype($arguments['h']) != 'string') {

		printError("multiple MySQL hostname specified");
		# Stop the script with error
		exit (ARGUMENT_ERROR_EXIT);
	}
	# Change the filename
	$mysql['hostname'] = $arguments['h'];
}

# Check if we are ruuning the script in dry mode. That's mind we won't change the datebase.
if (array_key_exists('dry_run', $arguments)) {
	$alterTable = false;
}

# Check if we have to create SQL table
if (array_key_exists('create_table', $arguments)) {
	# Create the MySQL table because the option is specified

	$sqlConnection = pdoConnection();
	$tableCreatedOrUpdated = "updated";

	# SQL query which delete the table
	$query = "DROP TABLE ".TABLE_NAME;
	try {
		$sqlConnection->exec($query);
	}
	catch (PDOException $e) {
		if ($e->getCode() !=  "42S02") {
			printError("SQL can't delete the '".TABLE_NAME."' table");
			exit(SQL_ERROR_EXIT);
		}
		$tableCreatedOrUpdated = "created";
	}

	# SQL query which create table
	$query = "CREATE TABLE ".TABLE_NAME." (
		name VARCHAR(30) NOT NULL,
		surname VARCHAR(30) NOT NULL,
		email VARCHAR(50) NOT NULL UNIQUE
	)";
	try {
		$sqlConnection->exec($query);
	}
	catch (PDOException $e) {
		printError("SQL can't create the '".TABLE_NAME."' table");
		exit(SQL_ERROR_EXIT);
	}

	printSuccess("table '".TABLE_NAME."' $tableCreatedOrUpdated");
	# Stop the script without error
	exit(SUCCESS_EXIT);
}

# Check if we specified an other filename.
if (array_key_exists('file', $arguments)) {

	# Check if we are running the script with several 'file' option
	if (gettype($arguments['file']) != 'string') {

		printError("multiple filename specified");
		# Stop the script with error
		exit (ARGUMENT_ERROR_EXIT);
	}
	# Change the filename
	$filename = $arguments['file'];
}


############################
# OPENING AND READING FILE #
############################

# Open the file in read only mode
$fd = fopen($filename, "r");

# Check the opening the file
if (!$fd) {
	printError("Can't open $filename");
	exit(FILE_ERROR_EXIT);
}

# Get the first row of data (csv header)
$columnName = fgetcsv($fd);
if (gettype($columnName) != 'array') {
	printError("$filename doesn't have correct data");
	exit(FILE_ERROR_EXIT);
}

# Get the right column index
for ($i = count($columnName); --$i >= 0;) {
	$columnName[$i] = strtolower(trim($columnName[$i]));

	if ($columnName[$i] == "name") {
		$nameIndex = $i;
	}
	if ($columnName[$i] == "surname") {
		$surnameIndex = $i;
	}
	if ($columnName[$i] == "email") {
		$emailIndex = $i;
	}
}

# Check if we have the 3 columns mandatory (name, surname, email)
if (!isset($nameIndex) || !isset($surnameIndex) || !isset($emailIndex)) {
	printError("$filename doesn't have correct data");
	exit(FILE_ERROR_EXIT);
}

# Create function table to format data
$formatData = [];

$formatData['name'] = function($name) {
	return addslashes(ucfirst(strtolower(trim($name))));
};

$formatData['surname'] = function($surname) {
	return addslashes(ucfirst(strtolower(trim($surname))));
};

$formatData['email'] = function($email) {
	$email = strtolower(trim($email));

	# Check if the email is correct
	if (($res = preg_match("/^[a-zA-Z0-9\.]+@[a-zA-Z\.]+\.[a-zA-Z]{2,}$/", $email))) {
		return $email;
	}
	printError("this addr: '$email' is not correct\n");
	exit(DATA_ERROR_EXIT);
};

# Parse CSV file and increament data table
while (($buffer = fgetcsv($fd)) !== FALSE) {
	array_push($data, [
		'name'		=> $formatData['name']($buffer[$nameIndex]),
		'surname'	=> $formatData['surname']($buffer[$surnameIndex]),
		'email'		=> $formatData['email']($buffer[$emailIndex]),
	]);
}

# close the file
fclose($fd);

#############################
# Insert data into database #
#############################
if (count($data) != 0) {
	$sqlConnection = pdoConnection();

		$query = "INSERT IGNORE INTO ".TABLE_NAME." (name, surname, email) VALUES ";
		for ($i = count($data); --$i >= 0; ) {
			$query .= "('".implode("', '", $data[$i])."'),";
		}
		$query = rtrim($query, ",");

	try {
		$res = $sqlConnection->exec($query);
	}
	catch(PDOException $e) {
		printError("Connection failed: ". $e->getMessage());
		exit(SQL_ERROR_EXIT);
	}
}
else {
	printWarning("no data into file '$filename'");
	$res = 0;
}

printSuccess($res." row added into the table '".TABLE_NAME."'");
# Stop the script without error
exit(SUCCESS_EXIT);


############
# Function #
############

# Display to the user an error msg
function printError($msg) {
	fwrite(STDERR, "Error: ".$msg."\n");
}

# Display to the user an warning msg
function printWarning($msg) {
	fwrite(STDERR, "Warning: ".$msg."\n");
}

# Display to user what the script done
function printSuccess($msg) {
	print("Success: $msg\n");
}

function pdoConnection() {
	global $mysql;
	global $alterTable;
	try {
		# Connection to database
		$connection = new PDO("mysql:host=$mysql[hostname];dbname=$mysql[databaseName]", $mysql['username'], $mysql['password']);
		# set the PDO error mode to exception
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		# Don't save change into the database if we are running the script with dry_run
		if (!$alterTable) {
			$connection->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
		}
	}
	catch(PDOException $e) {
		printError("SQL: can't connect to the database '$mysql[databaseName]' on host $mysql[hostname]");
		exit(SQL_ERROR_EXIT);
	}

	return $connection;
}
