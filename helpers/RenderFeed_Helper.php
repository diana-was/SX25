<?php

/**
 * The sponsored Code**
 * Render the Feed if the layout 
 * 
 */
function renderFeed ($htmlCode, $domain_id, $keyword, $pRow, $sid='', $traffictype='') 
{
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db 	= db_class::getInstance();
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$feed	= new feed();
	$Html 	= new Html();
	
	if (!empty($keyword) && stripos($htmlCode, '{SPONSOR_LISTINGS') !== false)
	{
		$feed_id = 0;
		$sitetracking = '';
		if(($pRow['domain_feedtype'] == 'VC') || ($pRow['domain_feedtype'] == 'TS') || ($pRow['domain_feedtype'] == 'TS-2'))
		{
			//get Feed ID and Tracking with specific $keyword, VC ONLY
			$tracking = ($pRow['domain_tracking_type'] == 'source')?$traffictype:$keyword;
			$feed_id = $feed->getfeedidfromkeyword($pRow['keywords_feeds'],$tracking);
			
			$type = strtolower($pRow['domain_feedtype']);
			if (!$feed_id){
				$feed_id =	$feed->getFeedID($keyword,$controller->rootPath."config/id$type.csv", $pRow['domain_feedid']);
			}
		}
		else if($pRow['domain_feedtype'] == 'OB')
		{
			//get Tracking and Feed ID with specific $keyword, OB ONLY
			$defaultsitetracking 	= urlencode(str_replace("www.", "", $_SERVER['HTTP_HOST']));
			$feed_id = $pRow['domain_feedid'];
			
			$sitetracking = $feed->getfeedidfromkeyword($pRow['keywords_feeds'],$keyword);
			//print "<br>sitetracking $sitetracking<br>";
			if (!$sitetracking){
				$sitetracking  = $feed->getFeedID($keyword,$controller->rootPath.'config/idob.csv', $defaultsitetracking);
				
			}
		}
		//print "<br>feed_id ".$feed_id." $sitetracking<br>";
		$_ads = $feed->loadAds($pRow['domain_feedtype'], $feed_id , $keyword, $sitetracking);
		
		
		$feedArray = $feed->displayAds($_ads, $pRow['domain_feedtype'], $sid);
		
		
		$extrahtml = '';
		if($pRow['domain_feedtype'] == 'VC') {
			$extrahtml = '<script type="text/javascript" src="http://feed.validclick.com/check.php?affid='.$feed_id.'"></script>';
		}
		if($pRow['domain_feedtype'] == 'OB') {
			$extrahtml = '';
		}
	
		/* Include banned domains code */
		Helper_Class::registerHelper('bannedDomain');
		$helper = Helper_Class::getInstance();
		$extrahtml .= $helper->bannedDomain($feedArray, $pRow['domain_url'], $keyword);
		
		$layoutRow = $domainObj->getLayout();
		$sponsoredCode = $Html->parseSponsored($layoutRow['layout_sponsored'],$feedArray,$layoutRow['layout_sponsored_num'],$extrahtml,1);
		// Clean Up tags if not found
		$sponsoredCode = str_replace(array('{SELLER_RATINGS_ADVERTISER_INFO}','{SELLER_RATINGS_RATING}','{SELLER_RATINGS_REVIEW_COUNT}','{DEEPLINKS}'), '', $sponsoredCode);		
		
		$htmlCode = str_replace('{SPONSOR_LISTINGS}', $sponsoredCode, $htmlCode);

		//---------- more than one block adv ------------------------------$layoutRow['layout_sponsored_num']
		if (stripos($htmlCode, '{SPONSOR_LISTINGS') !== false)
		{
			$sponsoredCode2= $Html->parseSponsored($layoutRow['layout_sponsored'],$feedArray,$layoutRow['layout_sponsored_num'],$extrahtml,2);
			// Clean Up tags if not found
			$sponsoredCode2 = str_replace(array('{SELLER_RATINGS_ADVERTISER_INFO}','{SELLER_RATINGS_RATING}','{SELLER_RATINGS_REVIEW_COUNT}','{DEEPLINKS}'), '', $sponsoredCode2);		
			$htmlCode = str_replace('{SPONSOR_LISTINGS_BLOCK2}', $sponsoredCode2, $htmlCode);
			
			$feedNum = count($feedArray);
			for($i=0; $i<$feedNum; $i++)
			{
				$sponsored = $Html->parseSponsoredEach($layoutRow['layout_sponsored'],$feedArray,$i);
				// Clean Up tags if not found
				$sponsored = str_replace(array('{SELLER_RATINGS_ADVERTISER_INFO}','{SELLER_RATINGS_RATING}','{SELLER_RATINGS_REVIEW_COUNT}','{DEEPLINKS}'), '', $sponsored);		
				
				$htmlCode = str_replace("{SPONSOR_LISTINGS_$i}", $sponsored, $htmlCode);
			}
		}
	} 
	if (!empty($keyword) && (stripos($htmlCode, '{DOMAIN_SPONSOR}') !== false))
	{
		//domain sponsor -------------- Gordon added 25 Mar 2010 --------------
		$dsRow=$domainObj->getSponsorDomains($keyword);
		$dsite = (!isset($dsRow['domain']) || trim($dsRow['domain'])=='')?'http://'.$pRow['domain_url']:'http://'.trim($dsRow['domain']);				
		$htmlCode = str_replace('{DOMAIN_SPONSOR}', $dsite, $htmlCode);
	
		if(!isset($dsRow['domain']) || trim($dsRow['domain'])==''){  
			$dsscript = "<script>$(function(){ $('#iframe-wrap').hide(); $('#article-wrap').show();});</script>";
		}else{
			$dsscript = "<script>$(function(){ $('#article-wrap').hide(); $('#iframe-wrap').show(); });</script>";
		}
		$htmlCode = str_replace('{JAVASCRIPT}', $dsscript, $htmlCode);
		//------------------ domain sponsor end -----------------------------------
	}

	// Clean Up if not found
	if ((stripos($htmlCode, '{SPONSOR_LISTINGS') !== false))
	{
		$htmlCode = str_replace("{SPONSOR_LISTINGS}", '', $htmlCode);		
		for($i=0; $i<10; $i++){
			$htmlCode = str_replace("{SPONSOR_LISTINGS_$i}", '', $htmlCode);		
		}
	}
	return $htmlCode;
}
?>