<?php
/**	APPLICATION:	SX25
*	FILE:			ForumModule.php
*	DESCRIPTION:	front end - Forum Module
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class ForumModule_Class    extends  Module_Class
{
	protected	$moduleName = 'Forum';
	protected	$sourceList = array('healthboards','amazonaskville','businessadvice','omgili');
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
			<!-- Forum Module -->
			<div class="moduleForum">
				<div class="divForum">
				<h2><a href="{FORUM_SOURCELINK}" target="_blank">{FORUM_SOURCETITLE}</a></h2>
				<ul id="ulArticle{FORUM_SOURCE}" >{FORUM_LIST}</ul>
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
	 *  Articles from healthboards.com by getGoogleWebSearch...
	 */
	public function getHealthBoards($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:healthboards.com/boards/showthread'; 
		
		return $this->SearchEngine->getGoogleWebSearch('healthboards',$keyword,$per_page);	
	}
	
	/**
	 *  Articles from askville.amazon.com by getBingWebSearch...
	 */
	public function getAmazonAskville($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:askville.amazon.com'; 
		
		return $this->SearchEngine->getBingWebSearch('amazonaskville',$keyword,$per_page);		
	}	

	/**
	 * businessadviceforum.com by getBingWebSearch...
	 */
	function getBusinessAdviceForum($keyword, $per_page = 5)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		$keyword .= ' site:businessadviceforum.com'; 
		
		return $this->SearchEngine->getBingWebSearch('businessadvice',$keyword,$per_page);	
	}

	/**
	 * Forums from www.omgili.com...
	 */
	function getOmgiliForum( $keyword = '', $per_page = 10)
	{	
		if ( empty($keyword) || !is_string($keyword) ) return array();
		
		$data = array();	
		$num = 0;
		
		$url = 'http://omgili.com/api.search?q='.urlencode($keyword).'&type=0';
		$result = @simplexml_load_file($url);

		if ( !empty($result) && @$result->ResultsInfo->TotalResults > 0 )
		{	
			foreach ( $result->Results->Item as $item )
			{	
				if ($num == $per_page ) break;
				$data[] = array(
						'content_title' =>  (string) $item->Title,
						'content_main_content' => (string) $item->Snippet,
						'content_link' =>  (string) $item->Link,
						'content_url' =>  (string) str_replace('http://','',$item->DiscussionSource),
						'content_time_start' =>  (string) strtotime($item->DiscussionDate),
				 		'content_source' => 'omgili'	       
				);
				
				$num++;
			}			
		} 
		
		return $data;	
	}
		
	/**
	 * Question and Answer from Yahoo! Questions...
	 * 
	 */
	function getYahooQuestAndAnswer($keyword = '', $per_page = 10)
	{
		if ( empty($keyword) || !is_string($keyword) ) return array();
	
		return $$this->SearchEngine->getYahooQuestAndAnswer($keyword,$per_page);	
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
					case 'healthboards':   	$data = array_merge($data,$this->getHealthBoards($keyword,$plus));
								   break;
					case 'amazonaskville':	$data = array_merge($data,$this->getAmazonAskville($keyword,$plus));
								   break;
					case 'businessadvice':	$data = array_merge($data,$this->getBusinessAdviceForum($keyword,$plus));
								   break;
					case 'omgili':			$data = array_merge($data,$this->getOmgiliForum($keyword,$plus));
								   break;
				}
			}
		}
		return $data;
	}
	
}