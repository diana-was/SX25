<?php
/**	APPLICATION:	SX25
*	FILE:			ImageModule
*	DESCRIPTION:	display domain - ImageModule_Class read image from database
*	CREATED:		15 October 2010 by Diana De vargas
*	UPDATED:									
*/

class ImageModule_Class extends  Module_Class
{
	protected	$moduleName = 'Image';
	protected	$sourceList = array('google','bing','people','flickr','db');
	private		$pictureList = array();
	private		$defaultImage = 'Default_0.jpg';
	private		$defaultAvatar = 'Default_1.jpg';
	
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
     * Get the ImageModule static object
     *
     * @return self
     */
	public static function getInstance(db_class $db) 
    {
    	return parent::getInstance($db,__CLASS__);
    }
	
	public function getDomainImages($domain_id,$imagePath)
	{
		$this->pictureList = array();
		/*-------------- extract images ---------------------------
		// firstly check images table to grab images, go to Bing if it is empty */
		$productQuerychk = "SELECT * FROM images WHERE image_domain_id='".$domain_id."'";
		$pResultchk =  $this->_db->select($productQuerychk);
		$pRow = array();
		while($array = $this->_db->get_row($pResultchk, 'MYSQL_ASSOC')){
			$location = trim($array['image_location']);
			switch ($location) 
			{
				case 'result_pic': 	$pRow['result_pic'] = $imagePath.$array['image_name'];
									$pRow['page_pic']   = $imagePath.$array['image_name'];
									break;
				case 'landing_pic':	$pRow['landing_pic']= $imagePath.$array['image_name'];
									$pRow['page_pic'] 	= $imagePath.$array['image_name'];
									break;
				case 'page_pic_1':	$pRow['page_pic_1'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_2':	$pRow['page_pic_2'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_3':	$pRow['page_pic_3'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_4':	$pRow['page_pic_4'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_5':	$pRow['page_pic_5'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_6':	$pRow['page_pic_6'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_7':	$pRow['page_pic_7'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_8':	$pRow['page_pic_8'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_9':	$pRow['page_pic_9'] = $imagePath.$array['image_name'];
									break;
				case 'page_pic_10':	$pRow['page_pic_10'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_1':	$pRow['menu_pic_1'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_2':	$pRow['menu_pic_2'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_3':	$pRow['menu_pic_3'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_4':	$pRow['menu_pic_4'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_5':	$pRow['menu_pic_5'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_6':	$pRow['menu_pic_6'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_7':	$pRow['menu_pic_7'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_8':	$pRow['menu_pic_8'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_9':	$pRow['menu_pic_9'] = $imagePath.$array['image_name'];
									break;
				case 'menu_pic_10':	$pRow['menu_pic_10'] = $imagePath.$array['image_name'];
									break;
			}
			$this->pictureList[] = $array;		
		}
		//--------finish load pic from database ---------------
		return $pRow;
	}

	public function getDefaultImage($imagePath)
	{
		return $imagePath.$this->defaultImage;
	}

	public function getDefaultAvatar($imagePath)
	{
		return $imagePath.$this->defaultAvatar;
	}
	
	protected function __setDefaultLayout() 
	{
		$layout = '		
					<style src="js/jcarousellite_views.css" rel="stylesheet" type="text/css"></style>
					<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript" language="javascript"></script>
					<script src="js/jcarousellite.js" type="text/javascript" language="javascript"></script>
					<script src="js/jquery.easing.js" type="text/javascript" language="javascript"></script>
					<script src="js/jquery.mousewheel.js" type="text/javascript" language="javascript"></script>
					
					<!-- Image Module -->
					<h3>Images</h3>
					<div class="moduleImage">
						<div class="imgWidget">
							<a id="imgWidgetLink" href="" target="_blank"><img id="imgWidget" alt="" src=""></img></a>
							<h6 id="imgWidgetCapture"></h6>
						</div>
						<div class="divImage" id="divImgSrc1">
							<h2><a href="{IMAGE_SOURCELINK_1}" target="_blank">{IMAGE_SOURCETITLE_1}</a></h2>
							<button id="divImageSrc1Prev" type="button" class="buttonPrev"></button>
							<div id="divImageSrc1"  class="carousel">
							<ul>
								{IMAGE_LIST_1}
							</ul>
							</div>
							<button id="divImageSrc1Next" type="button" class="buttonNext"></button>
						</div>
						<div class="divImage" id="divImgSrc2">
							<h2><a href="{IMAGE_SOURCELINK_2}" target="_blank">{IMAGE_SOURCETITLE_2}</a></h2>
							<button id="divImageSrc2Prev" type="button" class="buttonPrev"></button>
							<div id="divImageSrc2" class="carousel">
							<ul>
								{IMAGE_LIST_2}
							</ul>
							</div>
							<button id="divImageSrc2Next" type="button" class="buttonNext"></button>
					
						</div>
						<div class="divImage" id="divImgSrc3">
							<h2><a href="{IMAGE_SOURCELINK_3}" target="_blank">{IMAGE_SOURCETITLE_3}</a></h2>
							<button id="divImageSrc3Prev" type="button" class="buttonPrev"></button>
							<div id="divImageSrc3" class="carousel">
							<ul>
								{IMAGE_LIST_3}
							</ul>
							</div>
							<button id="divImageSrc3Next" type="button" class="buttonNext"></button>
						</div>
						<div class="divImage" id="divImgSrc4">
							<h2><a href="{IMAGE_SOURCELINK_4}" target="_blank">{IMAGE_SOURCETITLE_4}</a></h2>
							<button id="divImageSrc4Prev" type="button" class="buttonPrev"></button>
							<div id="divImageSrc4" class="carousel">
							<ul>
								{IMAGE_LIST_4}
							</ul>
							</div>
							<button id="divImageSrc4Next" type="button" class="buttonNext"></button>
						</div>
					</div>
				 ';
			$js = '  	var text = $("#divImgSrc1 h2 a").html();
						if (text.length > 0) {
							$("#divImgSrc1").css("display","block");
						    $("#divImageSrc1").jCarouselLite({
						        btnNext: "#divImageSrc1Next",
						        btnPrev: "#divImageSrc1Prev",
						    	easing:  "backout",
						        speed:   700
						    });
						    $("#divImageSrc1 li").click(function() {
							    $(this).children().each(function () {
									var elment = this.tagName;
									switch (elment) {
									case "IMG" : $("#imgWidget").attr("src", $(this).attr("src"));
												 $("#imgWidget").css("display","block");
												break;
									case "A" : $("#imgWidgetLink").attr("href", $(this).attr("href"));
												break;
									case "SPAN" : $("#imgWidgetCapture").html( $(this).html() );
												break; 
									}
							    });						    	 
						    });
						}
						
						var text = $("#divImgSrc2 h2 a").html();
						if (text.length > 0) {
							$("#divImgSrc2").css("display","block");
						    $("#divImageSrc2").jCarouselLite({
						        btnNext: "#divImageSrc2Next",
						        btnPrev: "#divImageSrc2Prev",
						    	easing:  "backout",
						        speed:   700
						    });
						    $("#divImageSrc2 li").click(function() {
							    $(this).children().each(function () {
									var elment = this.tagName;
									switch (elment) {
									case "IMG" : $("#imgWidget").attr("src", $(this).attr("src"));
												 $("#imgWidget").css("display","block");
												break;
									case "A" : $("#imgWidgetLink").attr("href", $(this).attr("href"));
												break;
									case "SPAN" : $("#imgWidgetCapture").html( $(this).html() );
												break; 
									}
							    });						    	 
						    });
						}
						
						var text = $("#divImgSrc3 h2 a").html();
						if (text.length > 0) {
							$("#divImgSrc3").css("display","block");
						    $("#divImageSrc3").jCarouselLite({
						        btnNext: "#divImageSrc3Next",
						        btnPrev: "#divImageSrc3Prev",
						    	easing:  "backout",
						        speed:   700
						    });
						    $("#divImageSrc3 li").click(function() {
							    $(this).children().each(function () {
									var elment = this.tagName;
									switch (elment) {
									case "IMG" : $("#imgWidget").attr("src", $(this).attr("src"));
												 $("#imgWidget").css("display","block");
												break;
									case "A" : $("#imgWidgetLink").attr("href", $(this).attr("href"));
												break;
									case "SPAN" : $("#imgWidgetCapture").html( $(this).html() );
												break; 
									}
							    });						    	 
						    });
						}
						
						var text = $("#divImgSrc4 h2 a").html();
						if (text.length > 0) {
							$("#divImgSrc4").css("display","block");
						    $("#divImageSrc4").jCarouselLite({
						        btnNext: "#divImageSrc4Next",
						        btnPrev: "#divImageSrc4Prev",
						    	easing:  "backout",
						        speed:   700
						    });
						    $("#divImageSrc4 li").click(function() {
							    $(this).children().each(function () {
									var elment = this.tagName;
									switch (elment) {
									case "IMG" : $("#imgWidget").attr("src", $(this).attr("src"));
												 $("#imgWidget").css("display","block");
												break;
									case "A" : $("#imgWidgetLink").attr("href", $(this).attr("href"));
												break;
									case "SPAN" : $("#imgWidgetCapture").html( $(this).html() );
												break; 
									}
							    });						    	 
						    });
						}
					';
			$css = '/*Module image Div*/
					.moduleImage {
					    position: relative;
					}
					
					/*source image Div*/
					.divImage {
					    position: relative;
					    margin: 0 0 30px 10px;
					    padding: 10px 0 0;
					    clear:both;
					    display: none;
					}
					
					/*source image title*/
					.divImage h6 {
					    margin: 0 0 10px 0px;
					    font-style:normal;
					    font-size:0.8em;
					    font-weight: bold;
					}
					
					/*image widget*/
					.imgWidget {
					    background-color: #DFDFDF;
					    border: 1px solid black;
					    height: 400px;
					    margin: 0 0 20px 80px;
					    width: 400px;
					}
					
					#imgWidget {
					    position: relative;
						margin: 7px;
					    height: 380px;
					    width: 380px;
						padding: 1px;
						background-color: #DFDFDF;
						border:2px solid black;
					    display: none;
					}
					
					/*carrusel buttons*/
					.divImage button {
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
					
					.divImage .link {
					    display:none;
					} 
					
					.divImage .description {
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
	
	
	private function __getValPictureList ()
	{
		return $this->pictureList;
	}

	public function loadPics($keyword,$imageLibrary,$imagePath)
	{
		//print "keyword$keyword<br>";
		$s_keyword = str_replace(' ','',ucwords($keyword));
		$s_keyword = substr ( $fName, 0, 30).'_0';
		$Imagename=$imageLibrary.$s_keyword.".jpg";
		$Imagefile=$imagePath.$s_keyword.".jpg";
		if(!file_exists($Imagefile))
		{
			// TODO - call sx25 to get image
			$Imagename=$imageLibrary."default_0.jpg";
		}
		
		return $Imagename;
	}


	/**
	 * Image from google ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page images to download
	 * 
	 * @return array
	 */
	function getGoogleImageSearch($keyword = '', $per_page = 4)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$SearchEngine = new SearchEngine_Class();

		return $SearchEngine->getGoogleImageSearch('google',$keyword,$per_page);	
	}

	/**
	 * Image from bing ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page images to download
	 * 
	 * @return array
	 */
	function getBingImageSearch($keyword = '', $per_page = 4)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();

		$SearchEngine = new SearchEngine_Class();

		return $SearchEngine->getBingImageSearch('bing',$keyword,$per_page);	
	}

	/**
	 * Image from people.com ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page images to download
	 * 
	 * @return array
	 */
	function getPeopleImage($keyword='',$per_page=6){
		if ( empty($keyword) || !is_string($keyword) ) return array();
		$keyword .= ' site:people.com/people/gallery'; 
		
		$SearchEngine = new SearchEngine_Class();

		return $SearchEngine->getGoogleImageSearch('people',$keyword,$per_page);	
	}
	
	/**
	 * get_photo_from_flickr
	 * get the images from flickr
	 * 
	 * @param unknown_type $method // $mehod = 'flickr.photos.search';
	 * @param unknown_type $vars   // $vars = 'apple';
	 */
	public function getFlickrImage($keyword = '', $per_page = 4, $api_key ='25ca6bd4931af22fba6e741629fe7545')
	{	
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$flickr_result = array();

		$this->_curlObj->createCurl('get','http://api.flickr.com/services/rest/',array('api_key' => $api_key,
																						'content_type' => 7,
																						'per_page' => $per_page,
																						'page' => 1,
																						'tags' => $keyword,
																						'method' => 'flickr.photos.search'
																						));
		$content = $this->_curlObj->__toString();
		
		$xml = @simplexml_load_string($content);
		
		if ($xml == false ) {
			return $flickr_result;
		}
		
		$xml_att = $xml->attributes();
		if ((string)$xml_att['stat'] !== 'ok') {
			return $flickr_result;
		}
		$photos = (array)$xml->photos;
		
		if (empty($photos['photo'])) {
			return $flickr_result;
		}
		
		foreach ($photos['photo'] as $photo) {
			$photo_att = (array)$photo->attributes();
			$photo_info = $photo_att['@attributes'];
			if(!empty($photo_info)) {
				$flickr['content_title'] 	= $photo_info['title'];
				$flickr['content_photo_src']= 'http://farm' . $photo_info['farm'] . '.static.flickr.com/' . $photo_info['server'] .	 '/' . $photo_info['id'] .'_' . $photo_info['secret'] . '_m.jpg';
				$flickr['content_link']   	= 'http://www.flickr.com/photos/' . $photo_info['owner'] . '/' . $photo_info['id'];
				$flickr['user_rest']   		= $this->_get_flickr_user_info_rest('flickr.people.getInfo', $api_key, $photo['owner']);
				$flickr['content_source'] 	= 'flickr';	  
				$flickr['content_keyword']  = $keyword;
			}
			$result[] = $flickr;
		}
		
		if(!empty($result))
		{
			foreach ($result as $value) {
				if (empty($value['user_rest'])) {
					continue;
				}
				$value['content_author'] = $this->_get_flickr_userinfo($value['user_rest']);
				unset($value['user_rest']);
				$flickr_result[] = $value;
			}
		}
		
		return $flickr_result;
	}
	
	/*get the rest of flickr user information */
	private function _get_flickr_user_info_rest($method, $api_key, $owner)
	{
		if (empty($method) || empty($owner)|| empty($api_key)) {
			return array ();;
		}
		
		return array( 'method=' => $method , 'api_key' => $api_key , 'user_id' => $owner);
	}
	
	/*get user name*/
	private function _get_flickr_userinfo($rest)
	{
		if(!is_array($rest) || empty($rest)){
			return false;
		}
		
		$this->_curlObj->createCurl('get','http://api.flickr.com/services/rest/',$rest);
		$content = $this->_curlObj->__toString();
		
		$xml = @simplexml_load_string($content);
		if ($xml == false) {
			return false;
		}
		$xml_att = (array)$xml->attributes();
		if ($xml_att['@attributes']['stat'] != 'ok' ){
			return false;
		}
		$person = (array)$xml->person;
		if (isset($person['username'])) {
			$username = $person['username'];
		} elseif (isset($person['realname'])) {
			$username = $person['realname'];
		}

		return $username;
	}
	
	/**
	 * getLibraryImage
	 * get the images from library
	 * 
	 * @param string $keyword searching keyword
	 * @param int $per_page num of results
	 */
	public function getLibraryImage($keyword = '', $per_page = 4)
	{	
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$config = Config_Class::getInstance();
		$result = array();
		$output = array();	
		$imagesQuery = "SELECT image_library_id, image_library_name, image_library_keyword
						FROM images_library  
						LEFT JOIN images ON image_name = image_library_name 
						WHERE image_library_keyword = '$keyword' AND image_library_approved >= 1 AND image_library_name IS NOT NULL
						GROUP BY image_library_name
						HAVING SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) <= 4 ORDER BY image_library_approved DESC, image_library_name Limit $per_page ";
		$pResults =  $this->_db->select($imagesQuery);
		while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
			$output[] = $row;
		}
		
		if (empty($output)) 
		{
			$kws = explode(' ',$keyword);
			switch (count($kws)) 
			{
				case 1:
						$keyword1 = "$keyword%";
						$keyword2 = "%$keyword";
						break;
				case 2:
						$keyword1 = $kws[0];
						$keyword2 = $kws[1];
						break;
				default :
						array_pop($kws);
						$keyword1 = implode ( ' ', $kws );
						$keyword2 = $keyword1.'%';
						break;
			} 
			$imagesQuery = "SELECT image_library_id, image_library_name, image_library_keyword
							FROM images_library  
							LEFT JOIN images ON image_name = image_library_name 
							WHERE (image_library_keyword like '$keyword1' or image_library_keyword like '$keyword2') AND image_library_approved >= 1 AND image_library_name IS NOT NULL
							GROUP BY image_library_name
							HAVING SUM(CASE IFNULL(image_id,0) WHEN 0 THEN 0 ELSE 1 END) <= 2 ORDER BY image_library_approved DESC, image_library_name Limit $per_page ";
			
			$pResults =  $this->_db->select($imagesQuery);
			while($row =  $this->_db->get_row($pResults, 'MYSQL_ASSOC')){
				$output[] = $row;
			}
		}
		
		if (!empty($output)) 
		{
			foreach ($output as $photo) {
				$libImage['content_title'] 		= $photo['image_library_name'];
				$libImage['content_photo_src']	= $config->imageLibrary.$photo['image_library_name'];
				$libImage['content_link']   	= 'images.php?image_id='.$photo['image_library_id'].'&keywords='.$photo['image_library_keyword'];
				$libImage['content_author']  	= 'Library';
				$libImage['content_keyword']  	= $photo['image_library_keyword'];
				$libImage['content_source'] 	= 'db';
				$result[] = $libImage;
			}
		}
		return $result;
	}

	/**
	 * getLibraryImageBYID
	 * get the images from library
	 * 
	 * @param int $id
	 */
	public function getLibraryImageBYID($id)
	{	
		if ( empty($id) ) return array();
		
		$config = Config_Class::getInstance();
		$result = array();
		$imagesQuery = "SELECT image_library_id, image_library_name, image_library_keyword
						FROM images_library  
						WHERE image_library_id = '$id' ";
		$pResults =  $this->_db->select($imagesQuery);
		$photo =  $this->_db->get_row($pResults, 'MYSQL_ASSOC');
		
		if (!empty($photo)) 
		{
			$libImage['content_title'] 		= $photo['image_library_name'];
			$libImage['content_photo_src']	= $config->imageLibrary.$photo['image_library_name'];
			$libImage['content_link']   	= 'images.php?image_id='.$photo['image_library_id'].'&keywords='.$photo['image_library_keyword'];
			$libImage['content_author']  	= 'Library';
			$libImage['content_keyword']  	= $photo['image_library_keyword'];
			$libImage['content_source'] 	= 'db';
			$result[] = $libImage;
		}
		return $result;
	}
	/**
	 * get Images
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numImages images to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numImages,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		// extra parameters			
		$image_id    = (isset($extraParams['image_id'])&&!empty($extraParams['image_id']))?$extraParams['image_id']:0;
		
		$numImg = round($numImages/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'google': $data = array_merge($data,$this->getGoogleImageSearch($keyword,$plus));
								   break;
					case 'bing':   $data = array_merge($data,$this->getBingImageSearch($keyword,$plus));
								   break;
					case 'people': $data = array_merge($data,$this->getPeopleImage($keyword,$plus));
								   break;
					case 'flickr': $data = array_merge($data,$this->getFlickrImage($keyword,$plus));
								   break;
					case 'db': 	   if (!empty($image_id))
								   		$data = array_merge($data,$this->getLibraryImageBYID($image_id));
								   else 
								   		$data = array_merge($data,$this->getLibraryImage($keyword,$plus));
								   break;
				}
			}
		}
		return $data;
	}

}