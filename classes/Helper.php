<?php
/**	APPLICATION:	SX25
*	FILE:			Helper.php
*	DESCRIPTION:	front end - Helper class - load helper files. Each helper file should be in the helper directory and have only functions 
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

/**
 * 
 * This creates the magic - loading the required helpers
 * @param $file the file name
 */
function loadHelpers($file) {
	require_once $file;
}


class Helper_Class   
{
	static protected $helpers = array();
	private static $_Object;	
	
    /**
     * constructor : set up the static object
     *
     * @return static object
     */
	public function __construct()
	{
		self::$_Object = $this;
		return self::$_Object;
	}

	
    /**
     * registerHelper returns the array of registered helpers
     * 
     * @param string $name the name of the helper. This with load a file with the name $name_helper.php from the helper directory  
     *
     * @return array
     */
	public static function registerHelper($name) {
		$path = defined('HELPER_PATH')?HELPER_PATH:'';
		$fileName = $path.$name.'_Helper.php';
		if (is_file($fileName)) {
			self::$helpers[] = $name;
			call_user_func_array('loadHelpers', array($fileName));
			return true;
		} 
		return false;
	}
	  
    /**
     * getHelpers returns the array of registered helpers
     *
     * @return array
     */
	private function __getValHelpers() {
		return self::$helpers;	
	}
	
    /**
     * getUserFunctions, get the functions defined by the user
     *
     * @return array
     */
	public function getUserFunctions () {
		$functions = get_defined_functions ();
		return	$functions['user'];
	}
	
    /**
     * Magic Call : call the function from the helper file
     *
     * @param string $method 
     * @param array $args 
     *
     * @return mixed
     */
	public function __call($method, $args) {
		$functions = get_defined_functions ( );
		if (in_array(strtolower($method), $functions['user'])) {
	    	return call_user_func_array($method, $args);
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
     * Get the class static object
     *
     * @return self
     */
    public static function getInstance() 
    {
    	if (!isset(self::$_Object)) {
    		return new Helper_Class();
    	}	
    	return self::$_Object;
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