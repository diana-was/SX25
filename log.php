<?php
	date_default_timezone_set('UTC');

	$appPath = pathinfo(__FILE__,PATHINFO_DIRNAME).'/';
	require_once ($appPath.'classes/Controller.php');
	require_once ($appPath.'classes/Config.php');
	
	// Get the data from the URL 
	$controller = Controller_Class::getInstance();
	
	// load configuration file
	$config = Config_Class::getInstance($appPath.'config/config.ini','PRODUCTION');
	
	$log_file_name	=	$config->logDir."track".date('dmY').".csv";//print "log_file_name $log_file_name\n";
	
	$handle	=	fopen($log_file_name,"a");	
	
	if(filesize($log_file_name)==0){
		// write header for CSV file
		$log_content =	"Time,referrer,ip,our_page,ad_position,campaign, adgroup, keyword, session_id, referer_id, redirect_url, event_type, advertiser_url, user_agent\n";
		fwrite($handle,$log_content);
	}
	
	if($_SESSION['referrer']==null){
		$referer = $_SESSION['referrer'] = $_REQUEST['referrer'];
	}else{
		$referer = $_SESSION['referrer'];
	}
		
	if($_SESSION['ip']==null){
		$referer_ip = $_SESSION['ip'] = $_REQUEST['ip'];
	}else{
		$referer_ip = $_SESSION['ip'];
	}
	
	// cleanup 
	foreach ($_REQUEST as $key => $val) {
		$_REQUEST[$key] = str_replace(array(',','"'),array('.',"'"),$val);
	}
	
	$log_content[1]	=	date('H:i:s',time());	
	$log_content[2]	=	$referer;	
	$log_content[3]	=	$referer_ip;	
	$log_content[4]	=	$_REQUEST['our_page'];	
	$log_content[5]	=	$_REQUEST['ad_position']+1;		
	$log_content[6]	=	$_REQUEST['c'];	
	$log_content[7]	=	$_REQUEST['a'];	
	$log_content[8]	=	$_REQUEST['keyword'];	
	
	$log_content[9]	=	$_REQUEST['sid'];	
	$log_content[10]=	$_REQUEST['refid'];	
	$log_content[11]=	$_REQUEST['agent'];	
	$log_content[12]=	$_REQUEST['redurl'];		
	$log_content[13]=	$_REQUEST['event'];	
	$log_content[14]=	$_REQUEST['adurl'];
	
	
	fputcsv($handle, $log_content, ',','"');
	fclose($handle);
?>
