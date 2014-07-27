<?php
Global $appPath;
$appPath = pathinfo(__FILE__,PATHINFO_DIRNAME).'/';
require_once ('classes/Controller.php');
require_once ('classes/Config.php');

// Get the data from the URL 
$controller = Controller_Class::getInstance();

// Testing Localhost
if ((($controller->address == '127.0.0.1') || stripos($controller->server_name, 'localhost') !== false) && ($controller->system == 'WINDOWS')) {
	define('APPLICATION_ENVIRONMENT', 'TESTLOCAL');
	error_reporting(E_ALL);
} elseif (stripos($controller->server_name, 'tb.princetonit.com') !== false) { // testbed server1
	define('APPLICATION_ENVIRONMENT', 'TESTING');
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
header("Content-Type: text/xml;charset=iso-8859-1");
if (file_exists ( $config->logDir.'sitemap.xml' )) :
	include_once $config->logDir.'sitemap.xml';
else : 
?>
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="http://<?php echo $controller->domain;?>/sitemap.xsl"?>
<!-- generator="sx25-ironhead" -->
<!-- generated-on="2012-08-19T22:13:29-07:00" -->
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
</urlset>
<?php endif; ?>