<?php
/**	APPLICATION:	SX25
*	FILE:			NewsModule.php
*	DESCRIPTION:	front end - News Module class
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class NewsModule_Class    extends  Module_Class
{
	protected	$moduleName = 'News';
	protected	$sourceList = array('forbes','theonions','yahoo');
	private     $SearchEngine;
	
	
    /**
     * constructor : call parent constructor
     *
     * @param object $db database object
     *
     * @return void
     */
	public function __construct(db_class $db)
	{
		$this->SearchEngine = SearchEngine_Class::getInstance();						
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

	protected function __setDefaultLayout() 
	{
		$layout = '		
			<!-- News Module -->
			<div class="moduleNews">
				<div class="divNews">
				<h2><a href="{NEWS_SOURCELINK}" target="_blank">{NEWS_SOURCETITLE}</a></h2>
				<ul id="ulArticle{NEWS_SOURCE}" >{NEWS_LIST}</ul>
				</div>
			</div>';
		   	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1)";
			$this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
	/**
	 * get news from forbes search
	 */
	public function getForbesNews($keyword , $per_page = 5)
	{
		$keyword = trim($keyword);

		$this->_curlObj->createCurl('get',"http://search.forbes.com/search/find",array('tab' => 'searchtabgeneraldark',
												 										'MT' => $keyword));
		$contents = $this->_curlObj->__toString();
		if( empty($contents)) return array();
		preg_match_all( "/<div class=\"head\">\s+<a href=\"(.*?)\" >(.*)<\/a>\s+<span class=\"type\">\s+\<\!-- Include video, slide or mp3 icon here ForbesResults\.jsp--\>\s+(.*?)<\/span><\/div>\s+<div class=\"date\">(.*?)<\/div>\s+<div class=\"dek\">([\w\W]*?)<\/div>/" , $contents, $matchs );
		if (!$matchs) return array();
		$data = array();
		$count = (count($matchs[1]) < $per_page) ? count($matchs[1]) : $per_page;
		for($i=0; $i < $count; $i++)
		{
			$data[] = array(
						'content_link' => $matchs[1][$i],
						'content_title' => preg_replace('/<span class=\"bold\">(.*?)<\/span>/', '<b>\\1</b>', $matchs[2][$i]).$matchs[3][$i],
						'content_main_content' => $matchs[5][$i],
						'content_time_start' => strtotime($matchs[4][$i]),
				 		'content_source' => 'forbes'	       
			);
		}
		return $data;
	}

	/**
	  * Get Theonions Search News ...
	  *
	  */	
	function getTheonionsNews($keyword, $per_page = 10)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$TheonionsData = array();
		$num = 0;
		$keyword = $keyword;	
		$theonion_url = "http://www.theonion.com";
		
		$this->_curlObj->createCurl('get',"http://www.theonion.com/search/",array('q' => $keyword));
		$html = $this->_curlObj->__toString();
		
		$htmlArray = explode('<ul id="archive_list">', $html);
		$htmlArray = isset($htmlArray[1])?explode('<div id="pagination">', $htmlArray[1]):'';
		$htmlArray = isset($htmlArray[0])?explode('</li>', $htmlArray[0]):'';
		
		if (!empty($htmlArray))
		{
			foreach ($htmlArray as $item)
			{
				if ($num == $per_page) break;
								
				if (strpos($item ,'class="with_image"'))
					$reg = '#<h3><a href="(.*?)">(.*?)</a></h3>.*?\|.*?([\d|.]*? )\|.*? <p class="teaser">\n\s+(.*)\n\s+<i>.*?<img src="(.*)" w.*?/>#is';
				else 
					$reg = '#<h3><a href="(.*)">(.*)</a></h3>.*?\|\s*([\d|.]*? )\| .*?<p class="teaser">\n\s+(.*)<i>.*?</p>#is';
					
				preg_match($reg, $item, $matches);	
				
				if (count($matches) == 5)
				{
					$TheonionsData[] = array(												
						'content_link' => $theonion_url.$matches[1],
						'content_title' => $matches[2],
						'content_time_start' => strtotime($matches[3]),
						'content_main_content' => $matches[4],
						'content_source' => 'theonions'
					);
				}
				elseif (count($matches) == 6)
				{
					$TheonionsData[] = array(												
						'content_link' => $theonion_url.$matches[1],
						'content_title' => $matches[2],
						'content_time_start' => strtotime($matches[3]),
						'content_main_content' => $matches[4],												
						'content_photo_src' => $matches[5],
						'content_source' => 'theonions'
					);
				}
				
				$num++;
			}	
		}
	
		return $TheonionsData;
	}
	
	/**
	 * get new from yahoo
	 * 
	 * @param str $appid
	 */
	public function getYahooNews($keyword, $per_page=5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$SearchEngine = SearchEngine_Class::getInstance(); 
		
		return $SearchEngine->getYahooNews($keyword,$per_page);	
	}
	
	
	
	
	public function getData($keyword,$sources,$numNewss,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		$numImg = round($numNewss/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'forbes': 			$data = array_merge($data,$this->getForbesNews($keyword,$plus));
								   break;
					case 'theonions':   	$data = array_merge($data,$this->getTheonionsNews($keyword,$plus));
								   break;
					case 'yahoo':   	$data = array_merge($data,$this->getYahooNews($keyword,$plus));
								   break;
				}
			}
		}
		return $data;
	}
	
}