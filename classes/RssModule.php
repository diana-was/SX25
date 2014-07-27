<?php
/**	APPLICATION:	SX25
*	DESCRIPTION:	display domain - RssModule_Class read rss from news sources
*	CREATED:		27 October 2010 by George Huynh
*	UPDATED:									
*/

class RssModule_Class extends  Module_Class
{
	protected	$moduleName = 'Rss';
	protected	$sourceList = array('bbc','cnn');
	protected   $rsLibObj;
	
    /**
     * constructor : call parent constructor
     *
     * @param object $db database object
     *
     * @return void
     */
	public function __construct(db_class $db)
	{
		parent::__construct($db);
		$this->rsLibObj = new RssLib_Class;
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
			<!-- RSS Module -->
			<div class="moduleRss">
				<div class="divRss">
				<h2><a href="{RSS_SOURCELINK}" target="_blank">{RSS_SOURCETITLE}</a></h2>
				<ul id="ulArticle{RSS_SOURCE}" >{RSS_LIST}</ul>
				</div>
			</div>';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $settings = json_encode(array());
			$pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1)";
			$this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
	
	
	/**
	 * News from BBC ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getRssBBC($keyword = '', $per_page = 4)
	{
		$url= "http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/england/norfolk/rss.xml";

		return $this->rsLibObj->RSS_display($url, $per_page);
	}

	/**
	 * News from CNN ..
	 * 
	 * @param string $keyword
	 * @param integer $per_page videos to download
	 * 
	 * @return array
	 */
	function getRssCNN($keyword = '', $per_page = 4)
	{
		$url= "http://feeds.smh.com.au/rssheadlines/top.xml";

		return $this->rsLibObj->RSS_display($url, $per_page);;
	}
	
	/**
	 * get RSS
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numRss videos to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numRss,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		$numRssSources = round($numRss/count($sources));
		if ($numRssSources <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numRssSources;
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'bbc': $data['bbc'] = $this->getRssBBC($keyword,$plus);
								break;
					case 'cnn':	$data['cnn'] = $this->getRssCNN($keyword,$plus);
								break;
				}
			}
		}
		
		return $data;
	}

}