<?php

/**
 * The magic wand**
 * Replace the Page Tags int the result page layout to be use in the other helpers
 * 
 */
function renderPage ($domain_id, $pageName) 
{
	$htmlCode = '';
	$controller = Controller_Class::getInstance();
	$db = db_class::getInstance();
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$layoutRow = $domainObj->getLayout();
	$htmlCode 	= isset($layoutRow['layout_result'])?$layoutRow['layout_result']:'';
	
	if (stripos($htmlCode, '{PAGE_') !== false)
	{
		// Init objects
		$db = db_class::getInstance();
		$Html = new Html();
		$Page = new Page_Class($db);

		// Get page
		$pageInfo = $Page->get_pagename_info($pageName);

		if ($pageInfo)
		{		
			$iniPos = stripos($htmlCode, '{PAGE_REPLACE_BEGIN}');
			$endPos = stripos($htmlCode, '{PAGE_REPLACE_END}');
			while ($iniPos !== false && $endPos !== false)
			{
				$htmlCode = substr($htmlCode, 0, $iniPos).substr ($htmlCode, $endPos + 18);
				$iniPos = stripos($htmlCode, '{PAGE_REPLACE_BEGIN}');
				$endPos = stripos($htmlCode, '{PAGE_REPLACE_END}');
			}
			$replace['PAGE_CONTENT'] 	= $pageInfo['page'];
			$replace['PAGE_TITLE'] 		= $pageInfo['page_display_name'];
			$domainObj->layout_result	= $Html->replaceHtmlTags($htmlCode,$replace);
		}
	}
	
	return;
}
?>