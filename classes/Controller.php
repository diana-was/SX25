<?php

/**	APPLICATION:	SX25
*	FILE:			Module.php
*	DESCRIPTION:	admin centre - Class_Controller read the controls from the URL
*	CREATED:		20 September 2010 by Diana De vargas
*	UPDATED:									
*/

class Controller_Class 
{
	private $server;
	private $domain;
	private $root_domain;
	private $module			= array();
	private $path;
	private $page;
	private $query;
	private $system;
	private $address;
	private $request;
	private $server_name;
	private $rootPath;
	private $baseURL;
	private $self;
	private $ori_kwd		= '';
	private $orign_keyword	= '';
	private $keyword		= '';	
	private static $_Controller; 
	
    /**
     * constructor : reads the $_SERVER variables and set up the server http
     *
     * @return void
     */
	public function __construct()
	{
		$this->server = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
				$this->server .= "s";
		}
		$this->server .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$this->server_name = $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		} else {
			$this->server_name = $_SERVER["SERVER_NAME"];
		}
		$this->domain = isset($_SERVER["SERVER_HOST"])?$_SERVER["SERVER_HOST"]:$_SERVER["SERVER_NAME"];
		$this->domain = str_replace('www.','',strtolower($this->domain));
		$dTemp = explode('.',$this->domain);
		if (array_shift ( $dTemp ) == 'ds') 
			$this->root_domain = implode('.',$dTemp);
		else
			$this->root_domain = $this->domain;
		
		$this->server .= $this->server_name;
		$this->system = (isset($_SERVER['SystemRoot']) && stripos($_SERVER['SystemRoot'], 'windows') !== false)?'WINDOWS':'LINUX';
		$this->address = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'';   
		$this->request = array();
		foreach($_REQUEST as $key => $val) {
			$key = strtolower($key);
			$this->request[$key] = urldecode($val);
		}
		
		$this->getController();
		
		$this->ori_kwd 	= isset($this->request['w'])?str_replace("+"," ",$this->request['w']):'';
		if(isset($this->request['keywords']))
		{
			$this->orign_keyword= isset($this->request['k']) ? str_replace("+", " ", str_replace("-", " ", $this->request['k'])) : str_replace("+", " ", str_replace("-", " ", $this->request['keywords']));
			$this->keyword 		= str_replace("+", " ", str_replace("-", " ",$this->request['keywords']));
		}
		elseif(isset($this->request['keyword']))
		{
			$this->orign_keyword= isset($this->request['k']) ? str_replace("+", " ", str_replace("-", " ", $this->request['k'])) : str_replace("+", " ", str_replace("-", " ", $this->request['keyword']));
			$this->keyword 		= str_replace("+", " ", str_replace("-", " ",$this->request['keyword']));
		}
		else
		{
			$this->__keywordThruPath();
		}
		
		$this->self = $_SERVER['PHP_SELF'];
		$this->baseURL = $this->__cleanPath (pathinfo($_SERVER['PHP_SELF'],PATHINFO_DIRNAME).'/');
		$this->rootPath = $this->__cleanPath ($_SERVER['DOCUMENT_ROOT'].pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME)).'/';
		self::$_Controller = $this;
		return self::$_Controller;
	}

	private function __cleanPath ($str) 
	{
		$str = str_replace('\\','/',$str);
		$parts = explode('/',$str);
		$clean = array();
		foreach ($parts as $val) {
			$val = trim($val);
			if (!empty($val)) {
				$clean[] = 	$val;
			}
		}
		if (count($clean) > 0 && $clean[count($clean)-1] == '/') {
			array_pop($clean);
		}
		$str = !empty($clean)?implode('/',$clean):'';
		$str = !preg_match('|^[a-z,A-Z]+:|', $str)?'/'.$str:$str;
		return $str;
	}
	
	private function __keywordThruPath()
	{
			$this->keyword = isset($this->path[0])?urldecode(str_replace("+", " ", str_replace("-", " ", $this->path[0]))):'';
			$this->orign_keyword = isset($this->path[1])?urldecode(str_replace("+", " ", str_replace("-", " ", $this->path[1]))):$this->keyword;
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
       $property = $property;
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
        		$value = $this->$property;
        	}
        }

        return $value;
    }

    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance() 
    {
    	if (!isset(self::$_Controller)) {
    		return new Controller_Class();
    	}	
    	return self::$_Controller;
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
     * getController : get the route from the url to extract the module name and the url parts
     *
     * @return void
     */
    public function getController() 
    {
	        /*** get the route from the url ***/
	        $route = (empty($_SERVER["REQUEST_URI"])) ? '' : $_SERVER["REQUEST_URI"];
	        $this->module = array();
			$this->query =array();
			$this->path = array();
			
	        if (!empty($route))
	        {
	                /*** get the parts of the route ***/
	                $parse = parse_url ( $route );
	                $parts = isset($parse['path'])?explode('/', pathinfo($parse['path'],PATHINFO_DIRNAME)):array();
	                $base  = isset($parse['path'])?str_replace('.'.pathinfo($parse['path'],PATHINFO_EXTENSION),'',pathinfo($parse['path'],PATHINFO_BASENAME)):'';
					// $parts = isset($parse['path'])?array_merge($parts,array($base)):$parts;
	                $query = isset($parse['query'])?$parse['query']:'';
	                parse_str($query,$this->query);
	                
	                foreach ($parts as $key => $val) {
	                	if (empty($val) || ($val == '/') || ($val == '\\')) {
	                		unset($parts[$key]);
	                	}
	                }
	                
	                if (count($parts) == 0) {
	                	$route = 'index';
	                } else {
	                	$this->module = strtoupper(array_shift($parts));
	                	$this->module = explode('&',$this->module);
	                }
	                $this->path = $parts;
	                
                	$self = str_replace('.'.pathinfo($_SERVER['PHP_SELF'],PATHINFO_EXTENSION),'',pathinfo($_SERVER['PHP_SELF'],PATHINFO_BASENAME));
                	if (strcasecmp($base,$self) != 0)
                	{
                		$this->page = strtolower($base);
                	}
                	else 
                	{
                		$this->page = '';
                	}
	        }
	}
	
	/**
	* printMe method display obj 
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