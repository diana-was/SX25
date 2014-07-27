<?php
session_cache_expire(60);
session_start();
if(isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) 
{
	session_destroy();
	session_regenerate_id();
}
$_SESSION['timeout_idle'] = time() + session_cache_expire();
$sid = session_id();
date_default_timezone_set('America/Los_Angeles');

include_once("config.php");

if(!isset($_COOKIE['ads'])) {
	$url = (APPLICATION_ENVIRONMENT == 'TESTING' || APPLICATION_ENVIRONMENT == 'TESTLOCAL')?$controller->server_name:$pRow['domain_url'];
	setcookie("ads", "show", time()+60*60*12, "/",$url,0);

	if((empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER']==null || $_SERVER['HTTP_REFERER']=='') && empty($controller->module)){
		header('Location: '.$config->indexFile);
		//$controller->printMe(); echo '<pre>'; print_r($_SERVER);
		exit;
	}
}

# ========================

// This section is used for landing pages

$ori_kwd		= $controller->ori_kwd;
$orign_keyword 	= $controller->orign_keyword;
$keyword 		= !empty($controller->keyword)?($controller->keyword):$pRow['domain_keyword'];
$cs 			= isset($controller->request['cs']) ? $controller->request['cs'] : '';
$traffictype	= isset($controller->request['traffictype']) ? $controller->request['traffictype'] : '';

$mapping_keyword= $domainObj->getMappingKeyword($keyword);
$keyword_feed	= !empty($mapping_keyword)?$mapping_keyword:$keyword;
$category		= !empty($domainObj->domain_product_category)?$domainObj->domain_product_category:'All';

//---------------- log file ----------
/*$im = new ImpressionLog($config->logDir);
$im->keyword = $keyword;
$im->sid 	 = $sid;
$im->writeToFileOfToday();*/
//--------------------------------------

//print "keyword $keyword\n";
if($pRow['domain_layout_id'] != '')
{
	
	$layoutRow = $domainObj->getLayout();
	$pRow['layout_folder'] = $layoutRow['layout_folder'];
	$pRow['page_keyword'] = ucwords(strtolower($orign_keyword));
	$pRow['page_mapping'] = $keyword;

	// Get the related keywords
	$topics = TopicModule_Class::getInstance($db);
	$pRow['page_relates'] = $topics->getData($keyword, $domainObj->domain_feedtype, 15, array('feed_id' => $domainObj->domain_feedid));
	
	/*-------------- extract images ---------------------------
	// firstly check images table to grab images */
	$imgObj 	= ImageModule_Class::getInstance($db);
	$picList 	= $imgObj->getDomainImages($domain_id, $config->imageLibrary);
	$pRow 		= array_merge($pRow,$picList);	
	//$imgObj->printMe();
	
	//--------get default pic if empty ---------------
	if(!isset($pRow['page_pic']) || $pRow['page_pic']==''||$pRow['page_pic']==null) 
		$pRow['page_pic'] = $imgObj->getDefaultImage($config->imageLibrary);
	//--------------------------------------------------------------
		
	/**
	 * Render the menus
	 * 
	 */
	Helper_Class::registerHelper('RenderMenu');
	$helper = Helper_Class::getInstance();
	$pRow = $helper->RenderMenu($domain_id, $keyword, $pRow);
	
	/** 
	 * The page content - delete maked content by {PAGE_REPLACE_BEGIN} and {PAGE_REPLACE_END} in the layout
	 * 
	 */
	 
	if (!empty($controller->page))
	{
		Helper_Class::registerHelper('RenderPage');
		$helper->RenderPage($domain_id, $controller->page);
	}

	/**
	 * The magic wand**
	 * Render the layouts for the master page and the modules
	 * 
	 */
	
	Helper_Class::registerHelper('RenderModules');
	$htmlCode = $helper->renderModules('result', $domain_id, $keyword, $orign_keyword, $pRow);	
	
	Helper_Class::registerHelper('RenderArticles');
	$pRow = $helper->RenderArticles($htmlCode, $keyword, $orign_keyword, $pRow);	
	
	Helper_Class::registerHelper('RenderQuestionAnswer');
	$pRow = $helper->renderQuestionAnswer($htmlCode, $keyword, $orign_keyword, $pRow, 'result');
	
	Helper_Class::registerHelper('RenderDirectories');
	$pRow = $helper->renderDirectories($htmlCode, $keyword, $pRow['domain_keyword'], $pRow);

	Helper_Class::registerHelper('RenderShopping');
	$pRow = $helper->renderShopping($htmlCode, $keyword, $category, $pRow, 'result');

	Helper_Class::registerHelper('RenderEvent');
	$pRow = $helper->renderEvent($htmlCode, $keyword, $orign_keyword, $pRow, 'result');	
	/** 
	 * The sponsored Code
	 * 
	 */
	Helper_Class::registerHelper('RenderFeed');
	$htmlCode = $helper->renderFeed ($htmlCode, $domain_id, $keyword_feed, $pRow, $sid, $traffictype);
	
	/**
	 *  final html mapping of variables
	 */
	$Html = new Html();
	$htmlCode = $Html->replaceTestingURL($htmlCode,$controller->server_name,$controller->rootPath);
	$htmlCode = $Html->parseFinalHtml($htmlCode,$pRow);

	echo $htmlCode;
}


?>