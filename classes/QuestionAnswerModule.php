<?php
/**	APPLICATION:	SX25
*	FILE:			directoryModule
*	DESCRIPTION:	display domain - DirectoryModule_Class read directories from database
*	CREATED:		18 October 2010 by Diana De vargas
*	UPDATED:									
*/

class QuestionAnswerModule_Class extends  Module_Class
{
	protected	$moduleName = 'QuestionAnswer';
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
		$layout = '		
			<!-- Question Module -->
			<div class="question">                                            
             <div class="left">
					<img width="50" height="50" class="avatar avatar-50 photo" src="{QUESTION_USER_PHOTO}" >
             </div>
                                            
             <div class="left questionMain">
                    <h4>{QUESTION_SUBJECT}</h4>
                    <p>{QUESTION_CONTENT}</p>                         
                    <div class="questionByline">                                                    
                          <img alt="Answers" src="num-answer-icon.jpg">
                                                    
                          <span class="answers">{QUESTION_ANSWER_SUMMARY} ... </span><a href="result.php?Keywords={QUESTION_KEYWORD}&question_id={QUESTION_ID}">[more]</a><br />
                          <span>By: <a href="result.php?Keywords={QUESTION_KEYWORD}&question_id={QUESTION_ID}">{QUESTION_ANSWERER}</a></span> <span class="question_date">{QUESTION_DATE}</span>                                                   
                    </div>
             </div>
                                             
             <div class="clear"></div>
			</div>';
			$css = '
					/*Module Question Div*/
					.question {
						border-bottom: 1px dashed #CCCCCC;
						margin-bottom: 10px;
						padding-bottom: 10px;
						width: 500px;
					}
					
					.left {
						float: left;
						overflow: hidden;
					}
					
					.avatar {
						border: 1px solid #CCCCCC;
						margin: 5px 4px 0 0;
						padding: 3px;
					}
					
					.questionMain {
						width: 432px;
					}
					
					.question h4 {
						margin-bottom: 5px;
						padding-bottom: 5px;
					}

					h4 {
						border-bottom: 1px solid #CCCCCC;
						font-size: 16px;
						font-weight: bold;
						line-height: 18px;
						margin: 3px 0 8px;
					}
					
					.questionByline {
						margin-top: 0;
						font-size: 12px;
					}
					
					.questionByline .answers, .question_date {
						font-weight: normal; color:#999;
					}

					span {
						margin-left: 4px;
					}
					
					.clear {
						clear: both;
					}
					
			';
			
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
    
    public function getOneQuestionByKeyword($keyword, $start, $alterkw='')
	{
		$data = $this->getQuestionsByKeyword($keyword, $start, 1, $alterkw);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = false;
		}
		
		return $pRow;
	}

	public function getQuestionsByKeyword($keyword, $start='', $numQuestiones=5, $alterkw='', $answers=false, $expert=false)
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numQuestiones > 0)?" LIMIT $numQuestiones ":'';
		$offset = (!empty($start))?" OFFSET $start ":'';
		$answer = $answers?' left join (SELECT question_id, COUNT(*) as num FROM answers GROUP BY question_id) as a on a.question_id = question_answer.question_id ':'';
		$wexpert= $expert?'':' or question_answer IS NOT NULL ';
		$where  = $answers?" and (num IS NOT NULL $wexpert) ":'';
		$order  = $answers?' question_default DESC, num DESC, question_created_date DESC ':' question_default DESC, question_created_date DESC ';
		$aQuery = "SELECT question_answer.* FROM question_answer $answer WHERE question_keyword = '$keyword' $where order by $order $limit $offset ";
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$aRow['content_source'] = 'db';
			$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$this->avatar:$aRow['question_user_photo'];
			$result[] = $aRow;
		}
	
		if(sizeof($result)==0) 
		{
			// Search for Q&A by related keyword in related_keyword_qa table or with the $alterkw
			$where 	= " question_keyword in (ifnull((select related_keyword_original From related_keyword_qa where related_keyword = '$keyword'),'$alterkw')) and ";
			$wexpert= $expert?'':' or question_answer IS NOT NULL ';
			$where .= $answers?" (num IS NOT NULL $wexpert) ":' question_answer IS NOT NULL ';
			$order  = $answers?' num DESC ':' question_default DESC ';
			$aQuery = "SELECT question_answer.* FROM question_answer $answer WHERE $where order by $order, question_created_date DESC $limit $offset ";
			$aResults = $this->_db->select($aQuery);
			$result = array();

			while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
			{
				$aRow['content_source'] = 'db';
				$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$this->avatar:$aRow['question_user_photo'];
				$result[] = $aRow;
			}
			if(sizeof($result)==0) 
			{
				// Search any Q&A
				$wexpert= $expert?'':' or question_answer IS NOT NULL ';
				$where  = $answers?' (num IS NOT NULL $wexpert) ':' question_answer IS NOT NULL ';
				$order  = $answers?' num DESC ':' question_default DESC ';
				$aQuery = "SELECT question_answer.* FROM question_answer $answer WHERE $where order by $order, question_created_date DESC $limit $offset ";
				$aResults = $this->_db->select($aQuery);
				$result = array();
	
				while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
				{
					$aRow['content_source'] = 'db';
					$aRow['question_user_photo'] = empty($aRow['question_user_photo'])?$this->avatar:$aRow['question_user_photo'];
					$result[] = $aRow;
				}
			}
		}
		return $result;
	}
	
	public function getQuestionById($question_id)
	{
		$aQuery = "SELECT * FROM question_answer WHERE question_id='".$question_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		// check the default for the photo
		$pRow['question_user_photo'] = empty($pRow['question_user_photo'])?$this->avatar:$pRow['question_user_photo'];
		
		return $pRow;
	}
	
	public function getQuestionComments($domain_id,$question_id,$maxComments=10)
	{
		$comment = Comment_Class::getInstance($this->_db);
		return $comment->get_comments('question_answer',$question_id,$domain_id,$maxComments);
	}
	
	public function getQuestionAnswers($question_id, $maxAnswers=0, $offset='')
	{
		$question 	= !empty($question_id)?" WHERE question_id = '$question_id' ":'';
		$limit		= !empty($maxAnswers)?" LIMIT $maxAnswers ":'';
		$offset 	= (!empty($offset) && !empty($limit))? " OFFSET  $offset ":'';
		$cQuery = "SELECT * FROM answers $question ORDER BY question_id, answer_order $limit $offset ";
		$cResults = $this->_db->select($cQuery);
		$answersArray = array();
		while($cRow = $this->_db->get_row($cResults, 'MYSQL_ASSOC')) 
		{
			$answersArray[] = $cRow;
		}
		return $answersArray;
	}
	
	/**
	 * get Questiones
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numQuestiones Questiones to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numQuestiones,$extraParams = array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		// extra parameters			
		$question_id    = (isset($extraParams['question_id'])&&!empty($extraParams['question_id']))?$extraParams['question_id']:0;
		$orign_keyword  = isset($extraParams['orign_keyword'])?trim($extraParams['orign_keyword']):'';
		$answers		= isset($extraParams['answers'])?$extraParams['answers']:false;
		$expert			= isset($extraParams['expert'])?$extraParams['expert']:false;
		
		$numImg = round($numQuestiones/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch ($source) {
					case 'db': 	if ($question_id > 0) {
									$data[] = $this->getQuestionById($question_id);
							   	} else {
									$data = $this->getQuestionsByKeyword($keyword, '', $plus, $orign_keyword, $answers, $expert);
							   	}
							   	break;
				}
			}
		}
		
		return $data;
	}
	
}