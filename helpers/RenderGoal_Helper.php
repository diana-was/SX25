<?php

/**
 * The magic wand**
 * Render the Directories  if the layout have the {DIRECTORIES} tags
 * leave the data in the $pRow array
 */
function renderGoal ($htmlCode, $keyword, $orign_keyword, $pRow, $page) 
{
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	
	// Init objects
	$controller = Controller_Class::getInstance();
	$config 	= Config_Class::getInstance();
	$db 		= db_class::getInstance();
	$goalObj 	= GoalModule_Class::getInstance($db);
	$domainObj 	= Domain_Class::getInstance($db, $controller->domain);
	$imgObj 	= ImageModule_Class::getInstance($db);
	$Html 		= new Html();
	$goalObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
	$altkw 		= (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
	
	$layoutRow = $domainObj->getLayout();
	$listLayoutModules = isset($layoutRow['layout_modules'])?$layoutRow['layout_modules']:null;
	$sources = $goalObj->getLayoutSettings($listLayoutModules,$page,'sources',$goalObj->sourceList);
	
	if(!empty($htmlCode)) 
	{
		$goals = Module_Class::modulesFromTags($htmlCode,'GOAL_CONTENT',array('{','GOAL_CONTENT_','}'));
		$goals = array_merge($goals,Module_Class::modulesFromTags($htmlCode,'GOAL_SUBJECT',array('{','GOAL_SUBJECT_','}')));
		$goals = array_unique($goals);
		if (in_array('GOAL_SUBJECT', $goals))
		{
			$pos = array_search('GOAL_CONTENT', $goals);	
			if ($pos !== false) unset($goals[$pos]);
		}
		$count = 0;
		foreach ($goals as $id) 
		{
			if (is_numeric($id))
			{
				$count = ($count < $id)?$id:$count;
			} 
		}
		
		if (!empty($goals))
		{
			$QAs = ($count > 0)?$goalObj->getData($keyword, $sources, $count, array('domain_id' => $domainObj->domain_id,'orign_keyword' => $altkw)):array();
			
			foreach ($goals as $id) 
			{
				switch ($id) 
				{
					case 'GOAL_SUBJECT' :
					case 'GOAL_CONTENT' :	
								$aRow = array();
								if(!empty($controller->request['goal_id']))
									$aRow = $goalObj->getQuestionById($controller->request['goal_id']);
								if (empty($aRow))
									$aRow = $goalObj->getOneQuestionByKeyword($orign_keyword, 0, $altkw);
	
								$pRow['goal_id'] 			= @$aRow['goal_id'];
								$pRow['goal_subject'] 		= @$aRow['goal_subject'];
								$pRow['goal_content']		= @$aRow['goal_content'];
								$pRow['goal_status']		= @$aRow['goal_status'];
								$pRow['goal_keyword']		= @$aRow['goal_keyword'];
								$pRow['goal_start_date']	= @$aRow['goal_start_date'];
								$pRow['goal_start_status']	= @$aRow['goal_start_status'];
								$pRow['goal_target_date']	= @$aRow['goal_target_date'];
								$pRow['goal_target_status']	= @$aRow['goal_target_status'];
								$pRow['goal_completion']	= @$aRow['goal_completion'];
								$pRow['goal_completion_date']=@$aRow['goal_completion_date'];
								$pRow['goal_visitor']		= @$aRow['goal_visitor'];
								$pRow['goal_user_photo']	= @$aRow['goal_user_photo'];

								//comments
								if (!empty($pRow['goal_id']) && (stripos($htmlCode, '{GOAL_COMMENTS}') !== false))
								{
									$commentsArray = $goalObj->getQuestionComments($domainObj->domain_id,$pRow['goal_id'],$layoutRow['layout_comment_num']); 
									$pRow['goal_comments'] = $Html->parseComment($layoutRow['layout_comment'],$commentsArray,'');
								}
								break;
					default :	if (is_numeric($id))
								{
									$id = trim($id);
									$k = (int) $id;
									$k = $k - 1;
									// get goal
									if (isset($QAs[$k]))
									{
										$aRow = $QAs[$k];
										$pRow["goal_id_$id"] 			= @$aRow["goal_id"];
										$pRow["goal_subject_$id"] 		= @$aRow["goal_subject"];
										$pRow["goal_content_$id"]		= @$aRow["goal_content"];
										$pRow["goal_status_$id"]		= @$aRow["goal_status"];
										$pRow["goal_keyword_$id"]		= @$aRow["goal_keyword"];
										$pRow["goal_start_date_$id"]	= @$aRow["goal_start_date"];
										$pRow["goal_start_status_$id"]	= @$aRow["goal_start_status"];
										$pRow["goal_target_date_$id"]	= @$aRow["goal_target_date"];
										$pRow["goal_target_status_$id"]	= @$aRow["goal_target_status"];
										$pRow["goal_completion_$id"]	= @$aRow["goal_completion"];
										$pRow["goal_completion_date_$id"]=@$aRow["goal_completion_date"];
										$pRow["goal_visitor_$id"]		= @$aRow["goal_visitor"];
										$pRow["goal_user_photo_$id"]	= @$aRow["goal_user_photo"];
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