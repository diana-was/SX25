<?php
/**	APPLICATION:	SX25
*	FILE:			APIMethods.php
*	DESCRIPTION:	methods for api
*	CREATED:		20 August 2012 by Diana De vargas
*	UPDATED:									
*/

class APIMethods_Class extends Model_Class
{
	private 	$_db;
	private		$_config;
	private		$_controller;
	private static $_Object; 
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db)
	{
		$this->_controller = Controller_Class::getInstance();
		$this->_config = Config_Class::getInstance();
		$this->_db = $db; 
		self::$_Object = $this;
		return self::$_Object;
	}


    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance(db_class $db) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }

	/*
	 *  Process The API request
	 *  
	 */
    public function processRequest()
    {
		// Get the data from the request 
		$data = new RestRequest_Class();
		$data->processRequest(); 
		//$data->printMe();
		
	    $action = $data->getData('action');
	    $contentType = $data->getData('type');
		// Process de requested function according to the requested method
		switch ($data->getMethod())
		{
			// gets are easy... List
			case 'get':
				switch($action)
				{
					case 'numads':
						$ads = $this->getNumAds($data->getData('keyword'),$data->getData('feed_type'),$error);
						$status = empty($error)?200:204;
						$message['ads'] = $ads;
						$message['msg'] = $error;
						$data->sendResponse($status, $message, $contentType, true);
						break;
					default: 
						$data->sendResponse(405, '', $contentType);
						break;
				}
				break;
			// so are posts..  Create
			case 'post':
				switch($action)
				{
					case 'sitemap':
						$uploaded = $this->createSitemap($data->getData('_UPLOAD_FILES'),$error);
						$status = $uploaded?200:207;
						$message['msg'] = $uploaded?'SiteMap created':$error;
						$message['msg'].= $uploaded?'<br>'.$this->pingSitemap ($this->_controller->server.'/sitemap.xml'):'';
						$data->sendResponse($status, $message, $contentType, true);
						break;
					default: 
						$data->sendResponse(405, '', $contentType);
						break;
				}
				break;
			// Delete
			case 'delete':
				$data->sendResponse(405, '', $contentType);
				break;
			// here's the tricky bit... Update
			case 'put':
				$data->sendResponse(405, '', $contentType);
				break;
			case 'verify':
				$data->sendResponse(405, '', $contentType);
				break;
			// so are posts..  Create
			default: // delete
				$data->sendResponse(405, '', $contentType);
				break;
		}
    	
    }

	/*
	 *  Upload the sitemap file 
	 *  
	 */
    public function createSitemap($files,&$error)
    {
    	$error = '';
    	if (empty($files) || !isset($files['sitemap']))
    	{
    		$error = 'File to uplolad empty or not found';
    		return false;
    	}

		if ($_FILES ['sitemap'] ['error'] == UPLOAD_ERR_OK && 	// checks for errors
			is_uploaded_file ( $_FILES ['sitemap'] ['tmp_name'] ))  // checks that file is uploaded
		{
			if (is_dir($this->_config->logDir))
			{
				$move = move_uploaded_file($_FILES ['sitemap']["tmp_name"], $this->_config->logDir.'sitemap.xml');
				if ($move)
					return true;
				else 
				{
					$error = 'Error moving Sitemap to directory: '.$this->_config->logDir;
					return false;
				}
			}			
			else 
			{
				$error = 'Sitemap directory does not exist: '.$this->_config->logDir;
				return false;
			}
		}
		else
		{
			$error = 'Error uploading sitemap';
			return false;
		}
    }
    
    public function pingSitemap ($pingUrl) 
    {
    	$message = '';
    	$curlObj = new SingleCurl_Class();
		//Ping Google
		$sPingUrl="http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode($pingUrl);
		$curlObj->createCurl('get',$sPingUrl);
		$err = $curlObj->getHttpErr();
		$status = $curlObj->getHttpStatus();
		if (($err != 0) || ($status != 200))
			$message .= 'Google ping fail '.$sPingUrl;
				
		//Ping YAHOO
		/*
		$sPingUrl="http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=" . $this->_config->yahookey . "&url=" . urlencode($pingUrl);
		$curlObj->createCurl('get',$sPingUrl);
		$err = $curlObj->getHttpErr();
		$status = $curlObj->getHttpStatus();
		if (($err != 0) |Yahoo ($status != 200))
			$message .= (!empty($message)?'<br>':'').'Yahoo ping fail '.$sPingUrl;
		*/			
		//Ping Bing
		$sPingUrl="http://www.bing.com/webmaster/ping.aspx?siteMap=" . urlencode($pingUrl);
		$curlObj->createCurl('get',$sPingUrl);
		$err = $curlObj->getHttpErr();
		$status = $curlObj->getHttpStatus();
		if (($err != 0) || ($status != 200))
			$message .= (!empty($message)?'<br>':'').'Bing ping fail '.$sPingUrl;
			
		return $message;
    }
    
	/*
	 *  Get number of Ads for keyword
	 *  
	 */
    public function getNumAds($keyword,$feed_type,&$error)
    {
    	$error = '';
    	if (empty($keyword))
    	{
    		$error = 'Not keyword was received';
    		return false;
    	}
    	if (empty($feed_type))
    	{
    		$error = 'Not feed type was received';
    		return false;
    	}
    	$feed = new feed();
		$_ads = $feed->loadAds($feed_type, 0 , $keyword, '');
		$feedArray = $feed->displayAds($_ads, $feed_type, 0);
		return count($feedArray);
    }
    
    
}