<?php

# Exit Code
const SUCCESS_EXIT = 0;
const ARGUMENT_ERROR_EXIT = 1;

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
$filename = "user.csv"; # Default filename
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

# Stop the script without error
exit(SUCCESS_EXIT);


############
# Function #
############

# Display to the user an error msg
function printError($msg) {
	fwrite(STDERR, "Error: ".$msg."\n");
}
