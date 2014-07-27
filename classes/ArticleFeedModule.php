<?php
/**	APPLICATION:	SX25
*	FILE:			ModArticleFeedModuleel.php
*	DESCRIPTION:	front end - ArticleFeedModule
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class ArticleFeedModule_Class    extends  Module_Class
{
	protected	$moduleName = 'ArticleFeed';
	protected	$sourceList = array('about','allbusiness','articlebase','hotfrog','yahoobuzz','cnn','digg','helium');
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
			<!-- ArticleFeed Module -->
			<div class="moduleArticleFeed">
				<div class="divArticleFeed">
				<h2><a href="{ARTICLE_SOURCELINK}" target="_blank">{ARTICLE_SOURCETITLE}</a></h2>
				<ul id="ulArticle{ARTICLE_SOURCE}" >{ARTICLE_LIST}</ul>
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
	 *  Articles from about.com by getBingWebSearch...
	 */
	public function getAboutArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:about.com'; 
		
		return $this->SearchEngine->getGoogleWebSearch('about',$keyword,$per_page);	
	}
	
	/**
	 *  Articles from allbusiness.com by getBingWebSearch...
	 */
	private function getAllbusinessArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:allbusiness.com'; 
		
		return $this->SearchEngine->getBingWebSearch('allbusiness',$keyword,$per_page);	
	}
	
	public function getArticlebaseArticles($keywords='',$per_page=5) 
	{
		if (empty($keywords) || !is_string($keywords)) return array();
		/** http://www.articlesbase.com/find-articles.php?q=new+york */
		$artiles_getdata = array();

		$this->_curlObj->createCurl('get','http://www.articlesbase.com/find-articles.php',array('q' => urlencode($keywords)));
		$artiles_data = $this->_curlObj->__toString();
		
		$content = @preg_match_all('|<div\sclass="article_row">\s*<div class="title">\s*<h3><a\stitle=".*?" href="(.*?)">(.*?)</a></h3>\s*</div>\s*<img\ssrc="(.*?)"\swidth=".*?"\sheight=".*?"\salt=".*?"\stitle=".*?"\sclass=".*?"\s/>\s*<p>(.*?)</p>\s*<div\sclass=".*?">|',$artiles_data,$artiles_array);
        if (isset($artiles_array['1'])) $artile['content_link'] = $artiles_array['1'];
        if (isset($artiles_array['2'])) $artile['content_title'] = $artiles_array['2'];
        if (isset($artiles_array['3'])) $artile['content_photo_src'] = $artiles_array['3'];
        if (isset($artiles_array['4'])) $artile['content_main_content'] = $artiles_array['4'];

        $count = count($artiles_array['1']);
        if ($count <= $per_page) {
	         for ($i = 0;$i< $count;$i++) {
				 $artiles_getdata[] = array(
	                    'content_link'	=> $artile['content_link'][$i],
						'content_title' => $artile['content_title'][$i],
						'content_main_content' => $artile['content_main_content'][$i],
						'content_photo_src'	=> $artile['content_photo_src'][$i],             
				 		'content_source' => 'articlebase'	       
				 ); 
			 }
        } else {
	        for ($i = 0;$i<$per_page;$i++) {
				 $artiles_getdata[] = array(
	                  	'content_link'	=> $artile['content_link'][$i],
						'content_title' => $artile['content_title'][$i],
						'content_main_content' => $artile['content_main_content'][$i],
						'content_photo_src'	=> $artile['content_photo_src'][$i],
				 		'content_source' => 'articlebase'	       
						    ); 
					 }
        }				
		return $artiles_getdata;
	}

	/**
	 * Articles from www.hotfrog.com...
	 * @author leenon
	 */
	function getHotfrogArticles($keyword,$per_page = 5) 
	{
		if (empty($keyword) || !is_string($keyword)) return array();
		$hotdata = array();

		/** http://www.hotfrog.com/CompaniesForProductByLoc.aspx?search=KEYWORD */
		$this->_curlObj->createCurl('get',"http://www.hotfrog.com/CompaniesForProductByLoc.aspx",array('search' => urlencode($keyword)));
		$artiles_data = $this->_curlObj->__toString();
		
		$content = preg_match_all('|<h4\sclass=".*?">\s*<a\sid=".*?"\shref="(.*?)">(.*?)<\/a>\s*<\/h4>\s*<p\sid=".*?">(.*?)</p>\s*<p\sid=".*?"\sclass=".*?">(.*?)<\/p>\s*<p\sclass=".*?">\s*(.*?)\s*<\/p>|',$artiles_data,$gethotdata);
		$result = array(
		   'data' => $gethotdata[3],
		   'created' => $gethotdata[4],
		   'content' => $gethotdata[5]
		 );
	       
		$count = (count($result['created']) < $per_page) ? count($result['created']) : $per_page;
		if ($count > 0)
		{
			$string = '';
			for($i=0; $i < $count; $i++)
			{
				if (!empty($result['data'][$i]))          $string .= $result['data'][$i];
				if (!empty($result['created'][$i]))       $string .= $result['created'][$i];
				if (!empty($result['content'][$i]))       $string .= $result['content'][$i];
				$data[]	= $string;
				$string = '';
			}
			for ($j=0; $j<$count; $j++) 
			{
				$hotdata[] = array(
				  'content_link' => $gethotdata[1][$j],
				  'content_title' => $gethotdata[2][$j],
				  'content_main_content' => $data[$j],
				  'content_source' => 'hotfrog'	       
				);
			 }
		} 
		return $hotdata;
	}
	
	/**
	 * Articles from buzz.yahoo.com/...
	 * @author leenon
	 */
	public function getYahooBuzzArticles($keyword='',$per_page=5) {
	    if (empty($keyword) || !is_string($keyword)) return array();
	    /*** http://buzz.yahoo.com/search;_ylt=Aozg_g6Ky1vtZ2ink9U8tH11fNdF?p=news*/

	    $buzz_data = array();
		$this->_curlObj->createCurl('get',"http://fe2.buzz.sp1.yahoo.com/search",array('p' => urlencode($keyword)));
		$buzz_sourse = $this->_curlObj->__toString();
		
	    $content = @preg_match_all('|<a\sclass=".*?"\starget=".*?"\shref="(.*?)".*?>(.*?)</a>\s*<.*?>.*?<.*?>\s*<.*?>\s*<.*?>\s*.*?<p>(.*?)<\/p>\s*<\/dd>\s*<.*?>\s*.*?>(.*?)<\/a>|', $buzz_sourse, $data);
	    if (isset($data['1'])) $artile['content_link'] = $data['1'];
        if (isset($data['2'])) $artile['content_title'] = $data['2'];
        if (isset($data['3'])) $artile['content_time_start'] = $data['3'];
        if (isset($data['4'])) $artile['content_main_content'] = $data['4'];
	    
        $count = (count($data[1]) >= $per_page)?$per_page:count($data[1]);
    	for ($i = 0;$i<$count;$i++){
    	    $buzz_data[] = array(
    	        'content_link' => $artile['content_link'][$i],
    	        'content_title' =>$artile['content_title'][$i],
      	        'content_time_start' =>$artile['content_time_start'][$i],
      	        'content_main_content' =>$artile['content_main_content'][$i],
				'content_source' => 'yahoobuzz'	       
    	    );
      	 }
      	 return $buzz_data;
	}
	
	/**
	 * Articles from digg.com/...
	 * @author leenon
	 */
	public function getDiggArticles($keyword='',$per_page=5) {
	    if (empty($keyword) || !is_string($keyword)) return array();
	      
	    $digg_data = array();
	    /***http://digg.com/search?q=new+york&submit=*/
		$this->_curlObj->createCurl('get',"http://digg.com/search",array('q' => urlencode($keyword), 'submit' => ''));
		$digg_sourse = $this->_curlObj->__toString();

		$content = @preg_match_all('|<a\srel=".*?"\shref="(.*?)"><img\ssrc="(.*?)".*?><\/a>\s*<\/div>\s*<.*?><.*?><h3><a\shref="(.*?)".*?>(.*?)<\/a><\/h3><.*?><.*?><.*?>.*?<\/a><a\srel=".*?"\shref="(.*?)".*?>(.*?)\s*(.*?)<span\sclass=".*?">|',$digg_sourse, $data);
//echo '<pre>'; print_r($data); echo '</pre>';
		$result = array(
	       'content1' => $data[6],
	       'content2' => $data['7']
	    );
	    $count = (count($result['content2']) < $per_page) ? count($result['content2']) : $per_page;
	    if ($count > 0) {
	    	$string = '';
	        for ($i=0;$i<$count;$i++) {
	        	if (!empty($result['content1']))        $string .= $result['content1'][$i];
	            if (!empty($result['content2']))        $string .= $result['content2'][$i];
	            $sourse[] = $string; 
	            $string = '';
	         }
		     
	         for ($j = 0;$j<$count;$j++) {
	             $digg_data[] = array(
	                 'content_link' 	=>'http://digg.com'.$data['1'][$j],
	                 'content_photo_src'=>$data['2'][$j],
	                 'content_title' 	=>$data['4'][$j],
	                 'content_main_content' =>$sourse[$j],
					 'content_source' 	=> 'digg'	       
	              	);
	         }
	    }
	      
		return $digg_data;
	}
	
	/**
	 * Articles from edition.cnn.com...
	 * @author leenon
	 */	
    public function getCnnArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:edition.cnn.com'; 
		
		return $this->SearchEngine->getBingWebSearch('cnn',$keyword,$per_page);	
	}

	/**
	 *  Advice from Lonely Planet by getBingWebSearch...
	 */
	function getHeliumArticles($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:helium.com'; 
		
		return $this->SearchEngine->getBingWebSearch('helium',$keyword,$per_page);	
	}
	
	public function getData($keyword,$sources,$numArticles,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		$numImg = round($numArticles/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'about': 			$data = array_merge($data,$this->getAboutArticles($keyword,$plus));
								   break;
					case 'allbusiness':   	$data = array_merge($data,$this->getAllbusinessArticles($keyword,$plus));
								   break;
					case 'articlebase':   	$data = array_merge($data,$this->getArticlebaseArticles($keyword,$plus));
								   break;
					case 'hotfrog':   	$data = array_merge($data,$this->getHotfrogArticles($keyword,$plus));
								   break;
					case 'yahoobuzz':   	$data = array_merge($data,$this->getYahooBuzzArticles($keyword,$plus));
								   break;
					case 'digg':   	$data = array_merge($data,$this->getDiggArticles($keyword,$plus));
								   break;
					case 'cnn':   	$data = array_merge($data,$this->getCnnArticles($keyword,$plus));
								   break;
					case 'helium':   	$data = array_merge($data,$this->getHeliumArticles($keyword,$plus));
								   break;
				}
			}
		}
		return $data;
	}
	
}