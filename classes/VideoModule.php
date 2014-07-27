<?php
/**	APPLICATION:	SX25
*	FILE:			VideoModule
*	DESCRIPTION:	display domain - VideoModule_Class read video from database
*	CREATED:		15 October 2010 by Diana De vargas
*	UPDATED:									
*/

class VideoModule_Class extends  Module_Class
{
	protected	$moduleName = 'Video';
	protected	$sourceList = array('youtube','rhapsody','hulu','bing');
	
    /**
     * constructor : call parent constructor
     *
     * @param object $db database object
     *
     * @return void
     */
	public function __construct(db_class $db)
	{
		return parent::__construct($db);
	}

    /**
     * Get the VideoModule static object
     *
     * @return self
     */
	public static function getInstance(db_class $db) 
    {
    	return parent::getInstance($db,__CLASS__);
    }
	
	protected function __setDefaultLayout() 
	{
		$layout = '
			<!-- Video Module -->
			<h3>Videos</h3>
			<div class="moduleVideo">
				<div class="divVideo" id="divVdAll">
					<h6>{VIDEO_SOURCES_TITLES}</h6>
					<button id="divVideoAllPrev" type="button" class="buttonPrev"></button>
					<div id="divVideoAll"  class="carousel">
					<ul>
						{VIDEO_LIST}
					</ul>
					<div id="videoWidgetAll" class="videoWidget"></div>
					</div>
					<button id="divVideoAllNext" type="button" class="buttonNext"></button>
				</div>
			</div>
			';
			$js = '  	
						var text = $("#divVdAll h6").html();
						if (text.length > 0) {
							$("#divVdAll").css("display","block");
						    $("#divVideoAll").jCarouselLite({
						        btnNext: "#divVideoAllNext",
						        btnPrev: "#divVideoAllPrev",
					        	easing:  "backout",
					            speed:   700
						    });
						    $("#divVideoAll li").mouseover(function() {
							    $(this).children().each(function () {
									var elment = this.tagName;
									switch (elment) {
									case "SPAN" : $("#videoWidgetAll").html( $(this).html() );
												break; 
									}
							    });						    	 
						    });
						}
						
						';
			$css = '/*Module video Div*/
					.moduleVideo {
					    position: relative;
					}
					
					/*source video Div*/
					.divVideo {
					    position: relative;
					    margin: 0 0 30px 10px;
					    padding: 10px 0 0;
					    clear:both;
					    display: none;
					}
					
					/*source image title*/
					.divVideo h6 {
					    margin: 0 0 10px 0px;
					    font-style:normal;
					    font-size:0.8em;
					    font-weight: bold;
					}
					
					/*image widget*/
					.videoWidget {
					    height: 1.5em;
					    margin: 3px 0px 2px 3px;
					    clear:both;
					}
					
					/*carrusel buttons*/
					.divVideo button {
					    float: left;
					    display: block;
					    margin-top: 48px;
					    border: 0;
					    height: 40px;
					    width: 40px;
					}
					
					.buttonPrev {
						background: url("../images/prev.jpg") no-repeat;
					}
					
					.buttonNext {
						background: url("../images/next.jpg") no-repeat;
					}
					
					/*carrusel div*/
					div.carousel {
					    background-color: #DFDFDF;
					    border: 1px solid black;
					    float: left;
					    display: block;
					    overflow: hidden;
					    position: relative; 
					    z-index: 2; 
					    left: 0px; 
					    width: 510px;
					}
					
					div.carousel ul {
						margin: 0pt; 
						padding: 0pt; 
						position: relative; 
						list-style-type: none; 
						z-index: 1; 
						width: 2890px; 
						left: -1190px;
					}
					
					div.carousel ul li {
						overflow: hidden; 
						float: left; 
						height: 136px;
					}
					
					div.carousel ul li img {
						height: 120px;
						width: 120px;
						margin: 5px;
						padding: 1px;
						background-color: #DFDFDF;
						border:2px solid black;
					}
					
					.moduleVideo .description {
					    display:none;
					} 
			';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
			$this->layout['id'] = $this->_db->insert_sql($pQuery);	
	 }
	
	/**
	 * Video from Youtube ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getYoutubeVideoSearch($keyword = '', $per_page = 4)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();

		$youtubeVideos = array();
		$keyword = str_replace(" ", "+", str_replace("-", "+", $keyword));
		
		$this->_curlObj->createCurl('get','http://gdata.youtube.com/feeds/base/videos',array(	"q" => $keyword,
																								"max-results" => $per_page,
																								//"alt" => 'rss',
																								 "v" => 2,
																								 "format" => 5
																							));
		//$this->_curlObj->displayResponce();
		
		$result =$this->_curlObj->__toString();
		$result = simplexml_load_string($result);	
		if (!empty($result))
		{
			foreach ($result->entry as $key => $item)
			{
				if ($key > $per_page) break;	
				preg_match('/<img alt="" src="(.+)">/',(string) $item->content, $photo_src); 
//echo '<pre>'; print_r($item->content);				
				$youtubeVideos[] = array(
					'content_title' => (string) $item->title,								
					'content_link' => (string) str_replace(array('watch?','&amp;feature=youtube_gdata','v='),array('','','v/'),$item->link['href']),
					//'content_photo_src' => $item->content['src'],
					'content_photo_src' => $photo_src[1],
					'content_author' => (string) $item->author->name,
					'content_time_start' => (string) strtotime($item->published),		
					'content_source' => 'Youtube'		
					);
			}		
		}

		return $youtubeVideos;
	}

	/**
	 * Video from Rhapsody ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getRhapsodyVideoSearch($keyword = '', $per_page = 4)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		$s_keyword = str_replace(" ", "+", str_replace("-", "+", $keyword));
		$rhapsodyVideo = array();	

		$this->_curlObj->createCurl('get','http://www.rhapsody.com/-search',array(	"query" => $keyword,
																					"searchtype" => 'video', 
																					 "from" => 'guide'
																				));
		//$this->_curlObj->displayResponce();
		
		$html =$this->_curlObj->__toString();
		if (!empty($html)){
			list($one,$need_html) = explode("<div class=\"mainCol\">", $html);
			list($result_html,$tow) = explode("<div class=\"adCol\">", $need_html);
			$preg = "|<tr>\D.+href=\"(.+)\" rcid.+ src=\"(.+)\" border=\"0\">.+\D.+\D(.+)\D(.+)\D.+\D(.+)|";
			@preg_match_all($preg, $result_html, $matches);
			$count = count($matches[1]);
			if ($count < $per_page) $per_page = $count;
			for ($i = 0; $i < $per_page; $i++) {
				$rhapsodyVideo[] = array(
					'content_title' => strip_tags($matches[3][$i]),
					'content_photo_src'=> $matches[2][$i],
					'content_link' => $matches[1][$i],
					'content_author' => strip_tags($matches[4][$i]),
					'content_time_start' => str_replace("Date Added: ", "", strip_tags($matches[5][$i])),
					'content_source' => 'Rhapsody'		
				);
			}
		}
		
		return $rhapsodyVideo;
	}

	/**
	 * Video from Hulu ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getHuluVideo($keyword='',$per_page=10,$allow=1){
		if ( empty($keyword) || !is_string($keyword) ) return array();
		$keyword .= ' site:hulu.com'; 
		
		$SearchEngine = new SearchEngine_Class();

		return $SearchEngine->getBingVideo('hulu',$keyword,$per_page, $allow);	
	}		

	/**
	 * Video from Bing ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getBingVideo($keyword='',$per_page=10,$allow=1){
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$SearchEngine = new SearchEngine_Class();

		return $SearchEngine->getBingVideo('bing',$keyword,$per_page, $allow);	
	}		
	
	/**
	 * get Videos
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numVideos videos to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numVideos,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		$numImg = round($numVideos/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'rhapsody': $data = array_merge($data,$this->getRhapsodyVideoSearch($keyword,$plus));
								   break;
					case 'youtube':   $data = array_merge($data,$this->getYoutubeVideoSearch($keyword,$plus));
								   break;
					case 'hulu':   $data = array_merge($data,$this->getHuluVideo($keyword,$plus));
								   break;
					case 'bing':   $data = array_merge($data,$this->getBingVideo($keyword,$plus));
								   break;
				}
			}
		}
		
		return $data;
	}

}