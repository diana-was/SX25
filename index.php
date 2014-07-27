<?php
session_cache_expire(60);
session_start();
if(isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) 
{
	session_destroy();
	session_regenerate_id();
}
$_SESSION['timeout_idle'] = time() + session_cache_expire();
date_default_timezone_set('America/Los_Angeles');

include_once("config.php");

// clean referrer jumping 2 times once in index.php and landing in results.php
If (isset($controller->request['r'])) 
{
	$url = empty($controller->request['r'])?"index.php":"result.php"; // clean referrer
	$method = empty($controller->request['r'])?"post":"get"; // clean referrer
	echo '<BODY onLoad="document.myform.submit()">';
    echo '<FORM NAME="myform" ACTION="'.$url.'" METHOD="'.$method.'">';
    foreach ($controller->request as $k => $v)
    {
    	if ($k == 'r')
    	{
    		if (empty($v)) echo '<input type="hidden" name="r" value="1"/>';
    	}
    	else 
    		echo '<input type="hidden" name="'.$k.'" value="'.$v.'"/>';
    }
    echo '</FORM>';
    exit;
}

$orign_keyword = $pRow['domain_keyword'];
$keyword = $pRow['domain_keyword'];
$category	= !empty($domainObj->domain_product_category)?$domainObj->domain_product_category:'All';

if($pRow['domain_layout_id'] != '')
{
	$layoutRow = $domainObj->getLayout();
	$pRow['layout_folder'] = $layoutRow['layout_folder'];
	$pRow['page_keyword'] = $orign_keyword;
	$pRow['page_mapping'] = $keyword;

	// Get the related keywords
	$topics = TopicModule_Class::getInstance($db);
	$pRow['page_relates'] = $topics->getData($orign_keyword, $domainObj->domain_feedtype, 15, array('feed_id' => $domainObj->domain_feedid));

	/*-------------- extract images ---------------------------
	// firstly check images table to grab images, go to Bing if it is empty */
	$imgObj = ImageModule_Class::getInstance($db);
	$picList = $imgObj->getDomainImages($domain_id, $config->imageLibrary);
	$pRow = array_merge($pRow,$picList);	
	//$imgObj->printMe();

	//--------get default pic if empty ---------------
	if(!isset($pRow['page_pic']) || $pRow['page_pic']==''||$pRow['page_pic']==null) 
		$pRow['page_pic'] = $imgObj->getDefaultImage($config->imageLibrary);
	
	//---------------------multiple images ----------------edited by Gordon --------------------------
	if(!isset($pRow['page_pic_1']) || empty($pRow['page_pic_1']))  $imgObj->getDefaultImage($config->imageLibrary);
	if(!isset($pRow['page_pic_2']) || empty($pRow['page_pic_2']))  $imgObj->getDefaultImage($config->imageLibrary);
	if(!isset($pRow['page_pic_3']) || empty($pRow['page_pic_3']))  $imgObj->getDefaultImage($config->imageLibrary);

	/**
	 * Render the menus
	 * 
	 */
	Helper_Class::registerHelper('RenderMenu');
	$helper = Helper_Class::getInstance();
	$pRow = $helper->RenderMenu($domain_id, $keyword, $pRow);

	/**
	 * The magic wand**
	 * Render the layouts for the master page and the modules
	 * 
	 */
	Helper_Class::registerHelper('RenderModules');
	$htmlCode = $helper->renderModules('landing', $domain_id, $keyword, $orign_keyword, $pRow) ;
	
	Helper_Class::registerHelper('RenderArticles');
	$pRow = $helper->RenderArticles($htmlCode, $keyword, $orign_keyword, $pRow, true);
	
	Helper_Class::registerHelper('RenderQuestionAnswer');
	$pRow = $helper->renderQuestionAnswer($htmlCode, $keyword, $orign_keyword, $pRow, 'landing');
	
	Helper_Class::registerHelper('RenderDirectories');
	$pRow = $helper->renderDirectories($htmlCode, $keyword, $orign_keyword, $pRow) ;

	Helper_Class::registerHelper('RenderShopping');
	$pRow = $helper->renderShopping($htmlCode, $keyword, $category, $pRow, 'landing');
	
	Helper_Class::registerHelper('RenderEvent');
	$pRow = $helper->renderEvent($htmlCode, $keyword, $orign_keyword, $pRow, 'landing');
	
	/**
	 *  final html mapping of variables
	 */
	$Html = new Html();
	$htmlCode = $Html->replaceTestingURL($htmlCode,$controller->server_name,$controller->rootPath);
	$htmlCode = $Html->parseFinalHtml($htmlCode,$pRow);
	echo $htmlCode;
	
}


?>