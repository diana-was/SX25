<?php
set_time_limit(60);

Global $appPath;
$appPath = pathinfo(__FILE__,PATHINFO_DIRNAME).'/';

// Magic class load : load the classes
function __autoload($class_name) {
	Global $appPath;
	$name_parts = explode("_", $class_name);
	foreach ($name_parts as $key => $name) {
		if (strtolower($name) == 'class') {
			unset($name_parts[$key]);
		}
	}
	$class_name = implode("_", $name_parts);
	$class_file_name = $appPath.'classes/'.$class_name.".php";
	require_once ($class_file_name);
}

// Get the data from the URL 
$controller = Controller_Class::getInstance();
//$controller->printMe();

// Testing Localhost
if ((($controller->address == '127.0.0.1') || stripos($controller->server_name, 'localhost') !== false) && ($controller->system == 'WINDOWS')) {
	define('APPLICATION_ENVIRONMENT', 'TESTLOCAL');
	define('TESTING_DOMAIN', 'bestcarhub.info');        //toponlinebankaccount.com
	$controller->domain = TESTING_DOMAIN; 
	error_reporting(E_ALL);
} elseif (stripos($controller->server_name, 'tb.princetonit.com') !== false) { // testbed server1
	define('APPLICATION_ENVIRONMENT', 'TESTING');
	define('TESTING_DOMAIN', 'bestcarhub.info');
	$controller->domain = TESTING_DOMAIN; 
	error_reporting(E_ALL);
} else {	// Production
	define('APPLICATION_ENVIRONMENT', 'PRODUCTION');	
	if (isset($controller->request['debug']))
		error_reporting(E_ALL);
	else
		error_reporting(0);
}


// load configuration file
$config = Config_Class::getInstance($appPath.'config/config.ini',APPLICATION_ENVIRONMENT);
//$config->printMe();
define('HELPER_PATH', $config->helperDir);

$db = db_class::getInstance();
if (!$db->connect($config->myServer, $config->myUser, $config->myPass, $config->myDB, true))  
{
	$db->print_last_error(false);
}
//$db->printMe();


$domainObj = Domain_Class::getInstance($db, $controller->domain);
if($domainObj->error) 
{
	echo 'fatal error!';
	exit;
}
$domain_id = $domainObj->domain_id;
$pRow = $domainObj->settings;
//$domainObj->PrintMe();
?>