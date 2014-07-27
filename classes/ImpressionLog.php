<?php

class ImpressionLog {
	private $referer;
	private $referer_ip;
	private $requested_page;
	private $now;
	private $today;
	private $keyword;
	private $sid;
	private $logDir;
	
	function __construct($dir){//print "1";		
		$this->logDir = $dir;
		$this->requested_page = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->now = date("H : i : s",time()-7200);// minus 2 hour to equal to Aus time
		$this->today = date("d - m - Y");
		$this->agent = $_SERVER["HTTP_USER_AGENT"];
		if($_SESSION['referrer']==null){
			$this->referer = $_SESSION['referrer'] = $_SERVER['HTTP_REFERER'];
		}else{
			$this->referer = $_SESSION['referrer'];
		}
		
		if($_SESSION['ip']==null){
			$this->referer_ip = $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		}else{
			$this->referer_ip = $_SESSION['ip'];
		}
	}
	
	function __set($name,$value){
		$this->$name = $value;
	}
	
	function writeToFileOfToday(){
		$log_file_name	=	"track".date('dmY').".csv";//print "log_file_name $log_file_name\n";
		if (!is_dir($this->logDir)) {
			mkdir($this->logDir,0777);
		}
		$handle	=	fopen($this->logDir.$log_file_name,"a");
		if(filesize($this->logDir.$log_file_name)==0){
			$log_content1 =	"Time,referrer,ip,our_page,ad_position,campaign, adgroup, keyword, session_id, referer_id, redirect_url, event_type, advertiser_url, user_agent\n";
			fwrite($handle,$log_content1);
		}
		
		$log_content[1]	=	$this->now;	
		$log_content[2]	=	$this->referer;	
		$log_content[3]	=	$this->referer_ip;	
		$log_content[4]	=	$this->requested_page;	
		$log_content[5]	=	'';		
		$log_content[6]	=	'';	
		$log_content[7]	=	'';	
		$log_content[8]	=	$this->keyword;
		$log_content[9]	=	$this->sid;	
		$log_content[10]	=	'';	
		$log_content[11]	=	'';		
		$log_content[12]	=	'impression';		
		$log_content[13]	=	'';	
		$log_content[14]	=	$this->agent;
		
		fputcsv($handle, $log_content, ',','"');
		fclose($handle);
	}
}
?>