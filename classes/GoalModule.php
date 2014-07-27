<?php
/**	APPLICATION:	SX25
*	FILE:			directoryModule
*	DESCRIPTION:	display domain - DirectoryModule_Class read directories from database
*	CREATED:		18 October 2010 by Diana De vargas
*	UPDATED:									
*/

class GoalModule_Class extends  Module_Class
{
	protected	$moduleName = 'Goal';
	protected	$sourceList = array('db');
	public      $avatar		= '';
	
    /**
     * constructor : call parent constructor
     *
     * @param object $db database object
     *
     * @return void
     */
	public function __construct(db_class $db)
	{
		return parent::__construct($db);
	}

    /**
     * Get the Module static object
     *
     * @return self
     */
	public static function getInstance(db_class $db) 
    {
    	return parent::getInstance($db,__CLASS__);
    }
	
	protected function __setDefaultLayout() 
	{
		$layout = '<!-- Goal Module -->
<div id="goal_{GOAL_ID}" class="goal">
		<div class="category"><img src="{GOAL_USER_PHOTO}"/>{GOAL_KEYWORD}</div>
		<div class="title">{GOAL_SUBJECT}</div>
		<div class="body">{GOAL_CONTENT}</div>
		<ul>
			<li class="start_date"><span>Goal Start Date: </span>{GOAL_START_DATE}</li>
			<li class="end_date"><span>Target Completion Date: </span>{GOAL_TARGET_DATE}</li>
			<li class="start_point"><span>Started At: </span>{GOAL_START_STATUS}</li>
			<li class="end_point"><span>Target: </span>{GOAL_TARGET_STATUS}</li>
		</ul>
		<a class="more_link showing_details">Show Less</a>

<div style="display: block;" class="more_div" id="comments_{GOAL_ID}">

	<div class="fb1">
		<div class="columns">
	
			<div class="leftwrap">
				<div class="lefttop">
							
					<div class="module results_cms_comment">
					
					<div class="frame">
					
						<div class="hgroup"><h2>Comments</h2></div>
						
						<div class="contents">
						<ul>
						{GOAL_COMMENTS}
						</ul>
						</div>
					
					</div> 
					
					</div> <!-- results_cms_comment -->
				
				</div><!-- lefttop -->
			</div><!-- leftwrap -->
			
			<div class="clear"></div>
		</div><!-- columns -->
		
		<div class="bottom"></div>
	</div><!-- fb1 -->
</div> <!-- more_div -->
</div>';
			$css = '/*Module Goal Div*/
.goal {
    margin: 17px 0 0;
    padding: 0 0 0 110px;
    position: relative;
}

.goal .category {
    left: 0;
    position: absolute;
    text-transform: capitalize;
    top: 0;
    width: 80px;
    min-height: 105px;
    color: #3C5A93;
    padding: 0 0 0 20px;
}

.goal .category img{
    width: 60px;
    display: block;
    border: none;
    padding: 0 0 30px 0;
    background: transparent;
}			

.goal .title {
    color: #CC3636;
    display: block;
    font-size: 18px;
    font-weight: bold;
    padding: 0 10px 0 0;
    width: 440px;
}

.goal .body {
    color: #686868;
    font-size: 12px;
    line-height: 18px;
    padding: 6px 0 10px;
}

.goal ul {
    height: 35px;
    padding: 10px 0 0;
    position: relative;
    list-style: none outside none;
}

.goal li {
    position: absolute;
    width: 265px;
    background: transparent !important;
}

.goal .start_date {
}

.goal .end_date {
    top: 25px;
}

.goal .start_point {
    left: 270px;
}

.goal .end_point {
    left: 270px;
    top: 25px;
}

.goal .more_link {
    background-image: url("/images/show-more-less.png");
    background-position: 0 -34px;
    display: block;
    height: 34px;
    left: -79px;
    margin: 20px 0 0;
    position: relative;
    text-indent: -999em;
    width: 619px;
}

.goal .more_div {
    background: none repeat scroll 0 0 #FFFFFF;
    border-bottom: 4px solid #B1B1B1;
    margin: 0 0 0 -80px;
    overflow: hidden;
    padding: 0 0 15px;
    position: relative;
    width: 619px;
}

.goal .results_cms_comment {
    background: none repeat scroll 0 0 transparent;
    border: medium none;
    margin: 0 0 0 80px;
    padding: 0;
}

.goal .results_cms_comment .frame {
    border: medium none;
    padding: 0;
}

.goal .results_cms_comment .hgroup {
    padding: 0;
}

.goal .results_cms_comment .contents {
    border: medium none;
    padding: 0;
    position: relative;
}

.goal .results_cms_comment ul {
    height: auto;
    padding: 0;
    position: relative;
}

.goal .results_cms_comment li {
    background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #CDCDCD;
    color: #686868;
    display: inline;
    float: left;
    font: bold 12px arial,helvetica,sans-serif;
    margin: 0 0 12px 59px;
    overflow: visible;
    padding: 0 0 10px;
    position: static;
    width: 442px;
    z-index: 100;
}

.goal .results_cms_comment .node_body {
    font-size: 12px;
    font-weight: normal;
    padding: 5px 15px;
}';
			
			$js = '';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 4, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
    public function getOneGoalByKeyword($keyword, $start, $alterkw='')
	{
		$data = $this->getGoalsByKeyword($keyword, $start, 1, $alterkw);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = false;
		}
		
		return $pRow;
	}

	public function getGoalsByKeyword($keyword, $start='', $numGoals=5, $alterkw='')
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numGoals > 0)?" LIMIT $numGoals ":'';
		$offset = (!empty($start))?" OFFSET $start ":'';
		$order  = ' goal_created_date DESC ';
		$aQuery = "SELECT goals.* FROM goals WHERE goal_keyword = '$keyword' and goal_approved = 1 order by $order $limit $offset ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		if($this->_db->row_count == 0) 
		{
			/* serach for the keyword in the content */
			$aQuery = "SELECT goals.*, (MATCH(goal_keyword,goal_subject,goal_content) AGAINST ('\"$keyword\"'  IN BOOLEAN MODE)) AS score FROM goals WHERE goal_approved = 1 HAVING score >= 1 order by $order $limit $offset ";
			$aResults = $this->_db->select($aQuery);
	
			if($this->_db->row_count == 0) 
			{
				// Search for Q&A by related keyword in related_keyword_qa table or with the $alterkw
				$order  = ' goal_created_date DESC ';
				$aQuery = "SELECT goals.* FROM goals WHERE goal_keyword = '$alterkw' and goal_approved = 1 order by $order $limit $offset ";
				$aResults = $this->_db->select($aQuery);

				if($this->_db->row_count == 0) 
				{
					// Search any Q&A
					$order  = ' goal_created_date DESC ';
					$aQuery = "SELECT goals.* FROM goals WHERE goal_approved = 1 order by $order $limit $offset ";
					$aResults = $this->_db->select($aQuery);
				}
			}
		}
		
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['content_source'] = 'db';
			$aRow['goal_user_photo'] = empty($aRow['goal_user_photo'])?$this->avatar:$aRow['goal_user_photo'];
			$result[] = $aRow;
		}
		
		return $result;
	}
	
	public function getGoalById($goal_id)
	{
		$aQuery = "SELECT * FROM goals WHERE goal_id='".$goal_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		// check the default for the photo
		$pRow['goal_user_photo'] = empty($pRow['goal_user_photo'])?$this->avatar:$pRow['goal_user_photo'];
		
		return $pRow;
	}
	
	public function getGoalComments($domain_id,$goal_id,$maxComments=10)
	{
		$comment = Comment_Class::getInstance($this->_db);
		return $comment->get_comments('goals',$goal_id,$domain_id,$maxComments);
	}
	
	/**
	 * get Goals
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numGoals Goals to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numGoals,$extraParams = array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		// extra parameters			
		$goal_id    = (isset($extraParams['goal_id'])&&!empty($extraParams['goal_id']))?$extraParams['goal_id']:0;
		$orign_keyword  = isset($extraParams['orign_keyword'])?trim($extraParams['orign_keyword']):'';
		
		$numImg = round($numGoals/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch ($source) {
					case 'db': 	if ($goal_id > 0) {
									$data[] = $this->getGoalById($goal_id);
							   	} else {
									$data = $this->getGoalsByKeyword($keyword, '', $plus, $orign_keyword);
							   	}
							   	break;
				}
			}
		}
		
		return $data;
	}
	
}