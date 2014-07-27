<?php
/**	APPLICATION:	SX25
*	FILE:			ForumModule.php
*	DESCRIPTION:	front end - Forum Module
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class BlogModule_Class    extends  Module_Class
{
	protected	$moduleName = 'Blog';
	protected	$sourceList = array('drupal');
	private     $config;
	private     $drupal_type = 'article';
	
	
    /**
     * constructor : call parent constructor
     *
     * @param object $db database object
     *
     * @return void
     */
	public function __construct(db_class $db)
	{
		$this->config = Config_Class::getInstance();
		return parent::__construct($db);
	}

    /**
     * Get the BlogModule static object
     *
     * @return self
     */
	public static function getInstance(db_class $db) 
    {
    	return parent::getInstance($db,__CLASS__);
    }

	protected function __setDefaultLayout() 
	{
		$layout = '		
			<!-- Blog Module -->
			<div class="node_block" style="display: block;">
			    <div class="node_photo"><a href="node_detail.php?node_id={NODE_ID}" ><img class="river-thumb" src="{RESULTS_IMAGE}"></a></div>
				<div class="node_intro"><h2><a href="node_detail.php?node_id={NODE_ID}">{NODE_TITLE}</a></h2><div class="byline"><a href="#">Category: </a>article<span class="pipe"> | </span>
				    <span class="date-heat"><span class="date format-date"> {NODE_CREATED} </span><span class="pipe">|</span><nobr><span class="grey views" title="views"></span></nobr>
					<span class="pipe">|</span><nobr title="Read comments"><a href="node_detail.php?node_id={NODE_ID}" class="comments-icon">Comments </a> <span>{NODE_COMMENT_COUNT}</span></nobr></span></div>
					<div class="excerpt">Created by  <span id="node_author_8">{NODE_AUTHOR}</span><span onclick="postComment({NODE_ID});" class="post_comment">Post Comment Here</span>
					<a class="nobr" href="node_detail.php?node_id={NODE_ID}">Read &gt;&gt;</a></div> </div> </div>';
			
		   	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= '';
	        $this->layout['layout_css'] = '';
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    	
	private function getNodesByType($keyword, $quantity)
	{
		$keyword = urlencode($keyword);
		$request = $this->config->drupal_service_url."my_services/node?parameters[type]=".$this->drupal_type;
		
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($request, false, $context);
		$xml = simplexml_load_string($xml);
		$num = 0;

		foreach ( $xml->item as $key=>$value)
		{	
			if ($num >=$quantity ) break;
			
			$timestamp = (string)$value->created;
            $created_date = date("Y-m-d H:i:s", $timestamp);

            $accessory = $this->getNodeByID($value->nid);	
				
			$data[] = array(
						'node_title' =>  (string) $value->title,
						'node_nid' => (string) $value->nid,
						'node_link' => (string) $value->uri,
						'node_created' =>  (string) $created_date,
						'node_type' =>  (string) $value->type,
						'node_comment_count' => $accessory['node_comment_count'],
						'node_image' => $accessory['node_image'],
						'node_author'=> $accessory['node_author'] 
			);				
			$num++;		
		} var_dump($data);
		return $data;
	}
	
	private function getNodeByID($nid)
	{	
		$url = $this->config->drupal_service_url."?q=my_services/node/".$nid;		
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($url, false, $context);
		$xml = simplexml_load_string($xml);
		$cmts = $this->getCommentsByNodeID($nid);

	    $comment_count = (string)sizeof($cmts); 
	    $image = (string)$xml->field_image->und->item->filename;   
		$author = (string)$xml->name; 
		$author = (!$author)?'Admin':$author;
		$node_content = (string)$xml->body->und->item->value;  
		$link = (string)$xml->path;
		$returnArray = array('node_comment_count'=>$comment_count, 'node_image'=>$image, 'node_author'=>$author, 'node_content'=>$node_content, 'node_link'=>$link); 
		return $returnArray;
	}
	
	private function getCommentsByNodeID($node_id)
	{
		$url = $this->config->drupal_service_url."?q=my_services/node/".$node_id."/comments";	
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$xml = file_get_contents($url, false, $context);
		$xml = simplexml_load_string($xml);
		
	    foreach ( $xml->item as $key=>$value)
		{				
            $created_date = date("Y-m-d H:i:s", (string)$value->created);
				
			$data[] = array(
						'comment_subject' => (string)$value->subject,
						'comment_nid' => (string)$value->nid,
						'comment_cid' =>  (string)$value->cid,
						'comment_created' => (string)$created_date,
						'comment_content' => (string)$value->comment_body->und->item->value,
						'comment_author'=> (string)$value->name 
			);					
		} 
		return $data;	
	}
	
	private function getDetailByNodeID($nid)
	{
		$node = $this->getNodeByID($nid);
		$comments = $this->getCommentsByNodeID($nid);
		$node['comments'] = $comments;
		
		return $node;
	}
	
	public function getData($keyword,$source,$numPost,$extraParams=array())
	{
		$data = array();
		if ($extraParams['node_id'] > 0) {
			$data = $this->getDetailByNodeID($extraParams['node_id']);
		} else {
			$data 	= $this->getNodesByType($keyword,$numPost);
		}
		return $data;
	}
	
}