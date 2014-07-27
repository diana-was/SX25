<?php
/**	APPLICATION:	SX25
*	FILE:			comment.php 
*	DESCRIPTION:	Insert the messages and comments in the messages and commets table
*	CREATED:		Diana De Vargas 
*	UPDATED:         
*   USAGE:          
*/
include_once("config.php");
$insert_id = false;

if (!empty($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'contact_us':
			$name 			= isset($_POST['name'])?@mysql_real_escape_string(strip_tags($_POST['name'])):'';
			$email 			= isset($_POST['email'])?@mysql_real_escape_string(strip_tags($_POST['email'])):'';
			$subject 		= isset($_POST['subject'])?@mysql_real_escape_string(strip_tags($_POST['subject'])):'';
			$message 		= isset($_POST['message'])?@mysql_real_escape_string(strip_tags($_POST['message'])):'';
			$msg_domain_id	= isset($_POST['domain_id'])?@mysql_real_escape_string(strip_tags($_POST['domain_id'])):$domain_id;
			
			if($message != '')
			{
				$Msg = Message_Class::getInstance($db);
				$insert_id = $Msg->save_message($controller->domain,$msg_domain_id,array('name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message));
			}
		break;
		
		case 'add_drupal_comment':
			$url = $config->drupal_service_url."?q=my_services/comment";
			$nid = $_REQUEST['nid'];
			$subject = !empty($_REQUEST['comment_subject'])?@mysql_real_escape_string(strip_tags($_REQUEST['comment_subject'])):'';
			$body = !empty($_REQUEST['comment_body'])?@mysql_real_escape_string(strip_tags($_REQUEST['comment_body'])):'';
			$name = !empty($_REQUEST['comment_name'])?@mysql_real_escape_string(strip_tags($_REQUEST['comment_name'])):'';

			$data = array('comment[nid]'=>$nid, 'comment[name]'=>$name, 'comment[status]'=>'0', 'comment[subject]'=>$subject, 'comment[language]'=>'und', 'comment[comment_body][und][0][value]'=>$body);
			$curlObj = new SingleCurl_Class();	  
			$curlObj->createCurl('post',$url,$data);  	  
        break;   
		
		case 'directory_listing':
			$comment_author 	= !empty($_REQUEST['username'])?@mysql_real_escape_string(strip_tags($_REQUEST['username'])):'';
			$comment_title 		= 'Application of Listing Directory';
			$business_name		= !empty($_REQUEST['business_name'])?@mysql_real_escape_string(strip_tags($_REQUEST['business_name'])):'';
			$email				= !empty($_REQUEST['email'])?@mysql_real_escape_string(strip_tags($_REQUEST['email'])):'';
			$phone				= !empty($_REQUEST['phone'])?@mysql_real_escape_string(strip_tags($_REQUEST['phone'])):'';
			$comment_content 	= 'Business name: '.$business_name.', Email: '.$email.', Phone: '.$phone;
			$comment_domain_id	= !empty($_POST['domain_id'])?@mysql_real_escape_string(strip_tags($_POST['domain_id'])):$domain_id;
			
			$comment = Comment_Class::getInstance($db);
			$insert_id = $comment->save_comment(null,null,$comment_domain_id,$controller->domain,array('author' => $comment_author, 'title' => $comment_title, 'content' => $comment_content));
		break;
		
		default:
			$comment_author 	= !empty($_POST['author'])?@mysql_real_escape_string(strip_tags($_POST['author'])):'';
			$comment_title 		= !empty($_POST['title'])?@mysql_real_escape_string(strip_tags($_POST['title'])):'';
			$comment_content 	= !empty($_POST['comment'])?@mysql_real_escape_string(strip_tags($_POST['comment'])):'';
			$comment_article_id = !empty($_POST['article_id'])?@mysql_real_escape_string(strip_tags($_POST['article_id'])):'';
			$comment_question_id= !empty($_POST['question_id'])?@mysql_real_escape_string(strip_tags($_POST['question_id'])):'';
			$comment_domain_id	= !empty($_POST['domain_id'])?@mysql_real_escape_string(strip_tags($_POST['domain_id'])):$domain_id;
			
			if (!empty($comment_article_id) && is_numeric($comment_article_id))
			{
				$table			= 'articles';
				$id				= $comment_article_id;
			}
			elseif (!empty($comment_question_id) && is_numeric($comment_question_id))
			{
				$table			= 'question_answer';
				$id				= $comment_question_id;
			}
			else
			{
				$table			= !empty($_POST['about'])?@mysql_real_escape_string(strip_tags($_POST['about'])):'';
				$id				= !empty($_POST['id'])?@mysql_real_escape_string(strip_tags($_POST['id'])):'';
			}
		
			$comment = Comment_Class::getInstance($db);
			$insert_id = $comment->save_comment($table,$id,$comment_domain_id,$controller->domain,array('author' => $comment_author, 'title' => $comment_title, 'content' => $comment_content));
		break;
	}
}

if (isset($_REQUEST['json']))
	echo json_encode(!empty($insert_id));
else 
	header("Location:".$_REQUEST['referer']);
?>