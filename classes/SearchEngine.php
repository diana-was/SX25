<?php
/**	APPLICATION:	SX25
*	FILE:			SearchEngine.php
*	DESCRIPTION:	front end - SearchEngine class
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class SearchEngine_Class   
{
	private $_curlObj;			// All private vars should be named $_name
	private $publicVar;			// All vars for public use should be named $name
	private static $_Object;	// Created a stacic object if the object can be shared or called in multiple places and can be unic 
	
    /**
     * constructor : set up the static object
     *
     * @return static object
     */
	public function __construct()
	{
		$this->_curlObj = new SingleCurl_Class();
		self::$_Object = $this;
		return self::$_Object;
	}

    /**
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance() 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class();
    	}	
    	return self::$_Object;
    }

	/**
	 * Web search from google ..
	 */
	public function getGoogleWebSearch($source, $keyword='', $per_page = 4, $api_key ='ABQIAAAA-AcshcNcyntuwOULxcNwkRTmkHv8EVFAxCToSfP2qbF2JkGCshT8Dzl3JPFuQ4AVijNqBpj31BRGDg')
	{	
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$googleData = array();	
		$this->_curlObj->createCurl('get',"http://ajax.googleapis.com/ajax/services/search/web",array('v' => '1.0',
																									'rsz' => intval($per_page),
																									'hl' => 'en',
																									'q' => $keyword,
																									'key' => $api_key));
		$resultJosn = $this->_curlObj->__toString();
		$result = json_decode( $resultJosn);
		
		if ( !empty( $result->responseData->results ) ) {			
			foreach ( $result->responseData->results as $res ) {	
				$googleData[] = array(
					'content_title' =>$res->title,
					'content_main_content' => $res->content,
					'content_link' => $res->url,
				    'content_source' => $source
				);
			}				
		}
		
		return $googleData;
	}
	
	/**
	 * web serach from bing...
	 *
	 */
	public function getBingWebSearch($source, $keyword, $per_page = 5, $api_key ='21A22DC008050C0B514E6119548DC4C54FCEA0F5')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$bingData = array();
		$num = 0;
		
		$this->_curlObj->createCurl('get','http://api.bing.net/xml.aspx',array( "Version" => '2.2', 
																				 "Query" => $keyword,
																				 "AppId" => $api_key,
																				 "Sources" => 'Web',
																				 "Market" => 'en-us',
																				 "Count" => $per_page
																				));
		
		$bingXml = $this->_curlObj->__toString();
		$xmlAction = new XmlAction_Class();
		$bingArray = $xmlAction->parse($bingXml);
		$result = isset($bingArray[0]['children'][1]['children'][2]['children']) ? $bingArray[0]['children'][1]['children'][2]['children'] : array();

		if(!empty($result))
		{
			foreach ($result as $item)
			{
				if ($num == $per_page) break;
			
				if (isset($item['children'][0]['tagData']))
					$data['content_title'] = $item['children'][0]['tagData'];
				if (isset($item['children'][1]['tagData']))
					$data['content_main_content'] = $item['children'][1]['tagData'];
				if (isset($item['children'][2]['tagData']))
					$data['content_link'] = $item['children'][2]['tagData'];					
				if (isset($item['children'][5]['tagData']))
					$data['content_time_start'] = strtotime($item['children'][5]['tagData']);				
				if (isset($item['children'][6]['children'][5]['children'][1]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][1]['children'][1]['tagData'];	
				if (isset($item['children'][6]['children'][1]['children'][2]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][2]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][2]['children'][3]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][3]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][3]['children'][4]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][4]['children'][1]['tagData'];																					
				if (isset($item['children'][6]['children'][4]['children'][5]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][5]['children'][1]['tagData'];		
				if (isset($item['children'][6]['children'][4]['children'][6]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][6]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][4]['children'][7]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][7]['children'][1]['tagData'];
				if (isset($item['children'][6]['children'][4]['children'][8]['tagData']))
					$data['content_link'] = $item['children'][6]['children'][8]['children'][1]['tagData'];
					
				if(!empty($data)) {										
					$data['content_source'] = $source;
					$bingData[] = $data;
					unset($data);
					$num++;
				}				
			}
		}

		return $bingData;
	}
    
	/**
	 * Image from google ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page images to download
	 * 
	 * @return array
	 */
	public function getGoogleImageSearch($source,$keyword = '', $per_page = 4, $api_key = 'ABQIAAAA-AcshcNcyntuwOULxcNwkRTmkHv8EVFAxCToSfP2qbF2JkGCshT8Dzl3JPFuQ4AVijNqBpj31BRGDg')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();

		$googleData = array();	
		$keyword = str_replace(" ", "+", str_replace("-", "+", $keyword));
		
		$this->_curlObj->createCurl('get','http://ajax.googleapis.com/ajax/services/search/images',array("v" => '1.0', 
																										 "rsz" => $per_page,
																										 "hl" => 'en',
																										 "q" => $keyword,
																										 "key" => $api_key
																									));
		//$this->_curlObj->displayResponce();
		$resultJosn=$this->_curlObj->__toString();
		$result = json_decode( $resultJosn);	
		if ( !empty( $result->responseData->results ) ) {			
			foreach ( $result->responseData->results as $res ) {
				$googleData[] = array(
					'content_title' => $res->title,
					//'content_main_content' => $res->content,
					'content_link' =>  $res->originalContextUrl,
					'content_photo_src' =>  $res->tbUrl,					
					'content_source' =>  $source,
				    'content_keyword' => $keyword,
				);
			}	
		}
		
		return $googleData;
	}

	/**
	 * Image from bing ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page images to download
	 * 
	 * @return array
	 */
	public function getBingImageSearch($source, $keyword = '', $per_page = 4)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();

		$bingData = array();	

		$this->_curlObj->createCurl('get','http://www.bing.com/images',array("q" => $keyword." filterui:imagesize-small", "FORM" => "I4IR"));
		$data=$this->_curlObj->__toString();
		//$this->_curlObj->displayResponce();
		
		preg_match_all ('|(span class="md_de")(.*)(/span)|U', $data, $tittles,  PREG_PATTERN_ORDER );
		preg_match_all ('|(span class="ic")(.+)(href=")(.+)(furl=)[^"]+"|U', $data, $urls,  PREG_PATTERN_ORDER );

		// Clean up data
		if (is_array($tittles) && count($tittles) > 0) {
			foreach ($tittles[0] as $key => $title) {
				$title = substr($title, strpos($title, '>')+1);
				$title = html_entity_decode(str_ireplace("</span", "", $title));
				if ($key < $per_page) {
					$bingData[$key]['content_title'] = $title;
					$bingData[$key]['content_source'] =  $source;				
				}
			}
		}

		if (is_array($urls) && count($urls) > 0) {
			foreach ($urls[0] as $key => $url) {
				$url = substr($url, strpos($url, 'href=') + 5);
				$url = html_entity_decode(str_ireplace('"', '', $url));
				$url = urldecode($url);
				if ($key < $per_page) {
					$bingData[$key]['content_link'] = "http://www.bing.com$url";
					$bingData[$key]['content_photo_src'] = substr($url, strpos($url, 'furl=')+5);
					$bingData[$key]['content_keyword'] = $keyword;
				}
			}
		}
		
		return $bingData;
	}
	
	/**
	 * video serach from bing...
	 */
	public function getBingVideo($source, $keyword = '', $per_page = 4, $allow=0, $api_key='21A22DC008050C0B514E6119548DC4C54FCEA0F5')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$bingVideos = array();
		$num = 0;

		$this->_curlObj->createCurl('get','http://api.bing.net/xml.aspx',array( "Version" => '2.2', 
																				 "Query" => $keyword,
																				 "AppId" => $api_key,
																				 "Sources" => 'Video',
																				 "Market" => 'en-us',
																				 "Count"  => $per_page + 1
																				));
		
		$bingXml = $this->_curlObj->__toString();
		$xmlAction = new XmlAction_Class();
		
		$bingArray = $xmlAction->parse($bingXml);
		$result = isset($bingArray[0]['children'][1]['children'][2]['children']) ? $bingArray[0]['children'][1]['children'][2]['children'] : array();
		if(!empty($result))
		{
			foreach ($result as $item)
			{
				if ($num == $per_page) break;
				
				if(isset($item['children'][0]['tagData']))
					$video['content_title'] = $item['children'][0]['tagData'];
				if ($allow = 1){
					if(isset($item['children'][1]['tagData']))
					$video['content_link'] = $item['children'][1]['tagData'];
				}else{
					if(isset($item['children'][4]['tagData']))
					$video['content_link'] = $item['children'][4]['tagData'];
				}	
				if(isset($item['children'][5]['children'][0]['tagData']))
					$video['content_photo_src'] = $item['children'][5]['children'][0]['tagData'];
				if(isset($item['children'][3]['tagData'])){
					$video['content_time_start'] = $item['children'][3]['tagData'];
					$minute = isset($video['content_time'])?floor($video['content_time']/60/1000):0;
					$second = isset($video['content_time'])?fmod($video['content_time']/60/1000,1)*60:0;
					if (strlen($second)== 1){
						$second = "0".$second;
					} 
					$video['content_time_start'] = $minute.":".$second;
					
				}		
				if(!empty($video)) {
					$video['content_source'] =  $source;				
					$bingVideos[] = $video;
					unset($video);
					$num++;
				}					
			}
		}
		
		return $bingVideos;
	}
	
	/**
	 * Question and Answer from Yahoo! Questions...
	 * 
	 */
	public function getYahooQuestAndAnswer($keyword = '', $per_page = 10, $apikey = 'W9CgwBTV34G9RS4cuLFs47CuGLrHrEeY3Son5yosmyOKpZzDCm2oUGLEk2BR2rxh5eo-')
	{
		//question search 
		$this->_curlObj->createCurl('get','http://answers.yahooapis.com/AnswersService/V1/questionSearch',array( "Query" => $keyword,
																												 "AppId" => $api_key,
																												 "results"  => $per_page
																												));
		
		$contents = $this->_curlObj->__toString();
		
		
		$xml_obj = @ simplexml_load_string($contents);
		
		$yahooData = array();
		if ($xml_obj != false) {			
			foreach ($xml_obj as $key => $question) {
				$q_attr = $question->attributes();
				$question_id = (string)$q_attr['id'];
				unset($q_attr);
				if ($key == 'Question') {
					$from_qaa = array();
					$from_qaa['content_title'] = (string)$question->Subject;
					
					if ( strlen((string)$question->ChosenAnswer) > 300 ) {
						$from_qaa['content_main_content'] = substr( trim((string)$question->ChosenAnswer), 0, 300) . '...';
					} else {
						$from_qaa['content_main_content'] = trim((string)$question->ChosenAnswer);
					}
					
					if ( empty($from_qaa['content_main_content']) && (int)$question->NumAnswers != 0 ) {
						$from_qaa['content_main_content'] = $this->_getYahooAnswer($question_id,$apikey);
						
						if ($from_qaa['content_main_content'] == false) {
							$from_qaa['content_main_content'] = '';
						}
					}
					
					$from_qaa['content_link'] = (string)$question->Link;
					$from_qaa['content_photo_src']  = (string)$question->UserPhotoURL;
					$from_qaa['content_author']  = (string)$question->UserNick;
					$from_qaa['content_time_start'] = (string)$question->Timestamp;
					$from_qaa['content_source'] =  'yahoo';				
					$yahooData[] = $from_qaa;
					unset($from_qaa);
				} 
			}
		}
		
		return $yahooData; 
	}
	
	/**
	 * Question and Answer from Yahoo! Answers...
	 * 
	 * the answer of  question, from yahoo getQeustion API
	 * @param $quesiton_id
	 */
	private function _getYahooAnswer($question_id, $apikey = 'W9CgwBTV34G9RS4cuLFs47CuGLrHrEeY3Son5yosmyOKpZzDCm2oUGLEk2BR2rxh5eo-')
	{
		if (empty($question_id) || !isset($question_id)) return false;

		$this->_curlObj->createCurl('get','http://answers.yahooapis.com/AnswersService/V1/getQuestion',array( "question_id" => $question_id,
																											  "AppId" => $api_key
																											));
		
		$content =  $this->_curlObj->__toString();
		$xmlob = simplexml_load_string($content);
		
		if( isset($xmlob->Question->Answers->Answer[0]->Content) ) {
			return (string)$xmlob->Question->Answers->Answer[0]->Content;
		} else {
			return false;
		}	
	}

	/**
	 * get new from yahoo
	 * 
	 * @param str $appid
	 */
	function getYahooNews($keyword, $per_page=5, $api_key = 'Bqgqp_vV34F6WwViDzAtd6FGCryc8XSq6D5FQk2iEqPuoiDx_9mVCV7GbSOP_Igu0g--')
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$keyword = urlencode(trim($keyword));

		$this->_curlObj->createCurl('get','http://boss.yahooapis.com/ysearch/news/v1/'.$keyword,array(  "appid" => $api_key,
																										 "age"  => '5h',
																										 'orderby' => 'date',
																										 'view' => 'english',
																										 'format' => 'xml', 
																										 'count' => $per_page
																										));
		
		$xml_str = $this->_curlObj->__toString();

		$data = array();
		if ($xml_str != false) 
		{
			$xml_obj = @simplexml_load_string($xml_str);
			if(isset($xml_obj->resultset_news)) 
			{
				$temp = array();
				$results = & $xml_obj->resultset_news;
				foreach ($results->children() as $child) 
				{
					if (!empty($child)) {
						if (isset($child->url) && !empty($child->url)) {
							$temp['content_link'] = (string)$child->url;
						}
						if (isset($child->sourceurl) && !empty($child->sourceurl)) {
							$temp['content_url'] = (string)$child->sourceurl;
						}
						
						if (isset($child->title) && !empty($child->title)) {
							$temp['content_title'] = (string)$child->title;
						}
						if (isset($child->abstract) && !empty($child->abstract)) {
							$temp['content_main_content'] = (string)$child->abstract;
						}
						if (isset($child->date) && !empty($child->date) && isset($child->time) && !empty($child->time)) {
							$temp['content_time_start'] = strtotime((string)$child->date.(string)$child->time);
						}
						if (!empty($temp)) {
							$temp['content_source'] =  'yahoo';	
							$data[] = $temp;
							$temp = array();
						}
					}
				}
			}	
		}
		return $data;
	}
	
	/**
     * Magic Get
     *
     * @param string $property Property name
     *
     * @return mixed
     */
    final public function __get($property)
    {
        return $this->__getProperty($property);
    }

    /**
     * Magic Set
     *
     * @param string $property Property name
     * @param mixed $value New value
     *
     * @return self
     */
    final public function __set($property, $value)
    {
        return $this->__setProperty($property, $value);
    }

    /**
     * Magic Isset
     *
     * @param string $property Property name
     *
     * @return boolean
     */
    final public function __isset($property)
    {
       if (isset($this->$property)) {
           return true;
       }
    }

    /**
     * Get Property
     *
     * @param string $property Property name
     *
     * @return mixed
     */
    final private function __getProperty($property)
    {
        $value = null;

        $methodName = '__getVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            $value = call_user_func(array($this, $methodName));
        } else {
        	if (isset($this->$property)) {
        		return $this->$property;
        	}
        }

        return $value;
    }

    /**
     * Set Property
     *
     * @param string $property Property name
     * @param mixed $value Property value
     *
     * @return self
     */
    final private function __setProperty($property, $value)
    {
        $methodName = '__setVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            call_user_func(array($this, $methodName), $value);
        } else {
        	if (isset($this->$property)) {
        		$this->$property = $value;
        	}
        }
            
        return $this;
    }

    /**
     * Display the object 
     *
     * @return void
     */
    public function printMe() {
		echo '<br />';
		echo '<pre>';
		print_r ($this);
		echo '</pre>';
	}
}