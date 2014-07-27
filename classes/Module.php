<?php
/**	APPLICATION:	SX25
*	FILE:			Module.php
*	DESCRIPTION:	display domain - Module_Class base class
*	CREATED:		15 October 2010 by Diana De vargas
*	UPDATED:									
*/

abstract class Module_Class
{
	protected	$moduleName = '';
	protected	$sourceList = array();
	protected	$moduleID;
	protected	$settings = array();
	protected	$layout = array();
	protected	$oLayouts = array();
	protected	$channels = array();
	protected 	$_db;
	protected	$_curlObj;
	private static $_Module = array(); 
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db)
	{
		$this->_curlObj = new SingleCurl_Class();
		$this->_db = $db;
		if (!$this->__getModuleData()) {
			$this->__setModuleData();
			$this->__setDefaultLayout();
		} else {
			if (!$this->__getLayoutData()) {
				$this->__setDefaultLayout();
			}
			$this->__getChannelsData();
		}
		self::$_Module[$this->moduleName] = $this;
		return self::$_Module[$this->moduleName];
	}

    /**
     * Get the module static object
     *
     * @return self
     */
    protected static function getInstance(db_class $db,$className) 
    {
    	$name = str_replace('Module_Class','',$className);
    	if (!isset(self::$_Module[$name])) {
    		return new $className($db);
    	}	
    	return self::$_Module[$name];
    }
	
    /**
     * Get Module Data from database
     *
     * @return boolean data found
     */
    final private function __getModuleData()
    {
 		$pRow = array();
		$pQuery = "SELECT * FROM modules WHERE module_name='".$this->moduleName."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			$this->moduleID = isset($pRow['module_id'])?$pRow['module_id']:null;
			$this->settings = isset($pRow['module_settings'])?json_decode($pRow['module_settings'],true):array();

			return true;
		} else {
			return false;
		}
    }

    /**
     * Get Layout Data from database
     *
     * @return boolean data found
     */
    protected function __getLayoutData()
    {
    	$this->layout = array();
    	$this->oLayouts = array();
		$pQuery = "SELECT * FROM modulelayouts WHERE modulelayout_module_id = ".$this->moduleID;
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			$id 					= isset($pRow['modulelayout_id'])?$pRow['modulelayout_id']:count($this->oLayouts);
			$layout					= array();
	    	$layout['id'] 			= isset($pRow['modulelayout_id'])?$pRow['modulelayout_id']:null;
			$layout['name'] 		= isset($pRow['modulelayout_name'])?$pRow['modulelayout_name']:null;
	        $layout['layout'] 		= isset($pRow['modulelayout'])?$this->__decodeTags($pRow['modulelayout']):null;
	        $layout['settings']		= isset($pRow['modulelayout_settings'])?json_decode($pRow['modulelayout_settings'],true):array();
	        $layout['layout_js']	= isset($pRow['modulelayout_js'])?$this->__decodeTags($pRow['modulelayout_js']):null;
	        $layout['layout_css'] 	= isset($pRow['modulelayout_css'])?$this->__decodeTags($pRow['modulelayout_css']):null;
	        if (isset($pRow['modulelayout_default']) && ($pRow['modulelayout_default'] == 1))
	        	$this->layout = $layout;
	        else 
	        	$this->oLayouts[$id] = $layout;
		}
		if (empty($this->layout))
			$this->layout = array_shift($this->oLayouts);
		return (!empty($this->layout));
    }

    /**
     * Set Module Data to database
     *
     * @return void
     */
    protected function __setModuleData()
    {
    	$settings = empty($this->settings)?json_encode(array('sources' => $this->sourceList)):json_encode($this->settings);
		$pQuery = "INSERT INTO modules (`module_name`,`module_settings`) values ('".$this->moduleName."','".$settings."')";
		$this->moduleID = $this->_db->insert_sql($pQuery);
    }

    /**
     * Set Module Layout Data to database
     *
     * @return void
     */
    protected function __setDefaultLayout()
    {
    	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
        $this->layout['layout'] 	= '<div class="module'.str_replace(' ','_',$this->moduleName).'"></div>';
        $this->layout['settings']	= array();
        $settings = json_encode(array());
		$pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`) values 
				  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags('<div class="module'.str_replace(' ','_',$this->moduleName).'"></div>')."','".$settings."',1)";
		$this->layout['id'] = $this->_db->insert_sql($pQuery);
    }

    /**
     * Get Channels Data from database
     *
     * @return boolean data found
     */
    protected function __getChannelsData()
    {
 		$pRow = array();
		$pQuery = "SELECT * FROM channels WHERE channel_module_id = ".$this->moduleID;
		$pResults = $this->_db->select($pQuery);
		while($pRow=$this->_db->get_row($pResults, 'MYSQL_ASSOC')) 
		{
			if (isset($pRow['channel_source'])) {
				$name = strtolower($pRow['channel_source']);
	    		$this->channels[$name] = $pRow;
			}
		}
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
    final protected function __getProperty($property)
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
    final protected function __setProperty($property, $value)
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
     * Get Layout Settings
     *
     * @return value
     */
    final public function getLayoutSettings($moduleList=array(),$page=null,$key=null,$default=null)
    {
   	
    	if (empty($moduleList) || empty($page) || !is_array($moduleList) || empty($moduleList[$page]))
    	{
    		$moduleList = array();
    		$id = $this->layout['id'];
    	}
    	else 
    	{
    		$moduleList = $moduleList[$page];
    		$id = isset($moduleList[$this->moduleID])?$moduleList[$this->moduleID]:$this->layout['id'];
	   	}
    	
    	
    	if (empty($key))
    	{
    		if (!empty($id) && isset($this->oLayouts[$id]))
    			return $this->oLayouts[$id]['settings'];
    		else
    			return $this->layout['settings'];
    	}	
        $value = $default;
    	if (!empty($id) && isset($this->oLayouts[$id]))
           	$value = isset($this->oLayouts[$id]['settings'][$key])?$this->oLayouts[$id]['settings'][$key]:$default;
        else
    		$value = isset($this->layout['settings'][$key])?$this->layout['settings'][$key]:$default;

        return $value;
    }
	
	final public function getModuleLayoutID($moduleList=array(),$page=null)
    {
    	if (empty($moduleList) || empty($page) || !isset($moduleList[$page]))
    		return $this->layout['id'];
    	
    	$moduleList = $moduleList[$page];
    	$id = !empty($moduleList[$this->moduleID])?$moduleList[$this->moduleID]:$this->layout['id'];	   
    	return $id;
    }

    /**
     * Get Layout 
     *
     * @return value
     */
    final public function getLayout($moduleList=array(),$page=null)
    {
    	if (empty($moduleList) || empty($page) || !isset($moduleList[$page]))
    		return $this->layout;
    		
    	$moduleList = $moduleList[$page];
		$id = isset($moduleList[$this->moduleID])?$moduleList[$this->moduleID]:$this->layout['id'];
		if (!empty($id) && isset($this->oLayouts[$id]))
			return $this->oLayouts[$id];
		else 
			return $this->layout;
    }

    /**
     * Get LayoutData 
     *
     * @return value
     */
    final public function getLayoutData($key,$moduleList=array(),$page=null)
    {
    	if (empty($key))
    		return null;
    		
    	if (empty($moduleList) || empty($page) || !isset($moduleList[$page]))
    		return isset($this->layout[$key])?$this->layout[$key]:null;
    		
    	$moduleList = $moduleList[$page];
		$id = isset($moduleList[$this->moduleID])?$moduleList[$this->moduleID]:$this->layout['id'];
		if (!empty($id) && isset($this->oLayouts[$id]))
			return isset($this->oLayouts[$id][$key])?$this->oLayouts[$id][$key]:null;
		else 
    		return isset($this->layout[$key])?$this->layout[$key]:null;
	}
    
    /**
     * Get LayoutData 
     *
     * @return value
     */
    final public function getLayoutDataByID($key,$id)
    {
    	if (empty($key) || empty($id))
    		return null;
    		
   		return isset($this->oLayouts[$id][$key])?$this->oLayouts[$id][$key]:null;
	}
	
	abstract public function getData($keyword,$sources,$numImages,$extraParams = array());
 
	protected function __decodeTags($resultBase)
	{
		$resultBase = str_replace('&lt;', '<', $resultBase);
		$resultBase = str_replace('&gt;', '>', $resultBase);
		return $resultBase;
	}

	protected function __encodeTags($resultBase)
	{
		$resultBase = str_replace('<', '&lt;', $resultBase);
		$resultBase = str_replace('>', '&gt;', $resultBase);
		return $resultBase;
	}
	
	final public static function modulesFromTags($data,$search='MODULE',$clean=array('{','_MODULE','}')){
		$tags = array();
		$modules = array();
		preg_match_all ('/({)[^({|})]+}/U', $data, $tags,  PREG_PATTERN_ORDER );
		if (is_array($tags) && isset($tags[0])) {
			foreach ($tags[0] as $tag) {
				if (stripos($tag, $search) !== false)	{
					$modules[] = str_replace($clean, '', $tag);
				}	
			}
		}
		return array_unique($modules);
	}
	
	final public static function cssModulesFromTags($data,$search='_MODULE',$clean=array('{','_MODULE','}')){
		$tags = array();
		$modules = array();
		preg_match_all ('/({)[^({|})]+}/U', $data, $tags,  PREG_PATTERN_ORDER );
		if (is_array($tags) && isset($tags[0])) {
			foreach ($tags[0] as $tag) {
				if (stripos($tag, $search) !== false)	{
					$modules[] = str_replace($clean, '', $tag);
				}	
			}
		}
		return array_unique($modules);
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