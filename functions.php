<?php
include_once("config.php");

$action = isset($_REQUEST['action'])?strtolower($_REQUEST['action']):'';

switch ($action) {
	case 	'allposts' :
				  $articleObj = ArticleModule_Class::getInstance($db);			
				  $array = $articleObj->getArticlesByDomain($domain_id,'%',0);
				  if(is_array($array)){
					foreach($array as $m) {
					  $articles[$m['article_id']] = array('i' => $m['article_id'],'t' => $m['article_title'],'s' => $m['article_summary'],'k' => $m['article_keyword'], 'a' => $m['article_author'],'type' => $m['article_type'],'d' => $m['article_update_date']);
					}
				  }
				  $imgObj = ImageModule_Class::getInstance($db);	
				  $imgObj->getDomainImages($domain_id, $config->imageLibrary);
				  $array2 = $imgObj->pictureList;
				  if(is_array($array2)){
					  foreach($array2 as $m) {
					  	$images[$m['image_id']] = array('i' => $m['image_id'],'k' => $m['image_keyword'],'n' => $m['image_name'],'l' => $m['image_location']);
					  }
				  }
				  $r = array("articles" => $articles, "images" => $images);
				  echo json_encode($r);
			break;
			
	case 	'allposts2' :
				$articleObj = ArticleModule_Class::getInstance($db);			
				$m = $articleObj->getOneArticlesByDomain($domain_id,$_REQUEST['keyword']);
				if(is_array($m)){
					$articles[0] = array('i' => $m['article_id'],'t' => $m['article_title'],'k' => strtolower($m['article_keyword']),'s' => $m['article_summary']);
				}
				$array = $articleObj->getArticlesByDomain($domain_id,'%',10);
				if(is_array($array)){
					foreach($array as $m) {
				    	$articles[$m['article_id']] = array('i' => $m['article_id'],'t' => $m['article_title'],'k' => strtolower($m['article_keyword']),'s' => $m['article_summary']);
					}
				}
				
				$imgObj = ImageModule_Class::getInstance($db);	
				$imgObj->getDomainImages($domain_id, $config->imageLibrary);
				$array2 = $imgObj->pictureList;
				if(is_array($array2)){
					  foreach($array2 as $m) {
					  	$images[$m['image_id']] = array('i' => $m['image_id'],'k' => $m['image_keyword'],'n' => $m['image_name'],'l' => $m['image_location']);
					  }
				}
			    $r = array("articles" => $articles, "images" => $images);
			    echo json_encode($r);
			break;
			
	case 	'allcomments' :
				$articleObj = ArticleModule_Class::getInstance($db);
				$articleID = isset($_REQUEST['article_id'])?$_REQUEST['article_id']:'';		
				$array = $articleObj->getArticleComments($domain_id,$articleID,50);
				if(is_array($array)){
					foreach($array as $m) {
					  $comments[$m['comment_id']] = array('i' => $m['comment_id'],'aid' => $m['comment_article_id'],'t' => $m['comment_title'],'c' => $m['comment_content'], 'a' => $m['comment_author'],'d' => $m['comment_date']);
					  $num =  $m['comment_content'];
					}
				}
			  	echo json_encode($comments);
			break;
			
	case	'allarticles': 
				$articles = array();
				$images = array();
				$articleObj = ArticleModule_Class::getInstance($db);			
				$pResultchk = $articleObj->getArticlesByDomain($domain_id,'%',0);
				if(is_array($pResultchk))
				{
					foreach($pResultchk as $m)
					{
							$articles[$m['article_id']] = array('id' => $m['article_id'],'title' => $m['article_title'],'summary' => stripslashes($m['article_summary']),'k' => $m['article_keyword'], 'a' => $m['article_author'],'d' => $m['article_update_date']);
					}
				}
				$imgObj = ImageModule_Class::getInstance($db);	
				$imgObj->getDomainImages($domain_id, $config->imageLibrary);
				$array2 = $imgObj->pictureList;
				if(is_array($array2)){
					  foreach($array2 as $m) {
					  	$images[$m['image_location']] = array('i' => $m['image_id'],'k' => $m['image_keyword'],'n' => $config->imageLibrary.$m['image_name']);
					  }
				}
				$r = array("articles" => $articles, "images" => $images);
				echo json_encode($r);
			break;
	case 	'getmorequestions' :
			$questionObj = QuestionAnswerModule_Class::getInstance($db);
			$start = empty($_REQUEST['start'])?0:$_REQUEST['start'];
			$amount = empty($_REQUEST['amount'])?10:$_REQUEST['amount'];
			$keyword = empty($_REQUEST['keyword'])?'':$_REQUEST['keyword'];
			$aRow = $questionObj->getQuestionsByKeyword($keyword, $start, $amount);				
			echo json_encode($aRow);
			break;
			
	case 	'getmoredirectories' :
			$directoryObj = DirectoryModule_Class::getInstance($db);
			$start = empty($_REQUEST['start'])?0:$_REQUEST['start'];
			$amount = empty($_REQUEST['amount'])?20:$_REQUEST['amount'];
			$keyword = empty($_REQUEST['keyword'])?'':$_REQUEST['keyword'];
			$aRow = $directoryObj->getDirectoriesByKeyword($keyword, $amount, $start);	
			echo json_encode($aRow);
			break;
			
	case 	'getdirectorybyid' :
			$directoryObj = DirectoryModule_Class::getInstance($db);
			$id = $_REQUEST['id'];			
			$aRow = $directoryObj->getDirectoryById($id);				
			echo json_encode($aRow);			
			break;
			
	case 	'getmoreevents' :
			$eventObj = EventModule_Class::getInstance($db);
			$offset 	= empty($_REQUEST['start'])?0:$_REQUEST['start'];
			$amount 	= empty($_REQUEST['amount'])?20:$_REQUEST['amount'];
			$keyword 	= empty($_REQUEST['keyword'])?'':$_REQUEST['keyword'];
			$city		= empty($_REQUEST['city'])?'':$_REQUEST['city'];
			$dayStart 	= isset($_REQUEST['dayStart'])?date('Y-m-d', strtotime($_REQUEST['dayStart'])):date('Y-m-d');
			$dayEnd 	= isset($_REQUEST['dayEnd'])?date('Y-m-d', strtotime($_REQUEST['dayEnd'])):date('Y-m-d', strtotime('+ 1 month'));
			$aRow = $eventObj->getEventsByKeyword($keyword, '', $city, $amount, $dayStart, $dayEnd, $offset);
			echo json_encode($aRow);
			break;
			
	case 	'geteventbyid' :
			$eventObj = EventModule_Class::getInstance($db);
			$id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
			if (!empty($id))
			{
				$aRow = $eventObj->getEventById($id);				
				echo json_encode($aRow);
			}			
			break;
			
	case 	'getfullarticle' :
			$articleObj = ArticleModule_Class::getInstance($db);			
			if(isset($_REQUEST['article_id']) && !empty($_REQUEST['article_id']))
			{
				$aRow = $articleObj->getArticleById($_REQUEST['article_id']);
			}
			elseif(isset($_REQUEST['keyword']) && !empty($_REQUEST['keyword']))
			{
				$aRow = $articleObj->getArticlesByDomain($domain_id,@$_REQUEST['keyword'],1);
			}
			$title = isset($aRow['article_title'])?$aRow['article_title']:'';
			$content = isset($aRow['article_content'])?$aRow['article_content']:'';
			echo "<h2>$title</h2><br> $content"; 
			break;
			
	case    'getecommerceproduts' : 
			$shoppingObj = ShoppingModule_Class::getInstance($db);
			$quantity = !empty($_REQUEST['quantity'])? $_REQUEST['quantity']:'12';
			$keyword = !empty($_REQUEST['keyword']) ? $_REQUEST['keyword']:'all';
			$source = !empty($_REQUEST['source']) ? $_REQUEST['source']:array('amazon','shopzilla');
			$products = $shoppingObj->getData($keyword, $source, $quantity);
			echo json_encode($products);
	        break;
			
	case 	'local_relate_sites' :
			  $keywords = explode(' ', $_REQUEST['keyword']);
			  $s = sizeof($keywords);
			  $domainObj = Domain_Class::getInstance($db, $domain_id);
				for($i=$s; $i>0; $i--){
					$array = $domainObj->getRelatedDomains($keywords[$i-1],6);
					if(is_array($array)){
						foreach($array as $m) {
							$sites[$m['domain_id']] = array('i' => $m['domain_id'],'k' => $m['domain_keyword'],'url' => $m['domain_url']);
						}
					}

					$array = $domainObj->getRelatedDomains(substr($keywords[$i-1],0,-1),6);
					if(is_array($array)){
						foreach($array as $m) {
							$sites[$m['domain_id']] = array('i' => $m['domain_id'],'k' => $m['domain_keyword'],'url' => $m['domain_url']);
						}
					}
				}
				array_unique($sites);
			    if($_REQUEST['from']=='local')
					echo json_encode($sites);
			    else
					echo $_GET['callback'] . '(' .json_encode($sites). ')';
			break;
			
	case 	'refer_url':
			$to = $_REQUEST['femail'];
			$fname = $_REQUEST['fname'];
			$yname = $_REQUEST['yname'];
			$yemail = $_REQUEST['yemail'];
			$subject = 'Great Article from your friend '.$yname.', '.$yemail;
			$url = $_REQUEST['url'];
			$domain = $_REQUEST['domain'];
			$message = "Dear ".$fname.',<br /><br />The following message is from your friend '.$yname.', '.$yemail.'.<br />"'.$_REQUEST['fmessage'].'".<br /><br/>The article link is '.$url.'<br /><br />Enjoy.<br /><br /><b>Team '.$domain.'</b>';
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: '.$yemail. "\r\n" . 'Reply-To: '.$yemail. "\r\n" . 'X-Mailer: PHP/' . phpversion();
			
			mail($to, $subject, $message, $headers);
			break;
			
	case 	'subscribe_news':
			$email = mysql_prep(!empty($_REQUEST['email'])?$_REQUEST['email']:null);
			$fname = mysql_prep(!empty($_REQUEST['fname'])?$_REQUEST['fname']:'');
			$lname = mysql_prep(!empty($_REQUEST['lname'])?$_REQUEST['lname']:'');
			$interests = !empty($_REQUEST['interest'])?$_REQUEST['interest']:'';
			$interest = array('interest' => mysql_prep(is_array($interests)?implode(',',$interests):$interests));
			$domain = $pRow['domain_url'];
			$obj = Visitor_Class::getInstance($db);
		    $array = array(  'firstname' => $fname
		    				,'lastname' => $lname
		    				,'email' => $email
		    				,'interest' => $interest
		    				,'active' => 1
		    				,'from_domain' => $domain
		    			); 
		    $obj->save_visitor($array);
			break;

	case 'submit_poll': // check the request
			if (empty($_REQUEST['domain_id']) || empty($_REQUEST['module_layout_id']) || empty($_REQUEST['options']))
			{
				if (isset($_REQUEST['json']))
					echo json_encode(false);
				else 
					header("Location:".$_REQUEST['referer']);
				break; 
			}
			
			$Poll = PollModule_Class::getInstance($db);
			// check if user already vote 		
			if($Poll->isVoted($_REQUEST['module_layout_id'],true))
			{
				if (isset($_REQUEST['json']))
					echo json_encode(false);
				else 
					header("Location:".$_REQUEST['referer']);
				break; 
			}
			
			// user didn't vote before
			$domain_id = $_REQUEST['domain_id'];
			$module_layout_id = $_REQUEST['module_layout_id'];
			$oa = explode(',',$_REQUEST['options']);
			
			$poll_new = array();
			$poll_old = $Poll->getPollResultById($domain_id, $module_layout_id);
			foreach($oa as $k=>$v){
				$poll_new[trim($v)] = 0;
			}
			if (!empty($poll_old))
			{
				$poll_new = array_merge($poll_new,$poll_old);
			}
			if($_REQUEST['type']=='checkbox')
			{															
				for($i=0; $i<sizeof($oa); $i++)
				{ 
					if(isset($_REQUEST['p'.$i]))
					{
						$poll_new[$_REQUEST['p'.$i]] += 1;
					}
				}			
			}
			else
			{			
				if(in_array($_REQUEST['pr'], $oa))
				{
					$poll_new[$_REQUEST['pr']] += 1;
				}
			}
			$id = $Poll->savePollResultById($poll_new, $domain_id, $module_layout_id);

			if (isset($_REQUEST['json']))
				echo json_encode(!empty($id));
			else 
				header("Location:".$_REQUEST['referer']);
			break;
			
	default :
			break;
}
exit;

function mysql_prep($value){
        if(get_magic_quotes_gpc()){
            $value = trim($value);
        } else {
            $value = addslashes($value);
        }
        return $value;
} 
?>
