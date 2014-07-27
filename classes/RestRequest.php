<?php
/**	APPLICATION:	PrincetonIT API
*	FILE:			RestRequest.php
*	DESCRIPTION:	admin centre - functions for API RestRequest
*	CREATED:		20 September 2010 by Diana De vargas
*	UPDATED:									
*/

class RestUtils_Class
{
	public function processRequest()
	{

	}

	public static function sendResponse($status = 200, $body = '', $controller='html', $build = false)
	{

	}

    /**
     * static :: Return the message for the required status
     *
     * @param integer $status status code
     *
     * @return integer
     */
	public static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    207 => 'Not Found',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}

    /**
     * Return the implode array into a xml string
     *
     * @param array $arrayRespose array to convert
     *
     * @return string
     */
	public static function xmlResponse($arrayRespose)
	{
		$response = '';
		foreach ($arrayRespose as $key => $val) {
			if (is_array($val)) {
				if (is_int($key)) {
					$response .= '<item>'.self::xmlResponse($val).'</item>';
				} else {
					$response .= '<'.$key.'>'.self::xmlResponse($val).'</'.$key.'>';
				}
			} else {
				if (is_int($key)) {
					$response .= '<item>'.$val.'</item>';
									} else {
					$response .= '<'.$key.'>'.$val.'</'.$key.'>';
				}
			} 
		}
		return $response;
	}
	
    /**
     * Return the implode array into a string in html format
     *
     * @param array $arrayRespose array to convert
     * @param array $level actual deep into the array
     *
     * @return string
     */
	public static function htmlResponse($arrayRespose,$level=0)
	{
		$header = $level + 1;
		$response = '';
		foreach ($arrayRespose as $key => $val) {
			if (is_array($val)) {
				if (is_int($key)) {
					$response .= self::htmlResponse($val,$level+1);
				} else {
					$response .= '<h'.$header.'>'.$key.'</h'.$header.'>';
					$response .= self::htmlResponse($val,$level+1);
				}
			} else {
				if (is_int($key)) {
					$response .= $val;
				} else {
					$response .= '<h'.$header.'>'.$key.'</h'.$header.'>';
					$response .= $val;
				}
			} 
			$response .= '<br />';
		}
		$response .= '<hr />';
		return $response;
	}
													
	
    /**
     * function to parse the http auth header
     *
     * @param array $txt http header to parse
     *
     * @return array
     */
	protected static function http_digest_parse($txt)
	{
	    // protect against missing data
	    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	    $data = array();
	    $keys = implode('|', array_keys($needed_parts));
	
	    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
	
	    foreach ($matches as $m) {
	        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
	        unset($needed_parts[$m[1]]);
	    }
	
	    return $needed_parts ? false : $data;
	}
	
}


class RestRequest_Class extends RestUtils_Class
{
	private $request_vars;
	private $data;
	private $http_accept;
	private $method;

    /**
     * Constructor inic variables with $_SERVER or defaults
     *
     * @return void
     */
	public function __construct()
	{
		$this->request_vars		= array();
		$this->data				= '';
		$slash					= isset($_SERVER['HTTP_ACCEPT'])?strpos($_SERVER['HTTP_ACCEPT'], '/'):0;
		$this->http_accept		= isset($_SERVER['HTTP_ACCEPT'])?substr($_SERVER['HTTP_ACCEPT'],($slash>0?$slash+1:0),strlen($_SERVER['HTTP_ACCEPT'])): 'html';
		$this->method			= 'get';
	}

    /**
     * setData set the variable data with the parameter value
     *
     * @param array $data  
     *
     * @return void
     */
	public function setData($data)
	{
		$this->data = $data;
	}

    /**
     * setMethod set the variable method with the parameter value
     *
     * @param string $method  
     *
     * @return void
     */
	public function setMethod($method)
	{
		$this->method = $method;
	}

    /**
     * setRequestVars set the variable request_vars with the parameter value
     *
     * @param array $request_vars  
     *
     * @return void
     */
	public function setRequestVars($request_vars)
	{
		$this->request_vars = $request_vars;
	}

    /**
     * getData returns the requested data variable according with the method get, post, put or delete
     *
     * @return array
     */
	public function getData($var='')
	{
		$data = array();
		
		switch ($this->getMethod())
		{
			// gets are easy... List
			case 'get':
			 	$data = $this->getRequestVars();
				break;
			// so are posts..  Create
			case 'post':
			 	$data = $this->getRequestVars();
				break;
			// here's the tricky bit... Update
			case 'put':
			 	$data = $this->data;
				break;
			// Delete
			case 'delete':
			 	$data = $this->getRequestVars();
				break;
			case 'verify':
			 	$data = $this->data;
				break;
		}
		if (!empty($var)) {
			if (is_array($data))
				return (isset($data[$var])?$data[$var]:null);
			elseif (is_object($var))
				return (isset($data->$var)?$data->$var:null);
			else 
				return null;
		}
		else
			return $data;
	}

    /**
     * getData returns the value of method
     *
     * @return string
     */
	public function getMethod()
	{
		return $this->method;
	}

    /**
     * getData returns the value of http_accept
     *
     * @return string
     */
	public function getHttpAccept()
	{
		return $this->http_accept;
	}

    /**
     * getData returns the value of request_vars
     *
     * @return array
     */
	public function getRequestVars()
	{
		return $this->request_vars;
	}

    /**
     * Read the request and store the data in the object variables 
     * request_method
     * RequestVars
     * data
     * 
     * @return void
     */
	public function processRequest()
	{
		// get our verb
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		// we'll store our data here
		$data	= array();
		$vars	= array();
		switch ($request_method)
		{
			// gets are easy... List
			case 'get':
				$vars = $_GET;
				break;
			// so are posts..  Create
			case 'post':
				$vars = $_POST;
				break;
			// Delete
			case 'delete':
				$vars = $_REQUEST;
			 	break;
			// here's the tricky bit... Update
			case 'put':
			case 'verify':
			default :
				break;
		}

		$files = array();
        if (!empty($_FILES)) {
        	$files['_UPLOAD_FILES'] = $_FILES;    
        }
		// set the raw data, so we can access it if needed (there may be
		// other pieces to your requests)
		$this->setRequestVars(array_merge($vars,$files));
        
        if (isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] >= 0) {
				// basically, we read a string from PHP's special input location,
				// and then parse it out into an array via parse_str... per the PHP docs:
				// Parses str  as if it were the query string passed via a URL and sets
				// variables in the current scope.
				parse_str(file_get_contents('php://input'), $put_vars);
				$data = $put_vars;
				if(isset($data['data']))
				{
					// translate the JSON to an Object for use however you want
					$data_vars = json_decode($data['data']);
					$data_vars->_UPLOAD_FILES = $files['_UPLOAD_FILES'];
					$this->setData($data_vars);
				} else {
					$this->setData(array_merge($data,$files));
				}
        }
		
        // store the method
		$this->setMethod($request_method);
	}

    /**
     * Send the response to the request
     *
     * @param integer $status response status code  
     * @param array/string $body parameters o variables to send 
     * @param string $contentType format of the response 'xml', 'json', 'html'   
     * @param boolean $build use the body to biuld a response or just display the $body as response 
     *
     * @return void
     */
	public static function sendResponse($status = 200, $body = '', $contentType=null, $build = false)
	{
		if (isset($this) && empty($contentType)) {
			switch ($this->getHttpAccept()) {
				case 'json'	: $content_type = 'application/json';
							break;
				case 'xml'	: $content_type = 'application/xml';
							break;
				default		: $content_type = 'text/html';
							break;
			}
		} else {
			switch ($contentType) {
				case 'js'	: $content_type = 'application/json';
							break;
				case 'xml'	: $content_type = 'application/xml';
							break;
				case 'html'	: $content_type = 'text/html';
							break;
				default		: $accept = explode(',',$_SERVER['HTTP_ACCEPT']);
							  $content_type = isset($_SERVER['HTTP_ACCEPT'])?$accept[0]:'text/html';
							  $content_type = empty($content_type)?'text/html':$content_type;
							break;
			}
		}
		// servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
		$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
		
		$status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if(is_array($body) || !empty($body))
		{
			if ($build && is_array($body)) {
				switch ($content_type) {
					case 'application/json':
								$response = array();
								foreach ($body as $key => $val) {
									$response[$key] = $val;
								} 
								$response['result'] =  array( 'code' => $status,
															  'message' => self::getStatusCodeMessage($status)
															 );
								$body = json_encode($response);
								break;
					case 'application/xml' :
								$body = '<?xml version="1.0" encoding="ISO-8859-1"?>
											<response>'.
												self::xmlResponse($body).
												'<result>
												  <code>'.$status.'</code>
												  <message>'.self::getStatusCodeMessage($status).'</message>
												</result>
											</response>';
								break;
					default		:
								$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
											<html>
												<head>
													<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
													<title>' . $status . ':' . self::getStatusCodeMessage($status) . '</title>
												</head>
												<body>'.
													self::htmlResponse($body).
													'<h1>' . self::getStatusCodeMessage($status) . '</h1>
													<hr />
													<address>' . $signature . '</address>
												</body>
											</html>';
								break;
						
				}
			}
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			switch ($content_type) {
				case 'application/json': $response = array( 'result' =>  array( 'code' => $status,
															'message' => self::getStatusCodeMessage($status),
															'detail' => $message
														)
													);
							$body = json_encode($response);
							break;
				case 'application/xml' :
							$body = '<?xml version="1.0" encoding="ISO-8859-1"?>
										<response>
											<result>
											  <code>'.$status.'</code>
											  <message>'.self::getStatusCodeMessage($status).'</message>
											  <detail>'.$message.'</detail>
											</result>
										</response>';
							break;
				default		:
							$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
										<html>
											<head>
												<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
												<title>' . $status . ':' . self::getStatusCodeMessage($status) . '</title>
											</head>
											<body>
												<h1>' . self::getStatusCodeMessage($status) . '</h1>
												<p>' . $message . '</p>
												<hr />
												<address>' . $signature . '</address>
											</body>
										</html>';
							break;
					
			}
			// this should be templatized in a real-world solution
		}
		// send the body
		echo $body;
	}

    /**
     * checkAuth : check the Authentication sent in the conecction with the parameters
     *
     * @param string $AuthRealm the DIGEST AuthRealm 
     * @param array $users array with users and passwords
     *
     * @return boolean
     */
	public static function checkAuth($AuthRealm,$users=array())
	{
		// figure out if we need to challenge the user
		if(empty($_SERVER['PHP_AUTH_DIGEST']))
		{
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Digest realm="' . $AuthRealm . '",qop="auth",nonce="' . uniqid(rand()) . '",opaque="' . md5($AuthRealm) . '"');
			// show the error if they hit cancel
			die(self::sendResponse(401));
		}
		
		// now, analayze the PHP_AUTH_DIGEST var
		if(!($data = self::http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']]))
		{
			// show the error due to bad auth
			die(self::sendResponse(401));
		}

		// so far, everything's good, let's now check the response a bit more...
		// Generate valid responces
		$A1 = md5($data['username'] . ':' . $AuthRealm . ':' . $users[$data['username']]);
		$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
		$valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
		
		// last check..
		if($data['response'] != $valid_response)
		{
			die(self::sendResponse(401));
		}
		
		return true;
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
?>