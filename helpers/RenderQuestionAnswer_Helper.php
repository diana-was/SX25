<?php

/**
 * The magic wand**
 * Render the Directories  if the layout have the {DIRECTORIES} tags
 * leave the data in the $pRow array
 */
function renderQuestionAnswer ($htmlCode, $keyword, $orign_keyword, $pRow, $page) 
{
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db = db_class::getInstance();
	$questionObj = QuestionAnswerModule_Class::getInstance($db);
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$imgObj = ImageModule_Class::getInstance($db);
	$Html = new Html();
	$questionObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
	$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
	
	$layoutRow = $domainObj->getLayout();
	$listLayoutModules = isset($layoutRow['layout_modules'])?$layoutRow['layout_modules']:null;
	$sources = $questionObj->getLayoutSettings($listLayoutModules,$page,'sources',$questionObj->sourceList);
	
	if(!empty($htmlCode)) 
	{
		$questions = Module_Class::modulesFromTags($htmlCode,'QUESTION_CONTENT',array('{','QUESTION_CONTENT_','}'));
		$questions = array_merge($questions,Module_Class::modulesFromTags($htmlCode,'QUESTION_SUBJECT',array('{','QUESTION_SUBJECT_','}')));
		$questions = array_merge($questions,Module_Class::modulesFromTags($htmlCode,'QUESTION_ANSWER_SUMMARY',array('{','QUESTION_ANSWER_SUMMARY_','}')));
		$questions = array_unique($questions);
		if (in_array('QUESTION_SUBJECT', $questions))
		{
			$pos = array_search('QUESTION_CONTENT', $questions);	
			if ($pos !== false) unset($questions[$pos]);
			$pos = array_search('QUESTION_ANSWER_SUMMARY', $questions);	
			if ($pos !== false) unset($questions[$pos]);
		}
		if (in_array('QUESTION_CONTENT', $questions) && in_array('QUESTION_ANSWER_SUMMARY', $questions))
		{
			$pos = array_search('QUESTION_CONTENT', $questions);	
			unset($questions[$pos]);
		}
		$count = 0;
		foreach ($questions as $id) 
		{
			if (is_numeric($id))
			{
				$count = ($count < $id)?$id:$count;
			} 
		}
		
		if (!empty($questions))
		{
			$QAs = ($count > 0)?$questionObj->getData($keyword, $sources, $count, array('domain_id' => $domainObj->domain_id,'orign_keyword' => $altkw)):array();
			
			foreach ($questions as $id) 
			{
				switch ($id) 
				{
					case 'QUESTION_ANSWER_SUMMARY' :	
					case 'QUESTION_SUBJECT' :
					case 'QUESTION_CONTENT' :	
								$aRow = array();
								if(!empty($controller->request['question_id']))
									$aRow = $questionObj->getQuestionById($controller->request['question_id']);
								if (empty($aRow))
									$aRow = $questionObj->getOneQuestionByKeyword($orign_keyword, 0, $altkw);
	
								$pRow['question_subject'] 	= @$aRow['question_subject'];
								$pRow['question_content']	= @$aRow['question_content'];
								$answerArray = explode(" ", @$aRow['question_answer']);
								$answerSummary = '';
								for($i=0; $i<15 && $i<count($answerArray); $i++){ $answerSummary .=" ".$answerArray[$i]; }
								$pRow["question_answer_summary"]= @$answerSummary;
								$pRow['question_answer']		= @$aRow['question_answer'];
								$pRow['question_answerer']		= @$aRow['question_answerer'];
								$pRow['question_username']		= @$aRow['question_username'];
								$pRow['question_user_photo']	= @$aRow['question_user_photo'];
								$pRow['question_date']			= @$aRow['question_date'];
								$pRow['question_keyword']		= @$aRow['question_keyword'];
								$pRow['question_id'] 			= @$aRow['question_id'];
								
								$tags = Module_Class::modulesFromTags($htmlCode,'ANSWER_');
								if (!empty($tags) && !empty($pRow['question_id']))
								{
									$answersArray = $questionObj->getQuestionAnswers($pRow['question_id']);
									foreach( $answersArray as  $i => $content) 
									{
											$k = $i + 1;
											$pRow["answer_id_$k"] 		= @$content['answer_id'];
											$pRow["answer_subject_$k"] 	= @$content['answer_subject'];
											$pRow["answer_keyword_$k"] 	= @$content['answer_keyword'];
											$pRow["answer_content_$k"] 	= @$content['answer_content'];
											$pRow["answer_link_$k"] 	= @$content['answer_link'];
											$pRow["answer_type_$k"] 	= @$content['answer_type'];
											$pRow["answer_user_name_$k"]= @$content['answer_user_name'];
											$pRow["answer_user_photo_$k"]= @$content['answer_user_photo'];
									}
								}
								//comments
								if (!empty($pRow['question_id']) && (stripos($htmlCode, '{QUESTION_COMMENTS}') !== false))
								{
									$commentsArray = $questionObj->getQuestionComments($domainObj->domain_id,$pRow['question_id'],$layoutRow['layout_comment_num']); 
									$pRow['question_comments'] = $Html->parseComment($layoutRow['layout_comment'],$commentsArray,'');
								}
								break;
					default :	if (is_numeric($id))
								{
									$id = trim($id);
									$k = (int) $id;
									$k = $k - 1;
									// get question
									if (isset($QAs[$k]))
									{
										$aRow = $QAs[$k];
										$pRow["question_subject_$id"] 		= @$aRow["question_subject"];
										$pRow["question_content_$id"]		= @$aRow["question_content"];
										$answerArray = explode(" ", @$aRow["question_answer"]);
										$answerSummary = "";
										for($i=0; $i<15 && $i<count($answerArray); $i++){ $answerSummary .=" ".$answerArray[$i]; }
										$pRow["question_answer_summary_$id"]= @$answerSummary;
										$pRow["question_answer_$id"]		= @$aRow["question_answer"];
										$pRow["question_answerer_$id"]		= @$aRow["question_answerer"];
										$pRow["question_username_$id"]		= @$aRow["question_username"];
										$pRow["question_user_photo_$id"]	= @$aRow["question_user_photo"];
										$pRow["question_date_$id"]			= @$aRow["question_date"];
										$pRow["question_keyword_$id"]		= @$aRow["question_keyword"];
										$pRow["question_id_$id"] 			= @$aRow["question_id"];
									}
								} 
								break;
				}
			}
		}
	}

	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	return $pRow;
}




?>