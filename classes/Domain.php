<?php
/**	APPLICATION:	SX25
*	FILE:			Domain.php
*	DESCRIPTION:	display domain data from database
*	CREATED:		20 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Domain_Class
{
	private 	$_db;
	private		$_domain_id;
	private		$_domain_url;
	private		$_layout;
	private 	$_isMobile;
	private		$settings = array();
	private		$error = false;
	private static $_Domain; 
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db,$domain_url)
	{
		// Mobile info
		//Available methods are isAndroid(), isAndroidtablet(), isIphone(), isIpad(), isBlackberry(), isBlackberrytablet(), isPalm(), isWindowsphone(), isWindows(), isGeneric(). 
		//Alternatively, if you are only interested in checking to see if the user is using a mobile device, without caring for specific platform.
		$mobileObj = Mobile_Class::getInstance();
		$this->_isMobile = $mobileObj->isMobile();
		$this->_db = $db; 
		$this->_domain_url = $domain_url; 
		$this->getDomain();
		self::$_Domain = $this;
		return self::$_Domain;
	}


    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance(db_class $db=null,$domain_url='') 
    {
    	if (!isset(self::$_Domain)) {
    		return new Domain_Class($db,$domain_url);
    	}	
    	return self::$_Domain;
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
       if (isset($this->settings[$property])) {
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
    final protected function __getProperty($property)
    {
        $value = null;

        $methodName = '__getVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            $value = call_user_func(array($this, $methodName));
        } else {
        	if (isset($this->settings[$property])) {
        		return $this->settings[$property];
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
    final protected function __setProperty($property, $value)
    {
        $methodName = '__setVal' . ucwords($property);
        if(method_exists($this, $methodName)) {
            call_user_func(array($this, $methodName), $value);
        } else {
        	if (isset($this->settings[$property])) {
        		$this->settings[$property] = $value;
        	}
        }
            
        return $this;
    }
    
    /**
     * Get Settings value
     *
     * @return array settings
     */
    private function __getValSettings(){
    	return 	$this->settings;
    }

    /**
     * Set Settings value
     *
     * @param $value settings
     */
    private function __setValSettings($value){
    	$this->settings = $value;
    }
    
    private function __setValLayout_landing($value)
    {
    	$this->getLayout ();
    	$this->_layout['layout_landing'] = trim($value);
    }

    private function __setValLayout_result($value)
    {
    	$this->getLayout ();
    	$this->_layout['layout_result'] = trim($value);
    }
    
    /**
     * Get Domain data from database set up $error as true if domain does not exits
     *
     */
    private function getDomain() {

 		$pRow = array();
 		$layout = $this->_isMobile?'mobile.*':'layouts.*';
 		$mobile_layout = $this->_isMobile?'left join layouts as mobile on mobile.layout_id = ifnull(layouts.layout_id_mobile,layouts.layout_id) ':'';
		$pQuery = "SELECT domains.*, $layout FROM domains 
				   left join layouts on layouts.layout_id = domains.domain_layout_id $mobile_layout
				   WHERE domains.domain_url = '".$this->_domain_url."' LIMIT 1";
		
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			foreach($pRow as $var => $value) {
				if (strpos($var, 'layout') === false || strpos($var, 'layout') > 0)
					$this->settings[$var] = $value;
				else 
					$this->_layout[$var] = $value;
			}
			if (!isset($this->settings['domain_layout_id']) || empty($this->settings['domain_layout_id'])) {
				$this->settings['domain_layout_id'] = 1;
			}
			if (isset($this->settings['domain_id'])) {
				$this->_domain_id = $this->settings['domain_id'];
			}
			if (isset($this->_layout['layout_modules'])) {
				$this->_layout['layout_modules'] = $this->toArray(json_decode($this->_layout['layout_modules']));
			}
			//echo '<pre>'; print_r($this->_layout['layout_modules']); echo '</pre>';
		} else {
			$this->error = true;
		}
 	}
 	
 	private function toArray($set)
 	{
 		if (!is_object($set))
 			return $set;
 			
 		$array = array();
		foreach($set as $member => $data)
		{
			$array[$member] = $this->toArray($data);
		}
		return $array;
 	}
 	
    /**
     * Get Menues from database
     *
     * @return string menus separated by :
     */
 	public function getAllMenus ($keyword) {
 		$mRow = array();
 		$linkMenu = array();
 		$displayMenu = array();
 		$articleMenu = array();
 		$keywordMenu = array();
 		
		$mQuery = "SELECT * FROM menus WHERE menu_domain_id='".$this->_domain_id."' ORDER BY menu_id ASC";
			
		$mResults = $this->_db->select($mQuery);
		while($mRow=$this->_db->get_row($mResults, 'MYSQL_ASSOC')) 
		{
			$displayMenu[] 	= trim($mRow['menu_name_display']);
			if ($mRow['menu_article'] == 1)
			{
				$articleMenu[] = trim($mRow['menu_name_display']);
				$keywordMenu[] = trim($mRow['menu_name_display']);
				$linkMenu[]    = (trim($mRow['menu_name_display']) != trim($mRow['menu_name']))?trim($mRow['menu_name']):'article';
			}
			else
			{
				$keywordMenu[] = $keyword;
				$linkMenu[]    = trim($mRow['menu_name']);
			}
		}
		
		return array('page_menus_link' => $linkMenu, 'page_menus_display' => $displayMenu, 'article_menus' => $articleMenu, 'page_menus_keyword' => $keywordMenu);
 	}

    /**
     * Get Layout from database
     *
     * @return array layout data for the domain
     */
 	public function getLayout () {
 		
 		if (!isset($this->_layout) || empty($this->_layout))
 		{
	 		$layout = $this->_isMobile?'mobile.*':'layouts.*';
	 		$mobile_layout = $this->_isMobile?'left join layouts as mobile on mobile.layout_id = ifnull(layouts.layout_id_mobile,layouts.layout_id) ':'';
 			$layoutQuery = "SELECT $layout FROM layouts $mobile_layout WHERE layouts.layout_id='".$this->settings['domain_layout_id']."' LIMIT 1";
	
 			$layoutResults = $this->_db->select($layoutQuery);
			if(!$this->_layout=$this->_db->get_row($layoutResults, 'MYSQL_ASSOC')) {
				$this->_layout = array();
			}
 			if (isset($this->_layout['layout_modules'])) {
				$this->_layout['layout_modules'] = $this->toArray(json_decode($this->_layout['layout_modules']));
			}
 		}
		return $this->_layout;
 	}
 	
    /**
     * Get Sponsor Domains from database
     *
     * @return array data for the domain
     */
 	public function getSponsorDomains ($keyword) {
 		
		$dsQuery = "SELECT * FROM sponsored_domain WHERE adgroupName='".urldecode($keyword)."' AND masking='".$this->settings['domain_url']."'  LIMIT 1";
		$dsResults = $this->_db->select($dsQuery);
 				
		if(!$dsRow=$this->_db->get_row($dsResults, 'MYSQL_ASSOC')) {
			$dsRow = array();
		}
		return $dsRow;
 	}

    /**
     * Get Related Domains
     *
     * @return string menus separated by ,
     */
 	public function getRelatedDomains ($keyword,$MaxCount=1) {
 		$mRow = array();
 		$domains = '';
 		
		$mQuery = "SELECT * FROM menus WHERE domains WHERE `domain_keyword` LIKE '%".$keyword."%' ORDER BY domain_id DESC LIMIT $MaxCount ";
			
		$mResults = $this->_db->select($mQuery);
		while($mRow=$this->_db->get_row($mResults, 'MYSQL_ASSOC')) 
		{
			$domains[] = $mRow;
		}
		
		if (is_array($domains) && (count($domains) == 1) && ($MaxCount == 1)) {
			$domains = $domains[0];
		}
		
		return $domains;
 	}
 	
    /**
     * Get the mapping keyword 
     *
     * @return string mapping keyword
     */
 	public function getMappingKeyword ($keyword) {
 		$keyword = trim($keyword);
		$mQuery = "select mapping_keyword_mapping from mapping_keyword where domain_id = '".$this->_domain_id."' AND mapping_keyword_original='$keyword' order by mapping_keyword_id DESC limit 1";
		$mk = $this->_db->select_one($mQuery);
		return ($mk?$mk:'');
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