<?php
/**	APPLICATION:	SX25
*	FILE:			topicModule Class
*	DESCRIPTION:	generate relative topic list which usually display on side bar.
*	CREATED:		04 Jan 2011 Gordon Ye
*	UPDATED:
*   USAGE:          generate relative topic.
*/

class TopicModule_Class extends  Module_Class
{
	protected	$moduleName = 'Topic';
	protected	$sourceList = array();
	protected	$settings   = array();
	
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
			<!-- Related Topic Module -->
			<h2>Related Searches</h2>
				<ul class="popular">
					<li><a href="/{MODULES}/result.php?Keywords={RELATED1}">{RELATED1}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED2}">{RELATED2}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED3}">{RELATED3}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED4}">{RELATED4}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED5}">{RELATED5}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED6}">{RELATED6}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED7}">{RELATED7}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED8}">{RELATED8}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED9}">{RELATED9}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED10}">{RELATED10}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED11}">{RELATED11}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED12}">{RELATED12}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED13}">{RELATED13}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED14}">{RELATED14}</a></li>
					<li><a href="/{MODULES}/result.php?Keywords={RELATED15}">{RELATED15}</a></li>
				</ul>
			';
	    	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['settings']	= array('perPage' => 15);
	        $settings = json_encode($this->layout['settings']);
			$pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1)";
			$this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
	
		
	/**
	 * get Topics
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numVideos videos to download 
	 * 
	 * @return array
	 */
	public function getData($keyword, $feed_type, $amount, $extraParams = array())
	{
		$feed_id = isset($extraParams['feed_id'])?$extraParams['feed_id']:'';
		
		$feed = new feed();
		$_relates = $feed->loadRelates($feed_type, $feed_id, $keyword);
		$relateString = $feed->displayRelates($_relates,$feed_type);	
		return $relateString;
	}
}

?>