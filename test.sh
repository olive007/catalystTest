#!/bin/bash


unitTestFailed() {
	echo "Error during unit test: $1";
	exit 1;
}


########
# Main #
########
echo "Testing user_upload.php script";

# Checking arguments
errorMsg=`php user_upload.php --file ersdsd --file qsdqsd 2>&1 >/dev/null`
if [ $? != 1 ] || [ "$errorMsg" != "Error: multiple filename specified" ]
then
	unitTestFailed "Multiple file"
fi

errorMsg=`php user_upload.php -u root -u olive 2>&1 >/dev/null`
if [ $? != 1 ] || [ "$errorMsg" != "Error: multiple MySQL username specified" ]
then
	unitTestFailed "Multiple username"
fi

errorMsg=`php user_upload.php -p pass -p toto 2>&1 >/dev/null`
if [ $? != 1 ] || [ "$errorMsg" != "Error: multiple MySQL password specified" ]
then
	unitTestFailed "Multiple password"
fi

errorMsg=`php user_upload.php -h 127.0.0.1 -h mysqlhost.com 2>&1 >/dev/null`
if [ $? != 1 ] || [ "$errorMsg" != "Error: multiple MySQL hostname specified" ]
then
	unitTestFailed "Multiple hostname"
fi

# Check the file
errorMsg=`php user_upload.php --file csvForTest/none.csv 2>&1 >/dev/null`
if [ $? != 2 ] || [ "$errorMsg" != "Error: Can't open csvForTest/none.csv" ]
then
	unitTestFailed "file doesn't exist"
fi

errorMsg=`php user_upload.php --file csvForTest/empty.csv 2>&1 >/dev/null`
if [ $? != 2 ] || [ "$errorMsg" != "Error: csvForTest/empty.csv doesn't have correct data" ]
then
	unitTestFailed "file empty"
fi

errorMsg=`php user_upload.php --file csvForTest/wrongHeader1.csv 2>&1 >/dev/null`
if [ $? != 2 ] || [ "$errorMsg" != "Error: csvForTest/wrongHeader1.csv doesn't have correct data" ]
then
	unitTestFailed "file with wrong data"
fi

errorMsg=`php user_upload.php --file csvForTest/wrongHeader2.csv 2>&1 >/dev/null`
if [ $? != 2 ] || [ "$errorMsg" != "Error: csvForTest/wrongHeader2.csv doesn't have correct data" ]
then
	unitTestFailed "file with wrong data"
fi


exit 0;
