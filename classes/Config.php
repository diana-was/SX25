<?php
/**	APPLICATION:	SX25
*	FILE:			Config.php
*	DESCRIPTION:	admin centre - Class_Config read the confif file
*	CREATED:		20 September 2010 by Diana De vargas
*	UPDATED:									
*/

class Config_Class
{
	private $_config;
	private $properties = array();
	private static $_Config;
	
    /**
     * constructor : reads the config file an set up the variables
     *
     * @param string $file file name
     * @param string $enviroment name of enviroment to read variables
     *
     * @return void
     */
	public function __construct($file,$enviroment)
	{
		if  (is_file($file)) {
			$this->_config = parse_ini_file($file,1);
			$this->properties = $this->__getVariables($this->_config,$enviroment,0);
		}
		self::$_Config = $this;
		return self::$_Config;
	}

    /**
     * reads the array of variables and return them in an array
     *
     * @param array $config variables in the config file
     * @param array $enviroment name of enviroment to read variables
     * @param integer $level level in the array
     *
     * @return array
     */
	private function __getVariables($config,$enviroment,$level) {
		$controller = Controller_Class::getInstance();
		$properties = array();
		$env = false;
		$search = array('{rootPath}','{HOME}','{domain}','{server_name}','{root_domain}');
		$replace = array($controller->rootPath,$controller->baseURL,$controller->domain,$controller->server_name,$controller->root_domain);
		foreach($config as $var => $value)
		{
			if (is_array($value)) {
				if ($level == 0) {
				    if ($var == $enviroment) {
						$properties = array_merge($properties,$this->__getVariables($value,$enviroment,$level+1));
						$env = true;
				    } elseif (!$env) {
						$properties[$var] = $this->replaceArray($search, $replace, $value);
				    }
				} else {
					$properties[$var] = $this->replaceArray($search, $replace, $value);
				}
			} else {
				$properties[$var] = $this->replaceArray($search, $replace, $value);
			}
		}
		return $properties;
	}
	
	
	public function replaceArray ($search,$replace,$subject)
	{
		if (is_array($subject)) {
			foreach ($subject as $key => $data) {
				$subject[$key] =  $this->replaceArray($search, $replace, $data);
			}
		} else {
			$subject = str_ireplace($search, $replace, $subject);
		}
		return $subject;
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
       if (isset($this->properties[$property])) {
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
        	if (isset($this->properties[$property])) {
        		return $this->properties[$property];
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
        	if (isset($this->properties[$property])) {
        		$this->properties[$property] = $value;
        	}
        }
            
        return $this;
    }

    
    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance($file='',$enviroment='') 
    {
    	if (!isset(self::$_Config)) {
    		return new Config_Class($file,$enviroment);
    	}	
    	return self::$_Config;
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