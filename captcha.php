<?php
// continue the session so we can access the session variable 
session_start(); 
if(isset($_REQUEST['action']) && ($_REQUEST['action']=='captcheck' || $_REQUEST['action']=='validate')){
	// Make posted code into upper case, then compare with the stored string 
	if(empty($_GET['code']) || empty($_SESSION['secret_string']) || strtoupper($_GET['code']) != $_SESSION['secret_string']) 
	{
		echo ($_REQUEST['action']=='captcheck')?'0':json_encode(false); // failed 
	} else { 
		echo ($_REQUEST['action']=='captcheck')?'1':json_encode(true); // passed 
	}
}
else
{
	// Decide what characters are allowed in our string // Our captcha will be case-insensitive, and we avoid some // characters like 'O' and 'l' that could confuse users 
	$charlist = '23456789ABCDEFGHJKMNPQRSTVWXYZ'; // Trim string to desired number of characters - 5, say 
	$chars = 4; $i = 0; 
	while ($i < $chars) { 
		$string .= substr($charlist, mt_rand(0, strlen($charlist)-1), 1); 
		$i++; 
	}
	// Create a GD image from our background image file 
	$captcha = imagecreatefrompng('whimsy/captcha.png'); 
	// Set the colour for our text string // This is chosen to be hard for machines to read against the background
	$col = imagecolorallocate($captcha, 240, 200, 240);  
	imagettftext($captcha, 24, 2, 13, 25, $col, 'whimsy/art.ttf', $string); // Write the string on to the image using TTF fonts 

	$_SESSION['secret_string'] = $string; // Store the random string in a session variable 
	header("Content-type: image/png"); // Put out the image to the page 
	imagepng($captcha);
}