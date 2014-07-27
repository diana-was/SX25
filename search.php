<?php
session_cache_expire(60);
session_start();
if(isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) 
{
	session_destroy();
	session_regenerate_id();
}
$_SESSION['timeout_idle'] = time() + session_cache_expire();

$domain 	= isset($_SERVER["SERVER_HOST"])?$_SERVER["SERVER_HOST"]:$_SERVER["SERVER_NAME"];
$domain 	= str_replace('www.','',strtolower($domain));
$ref 		= isset($_SERVER['HTTP_REFERER'])?parse_url ($_SERVER['HTTP_REFERER']):array();
$hostName 	= isset($ref['host'])?str_replace('www.','',strtolower($ref['host'])):'';
$NoReferer 	= (isset($_SERVER['HTTP_REFERER']) && $domain != $hostName); //$_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] || ;
If (!isset($_SESSION['timeout_idle']) || !isset($_REQUEST['_site']) || $NoReferer )
{
	$query = isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:http_build_query($_REQUEST, '');
	header('Location: result.php'.(empty($query)?'':'?'.$query));
	exit;
}

if (isset($_REQUEST['pass']) && ($_REQUEST['pass'] == 2)) :
	$url = urldecode($_POST['_site']).'/';
	unset($_POST['_site']);
	if (isset($_POST['_app']))
	{
		$url .= urldecode($_POST['_app']);
		unset($_POST['_app']);
	}
	$query = http_build_query($_POST, '');
	header('Location: '.$url.(empty($query)?'':'?'.$query));
	exit;
else :
	$query = isset($ref['query'])?$ref['query']:'';
	$pass  = !isset($_REQUEST['pass'])?1:2;
?>
<!DOCTYPE meta PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Search</title>
</head>
<BODY onLoad="document.myform.submit()">
<FORM NAME="myform" ACTION="search.php?pass=<?php echo "$pass".(empty($query)?'':'&'.$query); ?>" METHOD="post">
<?php
    $vars = !isset($_REQUEST['pass'])?$_REQUEST:$_POST;
    foreach ($vars as $k => $v)
    {
   		echo '<input type="hidden" name="'.$k.'" value="'.$v.'"/>';
    }
?>
</FORM>
</html>
<?php endif; ?>