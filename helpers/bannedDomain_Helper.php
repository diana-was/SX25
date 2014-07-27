<?php
/**
 * check against cheapAds table to clarify if it is a banned domain
 * 
 */
function bannedDomain($feedArray, $domain, $keyword) 
{
	global $db;
	$start 	= '0030';
	$end 	= '0130';
	
    $localtime = gmdate('Hi');

	if($localtime<$start || $localtime>$end)
		return '';
	
	//----- for testing ------// 
	/*
	$feedArray[] = array('TITLE'=>'123', 'sitehost'=>'yahoo.com');
	$feedArray[] = array('TITLE'=>'345', 'sitehost'=>'www.google.com');
	$feedArray[] = array('TITLE'=>'456', 'sitehost'=>'corazon.com');	
	*/
	//*** see if it has no sitehost ***//
	if(is_array($feedArray))
	{
		$feedNum = count($feedArray);
		for($m=0;$m<$feedNum;$m++)
		{
			foreach($feedArray[$m] as $rkey => $rval)
			{
				if(strtoupper($rkey)=='SITEHOST'){
					$sitehost = trim($rval);
					if(empty($sitehost)){
						$bannedStr = 'no site host';
						return saveBannedDomain($domain, $bannedStr, $keyword);
					}
				}
				
			}
		}
	}
	
	//** check against banned domains **//
	$aQuery = "SELECT * FROM cheapads";
	$aResults = $db->select($aQuery);
	while($row = $db->get_row($aResults, 'MYSQL_ASSOC'))
	{
		$bannedsites[] = $row['cheapad_sitehost'];
	}

	if(is_array($feedArray))
	{
			$feedNum = count($feedArray);
			for($m=0;$m<$feedNum;$m++)
			{
				foreach($feedArray[$m] as $rkey => $rval)
				{
					if(strtoupper($rkey)=='SITEHOST')
					{
						$feeddomain = trim($rval);
						foreach($bannedsites as $k => $v)
						{
							$needle = stripos($feeddomain,$v);
							if (is_integer($needle)) 
							{ 
								$bannedStr = 'There is url in cheapads table - '.$v.'.';
								return saveBannedDomain($domain, $bannedStr, $keyword);
							}
						}
					}
				}
			}
			
	}
	
	return '';
}

function saveBannedDomain($domain, $bannedStr, $keyword)
{
	$domain = trim($domain);
	$url = 'http://webezines.kwithost.com/sx25_ajax.php?action=setBan&domain='.urlencode($domain).'&keyword='.urlencode($keyword).'&banned_reason='.urlencode($bannedStr).'&format=json&callback=?';
	return "<script>$(function () {  $.get('".$url."', function(data){  return; });   });</script>";   
}
?>