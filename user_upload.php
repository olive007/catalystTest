<?php

# Exit Code
const SUCCESS_EXIT = 0;
const ARGUMENT_ERROR_EXIT = 1;
const FILE_ERROR_EXIT = 2;
const EMAIL_ERROR_EXIT = 3;

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
$mysql['username'] = "root";
$mysql['password'] = "";
$mysql['hostname'] = "127.0.0.1";

#######################
# Check the arguments #
#######################
# Check if we have to display the help
if (array_key_exists('help', $arguments)) {
	# Display the help

	# TODO

	# Stop the script without error
	exit(SUCCESS_EXIT);
}

# Check if we have to create SQL table
if (array_key_exists('create_table', $arguments)) {
	# Create the MySQL table because the option is specified

	# TODO

	# Stop the script without error
	exit(SUCCESS_EXIT);
}

# Check if we are ruuning the script in dry mode. That's mind we won't change the datebase.
if (array_key_exists('dry_run', $arguments)) {
	$alterTable = false;
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

# Open the file in read only mode
$fd = fopen($filename, "r");

# Check the opening the file
if (!$fd) {

	printError("Can't open $filename");
	exit(FILE_ERROR_EXIT);
}

$columnName = fgetcsv($fd);
if (gettype($columnName) != 'array' || count($columnName) <= 3) {

	printError("$filename doesn't have correct data");
	exit(FILE_ERROR_EXIT);
}

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

if (!isset($nameIndex) || !isset($surnameIndex) || !isset($emailIndex)) {
	printError("$filename doesn't have correct data");
	exit(FILE_ERROR_EXIT);
}

$row = 0;
$data = [];
$data['name'] = [];
$data['surname'] = [];
$data['email'] = [];

$formatData = [];

$formatData['name'] = function($name) {
	return ucfirst(trim($name));
};
$formatData['surname'] = function($surname) {
	return ucfirst(trim($surname));
};
$formatData['email'] = function($email) {
	$email = strtolower(trim($email));

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		printError("this addr: '$email' is not correct\n");
		exit(EMAIL_ERROR_EXIT);
	}

	return $email;
};


while (($buffer = fgetcsv($fd)) !== FALSE) {
	array_push($data['name'],    $formatData['name']($buffer[$nameIndex]));
	array_push($data['surname'], $formatData['surname']($buffer[$surnameIndex]));
	array_push($data['email'],   $formatData['email']($buffer[$emailIndex]));
}

fclose($fd);

var_dump($data);

# Stop the script without error
exit(SUCCESS_EXIT);


############
# Function #
############

# Display to the user an error msg
function printError($msg) {
	fwrite(STDERR, "Error: ".$msg."\n");
}
