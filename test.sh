#!/bin/bash


unitTestFailed() {
	echo "Error during unit test: $1";
	exit 1;
}


########
# Main #
########
echo "Testing user_upload.php script";

# Check arguments
#  multiple file
errorMsg=`php user_upload.php --file ersdsd --file qsdqsd 2>&1 >/dev/null`
if [ "$errorMsg" != "Error: multiple filename specified" ] || [ $? != 1 ] 
then
	unitTestFailed "Multiple file"
fi

#  multiple username
errorMsg=`php user_upload.php -u root -u olive 2>&1 >/dev/null`
if [ "$errorMsg" != "Error: multiple MySQL username specified" ] || [ $? != 1 ] 
then
	unitTestFailed "Multiple username"
fi

#  multiple password
errorMsg=`php user_upload.php -p pass -p toto 2>&1 >/dev/null`
if [ "$errorMsg" != "Error: multiple MySQL password specified" ] || [ $? != 1 ] 
then
	unitTestFailed "Multiple password"
fi

#  multiple hostname
errorMsg=`php user_upload.php -h 127.0.0.1 -h mysqlhost.com 2>&1 >/dev/null`
if [ "$errorMsg" != "Error: multiple MySQL hostname specified" ] || [ $? != 1 ] 
then
	unitTestFailed "Multiple hostname"
fi

exit 0;
