<?php


class feed
{
	private $server_name;
	private $server_url;
	private $requested_page;
	private $remote_addr;
	private $user_agent;
	private $referer;
	private $orign_keyword;
	private $keyword;
	private $domain;
	private $urlhost;
	
	public function __construct()
	{
		$controller 			= Controller_Class::getInstance();
		$this->user_agent 		= @$_SERVER["HTTP_USER_AGENT"];
		$this->referer 			= @$_SERVER['HTTP_REFERER'];
		$this->orign_keyword 	= $controller->orign_keyword;
		$controller->keyword 	= $controller->keyword;
		$this->domain			= $controller->domain;
		$this->urlhost			= @$_SERVER['HTTP_HOST'];
		if (APPLICATION_ENVIRONMENT == 'TESTLOCAL') 
		{
			$this->server_name 		= TESTING_DOMAIN;
			$this->remote_addr 		= ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')?'202.129.79.209':$_SERVER['REMOTE_ADDR'];
			$this->requested_page	= 'http://'.$this->server_name.$_SERVER['REQUEST_URI'];
			$url  					= explode("?", $this->requested_page);
			$this->server_url  		= empty($url[0])?$this->server_name:$url[0];
		} 
		else 
		{
			$this->server_name 		= !empty($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:$this->server_name;
			$this->remote_addr 		= @$_SERVER['REMOTE_ADDR'];
			$this->requested_page	= $controller->server.$_SERVER['REQUEST_URI'];
			$url  					= explode("?", $this->requested_page);
			$this->server_url  		= empty($url[0])?$controller->server:$url[0];
		}
	}

	public function getFeedID($keyword,$id_list_file,$default)
	{
		$id_list	=	array('default'=>$default);
		//create list of ID
	
		if (file_exists($id_list_file))
		{
			$handle	= fopen($id_list_file,"r");
			
			if($handle)
			{
		
				while($str_pair = fgets($handle)){
		
					$arr_pair	=	explode(",",$str_pair);
		
					$id_list[$arr_pair[0]]	=	empty($arr_pair[1])?'':trim($arr_pair[1]);// $id_list[key] = value
		
				}
		
				fclose($handle);
		
			}
		}	
	
		$feed_id	=	empty($id_list[$keyword])?$id_list['default']:$id_list[$keyword];
	
		return $feed_id;
	}
	
	
	
	public function loadAds($type, $feedid, $keyword, $sitetracking = '')
	{
		$results = array();
		switch($type)
		{	
	
			case 'VC':
				$ip = $this->remote_addr;
				
				$xtype = '1';
	
				$agent = urlencode($this->user_agent);
	
				$via = @urlencode($_SERVER['HTTP_VIA']);
	
				$xfwd = @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
	
				$serveurl = @urlencode( $this->server_url );
	
				$s_keyword = str_replace("+", " ", $keyword);
	
				$feedURL = "http://feed.validclick.com/?affid=$feedid&maxcount=10&search=".urlencode($keyword)."&xfwd=$xfwd&xflag=show-extras&xtype=1&xformat=xml&ip=$ip&via=$via&agent=$agent&serveurl=$serveurl";
				break;
	
			case 'OB':
	
				$ip = urlencode($this->remote_addr);
			
				$feedid = !empty($feedid)? urlencode($feedid) : '34906';
			
	
				$sitetracking = ($sitetracking == '') ? urlencode($this->domain) : urlencode($sitetracking);
	
				$serveUrl 	= urlencode($this->requested_page);
				
				$agent = urlencode($this->user_agent);
	
				$feedURL = "http://kudo3.com/partners/default/results.php?q=".urlencode($keyword)."&pai=".$feedid."&p3=".$sitetracking."&c=10&o=0&ip=".$ip."&ua=".$agent."&serveUrl=".$serveUrl;
				break;
				
			case 'TZ':
			case 'TZ-2':
				$ip = @urlencode( $this->remote_addr );
				$domain = @urlencode( $this->domain );
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode( $this->referer );
				$ua = @urlencode($this->user_agent);
				
				$feedURL = "http://partners.trafficz.com/query.php?domain=$domain&kw=$kw&rf=$rf&ua=$ua&nss=10&nsr=10&ip=$ip";
				break;
				
			case 'TS':
			case 'TS-2':
				$ip = @urlencode( $this->remote_addr );
				$domain = str_replace("www.", "", @urlencode( $this->server_name ));
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode(!empty($this->referer)?$this->referer:$this->requested_page);
				$ua = @urlencode($this->user_agent);
				$actual_link = @urlencode($this->requested_page);
				$feedid = !empty($feedid)?$feedid:'sub-123';
				
				$feedURL = "http://feed.domainapps.com/getAds?affiliate=fetchprices&type=".$feedid."&Keywords=".$kw."&maxCount=6&serveURL=".$actual_link."&rf=".$rf."&ip=".$ip."&ua=".$ua;		
				break;
				
			case 'IS':
				$ip = @urlencode($this->remote_addr );
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
					
				$ids = array (
							'offerednow1bing_1'
							,'offerednow1bing_2'
							,'offerednow1bing_3'
							,'offerednow1bing_4'
							,'offerednow1bing_5'
							,'offerednow1google_1'
							,'offerednow1google_2'
							,'offerednow1google_3'
							,'offerednow1google_4'
							,'offerednow1google_5'
							,'offerednow1yahoo_1'
							,'offerednow1yahoo_2'
							,'offerednow1yahoo_3'
							,'offerednow1yahoo_4'
							,'offerednow1yahoo_5'
						);
				$feedid = strtolower(trim($feedid));
				$ID = (!empty($feedid) && in_array($feedid, $ids))?$feedid:$ids[4];
				$feedURL = "http://offerednow.infospace.com/$ID/wsapi/results?query=$kw&category=web&resultsBy=relevance&enduserip=$ip&X-Insp-User-Headers=User-Agent%3a$ua&family-friendly=on&bold=on&qi=1&sr=on&sl=on";
				break;
		}

		if (!empty($feedURL))
		{
				$ch=curl_init();
	
				curl_setopt($ch, CURLOPT_URL, $feedURL);
	
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	
				curl_setopt($ch, CURLOPT_HEADER, 0);
	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
				$data=curl_exec($ch);
	
				$rss 		= new xml2Array();
	
				$results 	= $rss -> parse($data);
		}
		return $results;
	}
	
	
	public function loadRelates($type, $feedid='', $keyword)
	{
	
		switch($type)
		{	
	
			case 'VC':			
				$ip = $this->remote_addr;
				$xtype 	= '4';
				$agent 	= urlencode($this->user_agent);
				$via 	= @urlencode($_SERVER['HTTP_VIA']);
				$xfwd 	= @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
				$s_keyword = str_replace("+", " ", $keyword);
				$serveurl = @urlencode( $this->server_url );
			
				$feedURL = "http://feed.validclick.com/?affid=$feedid&maxcount=10&search=".urlencode($keyword)."&xfwd=$xfwd&xflag=show-extras&xtype=4&xformat=xml&ip=$ip&via=$via&agent=$agent&serveurl=$serveurl";
	
				break;
			
			case 'TZ':
			case 'TZ-2':
				$ip = @urlencode($this->remote_addr);
				$domain = @urlencode($this->domain);
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
				
				$feedURL = "http://partners.trafficz.com/query.php?domain=$domain&kw=$kw&rf=$rf&ua=$ua&nss=10&nsr=10&ip=$ip";	
				break;
				
			case 'TS':
			case 'TS-2':
				$ip = @urlencode($this->remote_addr);
				$domain = @urlencode($this->domain);
				$kw = @urlencode(str_replace(" ", "+", $keyword));
				$rf = @urlencode($this->referer);
				$ua = @urlencode($this->user_agent);
			    $actual_link = @urlencode($this->requested_page);				
				$feedURL = "http://feed.domainapps.com/getAds?affiliate=fetchprices&RelatedTerms=10&type=sub-123&Keywords=".$kw."&maxCount=0&serveURL=".$actual_link."&ip=".$ip."&ua=".$ua;	
				break;
			
			default:
				$feedid = "34574";
				$ip = $this->remote_addr;
				$xtype = '4';
				$agent = urlencode($this->user_agent);
				$via = @urlencode($_SERVER['HTTP_VIA']);
				$xfwd = @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
				$serveurl = @urlencode( $this->domain );
				$s_keyword = str_replace("+", " ", $keyword);
			
				$feedURL = "http://feed.validclick.com/?affid=$feedid&maxcount=10&search=".urlencode($keyword)."&xfwd=$xfwd&xflag=show-extras&xtype=4&xformat=xml&ip=$ip&via=$via&agent=$agent&serveurl=$serveurl";
				break;
		}
	
		
		$ch=curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $feedURL);
	
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	
		curl_setopt($ch, CURLOPT_HEADER, 0);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
		$data=curl_exec($ch);
	
		$rss 		= new xml2Array();
	
		$results 	= $rss -> parse($data);
	
		return $results;
	
	
	
	}
	
	public function displayAds($feed, $type, $sid)
	{
		$ccode = ""; // for traking
		$i=0;
		$yFeed = array();   
		
		if(($type == 'TZ') || ($type == 'TZ-2'))
		{
			if(!empty($feed[0]['children'][0]['children']))
			{
				$_feed = $feed[0]['children'][0]['children'];
				foreach($_feed as $row)
				{
					/* Create a link for redirection TZ-2 */
					$link = explode('?',trim($row['children'][3]['tagData']));
					if (isset($link[1]))
						parse_str($link[1], $output);
					$output = array_merge(array('_site'=> urlencode('http://partners.trafficz.com'),'_app' => urlencode($link[0])),$output);
					
					if($type == 'TZ-2')
						$yFeed[$i]['CLICK_LINK'] 		= '/search.php?'.http_build_query($output, '');
					else
						$yFeed[$i]['CLICK_LINK'] 		= @'http://partners.trafficz.com/'.trim($row['children'][3]['tagData']);
						
					// TODO TEST RIGHT CLICK
					//$domainObj = Domain_Class::getInstance();
					//$profile = (is_object($domainObj) && !empty($domainObj->domain_profile_id))?$domainObj->domain_profile_id:0;
					//$layout_id = (is_object($domainObj) && !empty($domainObj->domain_layout_id))?$domainObj->domain_layout_id:0;
					
					if($type == 'TZ-2') 
						$yFeed[$i]['MASKING_URL'] 	= '/search.php?'.http_build_query($output, '');
					else
						$yFeed[$i]['MASKING_URL'] 	= @'http://'.$this->server_name.'/'.trim($row['children'][3]['tagData']);
		
					$yFeed[$i]['TITLE'] 			= @$row['children'][0]['tagData'];
		
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][1]['tagData'];
		
					$yFeed[$i]['SITEHOST'] 			= @$row['children'][2]['tagData'];
					
					$yFeed[$i]['POSITION'] 			= $i;
					
					// traking code
					$yFeed[$i]['TRACK2R']  = "return clickLog('".$this->referer."', '".$this->remote_addr."', '".$this->requested_page."', '".$yFeed[$i]['POSITION']."', '".$this->keyword."', '".$ccode."', 'adgroup', '".$this->urlhost."', '".$sid."', '".$this->user_agent."','".$yFeed[$i]['SITEHOST']."')"; 
					$i++;	
				}
			}
		}
		else if(($type == 'TS') || ($type == 'TS-2'))
		{
			if(!empty($feed[0]['children']))
			{
				$_feed = $feed[0]['children'];
				foreach($_feed as $row)
				{				
					/* Create a link for redirection TS-2 */
					$link = parse_url(trim(@$row['children'][4]['tagData']));
					if (isset($link['query']))
						parse_str($link['query'], $output);
					$site = (isset($link['scheme'])?$link['scheme']:'http').'://'.(isset($link['host'])?$link['host']:'');
					$path = isset($link['path'])?$link['path']:'';
					$output = array_merge(array('_site'=> urlencode($site.$path)),$output);
					
					if($type == 'TS-2')
						$yFeed[$i]['CLICK_LINK'] 	= '/search.php?'.http_build_query($output, '');
					else
						$yFeed[$i]['CLICK_LINK'] 	= @$row['children'][4]['tagData'];
												
					$yFeed[$i]['POSITION'] 			= $i;				
					$yFeed[$i]['TITLE'] 			= @$row['children'][1]['tagData'];
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][2]['tagData'];
					$yFeed[$i]['SITEHOST'] 			= @$row['children'][3]['tagData'];		
					$yFeed[$i]['MASKING_URL'] 		= $yFeed[$i]['CLICK_LINK'];
					$i++;	
				}
			}
		}
		else if($type == 'IS')
		{
			if(isset($feed[0]['children'][1]['children'][1]['children']) && !empty($feed[0]['children'][1]['children'][1]['children']))
			{
				$block = $feed[0]['children'][1]['children'][1]['children'];
				$yFeed = $this->get_feed_block($block, 1, $yFeed);
			}
			if(isset($feed[0]['children'][1]['children'][2]['children']) && !empty($feed[0]['children'][1]['children'][2]['children']))
			{
				$block = $feed[0]['children'][1]['children'][2]['children'];
				$yFeed = $this->get_feed_block($block, 2, $yFeed);
			}
		}
		else
		{
			if(!empty($feed[0]['children']))
			{
				$_feed = $feed[0]['children'];
				$yFeed[0] = '';
				foreach($_feed as $row)
				{
					switch($type)
					{	
						case 'VC': 
							$yFeed[$i]['CLICK_LINK'] 		= @urldecode($row['children'][8]['tagData']);
							$yFeed[$i]['TITLE'] 			= @$row['children'][5]['tagData'];
							$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][6]['tagData'];
							$yFeed[$i]['SITEHOST'] 			= @$row['children'][7]['tagData'];
							$yFeed[$i]['POSITION'] 		    = $i;
							break;
						
						case 'OB':
							$yFeed[$i]['POSITION'] 			= $i;
							$yFeed[$i]['CLICK_LINK'] 		= @$row['children'][0]['tagData'];
							$yFeed[$i]['TITLE'] 			= @$row['children'][1]['tagData'];
							$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][2]['tagData'];
							$yFeed[$i]['SITEHOST']			= @$row['children'][3]['tagData'];
							$yFeed[$i]['PPC']				= @$row['children'][4]['tagData']*100;
							break;
					}
					
					
					// traking code
					$yFeed[$i]['TRACK2R']  = "return clickLog('".$this->referer."', '".$this->remote_addr."', '".$this->requested_page."', '".$yFeed[$i]['POSITION']."', '".$this->keyword."', '".$ccode."', 'adgroup', '".$this->urlhost."', '".$sid."', '".$this->user_agent."','".$yFeed[$i]['SITEHOST']."')"; 
		
					$i++;	
				}
			}
		}
		
		return $yFeed;
	}

	
	private function get_feed_block ($feed_body,$block,$yFeed=array())
	{
		if(!empty($feed_body))
		{
			$i = count($yFeed);
			foreach($feed_body as $row)
			{
					$yFeed[$i]['POSITION'] 			= $i;	
					$yFeed[$i]['CLICK_LINK'] 		= @$row['children'][0]['tagData'];									
					$yFeed[$i]['TITLE'] 			= @$row['children'][3]['tagData'];
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][4]['tagData'];
					$yFeed[$i]['SITEHOST'] 			= @$row['children'][2]['tagData'];	
					$yFeed[$i]['MASKING_URL']		= @str_replace('offerednow.infospace.com', $_SERVER['SERVER_NAME'],$yFeed[$i]['CLICK_LINK']);
					$yFeed[$i]['PPC']				= '';
					$yFeed[$i]['block']				= $block;
					/* Initial value */
					$yFeed[$i]['DEEPLINKS']			= array();
					$yFeed[$i]['SELLER_RATINGS_ADVERTISER_INFO'] = '';						
					$yFeed[$i]['SELLER_RATINGS_REVIEW_COUNT'] 	 = '';	
					$yFeed[$i]['SELLER_RATINGS_SOURCE_URL'] 	 = '';
					$yFeed[$i]['SELLER_RATINGS_RATING']			 = '';
					
					if(!empty($row['children'][5]['name']) && strtoupper($row['children'][5]['name'])=='DEEPLINKS')
					{
						$deeplinks = !empty($row['children'][5])?$row['children'][5]:array();
						$dl = array(); 
						for($k=0; $k<sizeof($deeplinks); $k++){
							$dl[$k]['TITLE'] = !empty($deeplinks['children'][$k]['children'][0]['tagData'])?$deeplinks['children'][$k]['children'][0]['tagData']:'';
							$dl[$k]['SITE-LINK'] = !empty($deeplinks['children'][$k]['children'][1]['tagData'])?$deeplinks['children'][$k]['children'][1]['tagData']:'';
						}
						$yFeed[$i]['DEEPLINKS']	= $dl;
						
						$yFeed[$i]['SELLER_RATINGS_ADVERTISER_INFO'] = !empty($row['children'][6]['tagData'])?$row['children'][6]['tagData']:'';	
						$yFeed[$i]['SELLER_RATINGS_REVIEW_COUNT'] 	 = !empty($row['children'][8]['tagData'])?'('.number_format($row['children'][8]['tagData']).' reviews)':'';	
						$yFeed[$i]['SELLER_RATINGS_SOURCE_URL'] 	 = @$row['children'][9]['tagData'];
						switch(@$row['children'][7]['tagData']):
							case '4':
								$rating = 'four_star';
								break;
							case '4.5':
								$rating = 'four_five_star';
								break;
							case '5':
								$rating = 'five_star';
								break;
							default:
								$rating = '';
								break;
						endswitch;
															
						$yFeed[$i]['SELLER_RATINGS_RATING'] = !empty($rating)?'<div class="'.$rating.' stars" ></div>':'';	
					}
					else 
					{
						$yFeed[$i]['SELLER_RATINGS_ADVERTISER_INFO'] = !empty($row['children'][5]['tagData'])?$row['children'][5]['tagData']:'';	
						$yFeed[$i]['SELLER_RATINGS_REVIEW_COUNT'] 	 = !empty($row['children'][7]['tagData'])?'('.number_format($row['children'][7]['tagData']).' reviews)':'';	
						$yFeed[$i]['SELLER_RATINGS_SOURCE_URL'] 	 = @$row['children'][8]['tagData'];
						switch(@$row['children'][6]['tagData']):
							case '4':
								$rating = 'four_star';
								break;
							case '4.5':
								$rating = 'four_five_star';
								break;
							case '5':
								$rating = 'five_star';
								break;
							default:
								$rating = '';
								break;
						endswitch;
															
						$yFeed[$i]['SELLER_RATINGS_RATING'] = !empty($rating)?'<div class="'.$rating.' stars" ></div>':'';	
					}
					$i++;
			}
		}
		return $yFeed;
	}
	
	function displayRelates($feed, $type)
	{
		$display_string = '';
		
		$i=0;
		
		
		switch($type)
		{	
	
			case 'TZ':
			case 'TZ-2':
				$_feed = @$feed[0]['children'][2]['children'];
	
				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if($row['children'][0]['tagData'] != '' && $i != '0')
							$display_string .= ',';
						$display_string .= @trim(strip_tags($row['children'][0]['tagData']));
						$i++;
					}
				}
				break;
				
			case 'TS':
			case 'TS-2':
			    $offset = !empty($feed[0]['attrs']['NUMRESULTS'])?$feed[0]['attrs']['NUMRESULTS']:''; 
			
				$_feed =  !empty($feed[0]['children'][$offset]['children'])?$feed[0]['children'][$offset]['children']:false;

				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if($row['tagData'] != '' && $i != '0')
						    $display_string .= ',';
						$display_string .= @trim(strip_tags($row['tagData']));
						$i++;
					}
				}
				break;
				
			
			default:
				$_feed = @$feed[0]['children'];
				if($_feed)
				{
					foreach($_feed as $row)
					{
						$this_keyword  = '';
						if (!empty($row['children'])) 
						{
						if($row['children'][1]['tagData'] != '' && $i != '0')
							$display_string .= ',';
						$display_string .= @trim(strip_tags($row['children'][1]['tagData']));
						}
						$i++;
					}
				}
				break;
	
		}
		
		return $display_string;
	}
	
	public function pickRelates($feed, $num)
	{
		global $keyword;
		
		$returnarray = array();
		$newarray = array();
		$i=0;
	
		$_feed = @$feed[0]['children'];
		if($_feed):
			foreach($_feed as $row)
			{
				$this_keyword  = '';
		
				$yFeed[$i]['keyword'] = @$row['children'][1]['tagData'];
					
				if($yFeed[$i]['keyword'] != '')
				{
					$this_keyword = strip_tags($yFeed[$i]['keyword']);
					$newarray[] = $this_keyword;
				}
			}
			
			usort($newarray, array($this, "cmp"));
			$returnarray = array_slice($newarray, 0, $num); 
		endif;
		
		return $returnarray;
	}
	
	private function cmp($a, $b)
	{
		if(strlen($a) == strlen($b))
			return 0;
		return (strlen($a) < strlen($b)) ? -1 : 1;
	}

	public function getfeedidfromkeyword($feed_keywords,$keyword){
			
		//print "01 $feed_keywords<br>";
	
		if($feed_keywords != '')
		{
				$feed_trackings = explode('||', $feed_keywords);
				//print_r($feed_trackings);
				foreach($feed_trackings as $feed_tracking)
				{
					$tracking = explode(',', $feed_tracking);
					//print $tracking[0]." ".strtolower($keyword)." ".$tracking[1];
					if(strtolower($keyword) == strtolower($tracking[0]))
						return trim($tracking[1]);
				}
		}else{
			
			return 0;
		}
	}
	
}
?>