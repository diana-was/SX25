<?php

/**
 * The magic wand**
 * Render the layouts for the master page and the modules
 * 
 */
function renderModules ($page, $domain_id, $keyword, $orign_keyword, $pRow) 
{
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db = db_class::getInstance();
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$Html = new Html();
	
	$htmlCode = '';
	$layoutRow = $domainObj->getLayout();
	$listLayoutModules = isset($layoutRow['layout_modules'])?$layoutRow['layout_modules']:null;
	$defaultImage = $Html->getDefaultImage($pRow);
	
	if(count($layoutRow) > 0) 
	{
		// Map the modules in the path with the layout {MODULEx} tags
		$layout = isset($layoutRow['layout_'.$page])?$layoutRow['layout_'.$page]:'';
		$modules= Module_Class::modulesFromTags($layout);

		// Insert the modules by URL in the module list if not already there
		$urlModule = array();
		if (!empty($layoutRow['layout_default_module']) && empty($controller->module))
			$controller->module = array(strtoupper($layoutRow['layout_default_module']));

		foreach ($controller->module as $pos => $mod)
		{
			$key = ($pos == 0) ?'MODULE':'MODULE'.intval($pos);
			$p   = array_search($key,$modules);
			if ($p !== false)
			{
				$modules[$p] = $mod;
				$urlModule[$mod] = $key;
			}
			$p   = array_search($key.'_BYID',$modules);
			if ($p !== false)
			{
				$modules[$p] = $mod.'_BYID';
				$urlModule[$mod.'_BYID'] = $key.'_BYID';
			}
		}
		array_unique($modules);
		
		// replace domain data tags in the layout
		$htmlCode 	= isset($layoutRow['layout_'.$page])?$Html->parseHtml($layoutRow['layout_'.$page],$pRow):'';
		
		// init js and css from the layout
		$cssCode 	= '';
		$jsModules	= '';
		
		foreach($modules as $module) 
		{
			$layoutPage = $page;
			$moduleCode = '';
			$replace 	= array($module.'_MODULE' => '');
			if (!empty($urlModule[$module])) 
			{
				$replace[$urlModule[$module]] = ''; 
			}
			switch ($module) {
				case 'ARTICLE' :
						//article
						$aRow = array();
						$modObj = ArticleModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',3);
						$aRow = $modObj->getData($keyword, $sources, $num, array('domain_id' => $domain_id, 'empty' => true));
						$moduleCode = $Html->parseArticleModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$aRow);
						/* replace images */
						$tags = Module_Class::modulesFromTags($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),'IMAGE');
						if (!empty($tags))
						{
							$imgObj = ImageModule_Class::getInstance($db);
							$images = $imgObj->getData($keyword, 'db', $num);
							$moduleCode = $Html->parseImageModule($moduleCode,$images,array('db'),array(),$defaultImage);
						}
						break;
				case 'ARTICLE_BYID' :
						//article
						$aRow = array();
						$layoutPage = "$layoutPage-id";
						$modObj = ArticleModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						if(!empty($controller->request['article_id']))
						{
							$aRow = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id, 'empty' => true, 'article_id' => $controller->request['article_id']));
							$moduleCode = $Html->parseArticleModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),array($aRow));
						}
						else
						{
							$aRow = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id, 'empty' => true));
							$moduleCode = $Html->parseArticleModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$aRow);
						}
						$article_id = @$aRow['article_id'];
						
						//comments
						$extrabox = '';
						$tags = Module_Class::modulesFromTags($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),'COMMENTS');
						if (($article_id != '') && in_array('COMMENTS',$tags))
						{
							$commentsArray = $modObj->getArticleComments($domain_id,$article_id,$layoutRow['layout_comment_num']);
							$commentCode = $Html-> parseComment($layoutRow['layout_comment'],$commentsArray,$extrabox);
							$moduleCode = str_replace('{COMMENTS}', $commentCode, $moduleCode);
							$replace['ARTICLE_ID'] 	= $article_id;
						}
						/* replace images */
						$tags = Module_Class::modulesFromTags($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),'IMAGE');
						if (!empty($tags))
						{
							$imgObj = ImageModule_Class::getInstance($db);
							$images = $imgObj->getData((isset($aRow['article_keyword'])?$aRow['article_keyword']:$keyword), 'db', 10);
							$moduleCode = $Html->parseImageModule($moduleCode,$images,array('db'),array(),$defaultImage);
						}
						break;
				case 'IMAGE' :
						$modObj = ImageModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$images = $modObj->getData($keyword, $sources, $num);
						$moduleCode = $Html->parseImageModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$images,$sources,$modObj->channels,$defaultImage);
						break;
				case 'IMAGE_BYID' :
						$modObj = ImageModule_Class::getInstance($db);
						$layoutPage = "$layoutPage-id";
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$id = (empty($controller->request['image_id']))?'':$controller->request['image_id'];

						if(!empty($id))
						{
							$images = $modObj->getData($keyword, 'db', 1, array('image_id' => $id));
							$moduleCode = $Html->parseImageModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$images,array('db'),$modObj->channels,$defaultImage);
						}
						else
						{
							$keyword = empty($keyword)? $orign_keyword:$keyword;
							$images = $modObj->getData($keyword, 'db', 1, array());
							$moduleCode = $Html->parseImageModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$images,array('db'),$modObj->channels,$defaultImage);
						}
						break;
				case 'VIDEO' :
						$modObj = VideoModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$videos = $modObj->getData($keyword, $sources, $num);
						$moduleCode = $Html->parseVideoModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$videos,$sources,$modObj->channels);
						break;
				case 'DISPLAY' :
						$show = isset($controller->path[0])?strtoupper($controller->path[0]):'';
						switch ($show) {
							case 'VIDEO' : 
								$moduleCode = $Html->parseDisplayVideo(@$controller->request['hplink']);
								break;
						}
						break;
				case 'POLL' :
						$modObj = PollModule_Class::getInstance($db);
						$module_layout_id = $modObj->getModuleLayoutID($listLayoutModules,$layoutPage); 					
						$extraParams = array('domain_id'=>$domain_id, 'module_layout_id'=>$module_layout_id);															
						$polldata = $modObj->getData('','','',$extraParams);																				
						$flag = $modObj->isVoted($module_layout_id);
						$moduleCode = $Html->parsePollModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage), $polldata, $flag);
						break;
				case 'RSS' :
						$modObj = RssModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$rss = $modObj->getData($keyword,$sources,$num);
						$moduleCode = $Html->parseRssModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$rss,$sources,$modObj->channels);
						break;
				case 'ARTICLEFEED' :
						$modObj = ArticleFeedModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$feed = $modObj->getData($keyword,$sources,$num);
						$moduleCode = $Html->parseArticleFeedModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$feed,$sources,$modObj->channels);
						break;
				case 'NEWS' :
						$modObj = NewsModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$feed = $modObj->getData($keyword,$sources,$num);
						$moduleCode = $Html->parseNewsModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$feed,$sources,$modObj->channels);
						break;
				case 'FORUM' :
						$modObj = ForumModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',5);
						$feed = $modObj->getData($keyword,$sources,$num);
						$moduleCode = $Html->parseForumModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$feed,$sources,$modObj->channels);
						break;
				case 'BLOG' :
						$modObj = BlogModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',8);
						$nodes = $modObj->getData($keyword, $sources, $num);  //beware keyword here should comply with the type of post in drupal
						$moduleCode = $Html->parseBlogModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$nodes);
						break;
				case 'BLOG_BYID' :
						$modObj = BlogModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$layoutPage = "$layoutPage-id";
						$node_id = (!isset($controller->request['node_id']) || empty($controller->request['node_id']))?'':$controller->request['node_id'];
						if (!empty($node_id))
						{
							$nodeDetail = $modObj->getData($keyword,$sources,1,array('node_id' => $node_id));
							$moduleCode = $Html->parseBlogDetail($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$nodeDetail);
						}
						break;
				case 'TOPIC' :
						$modObj = TopicModule_Class::getInstance($db);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',15);
						$feed = (isset($pRow['page_relates']) && !empty($pRow['page_relates']))?$pRow['page_relates']
									:$modObj->getData($keyword, $domainObj->domain_feedtype, $num, array('feed_id' => $domainObj->domain_feedid));
						$moduleStr = implode('&', $controller->module);
						$moduleCode = $Html->parseTopicModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$feed, $moduleStr);					
						break;
				case 'WIDGET' :
						$modObj = WidgetModule_Class::getInstance($db);		
						$moduleCode = $Html->parseWidgetModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage), $domainObj->domain_url);					
						break;			
				case 'DIRECTORY' :
						$modObj = DirectoryModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',10);
						$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
						$dirs = $modObj->getData($keyword,$sources,$num,array('orign_keyword' => $altkw));
						$moduleCode = $Html->parseDirectoryModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$dirs,$sources);
						break;
				case 'EVENT' :
						$modObj = EventModule_Class::getInstance($db);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',13);					
						$city = isset($controller->request['city'])? trim($controller->request['city']): '';
						$dayStart = isset($controller->request['dayStart'])?trim($controller->request['dayStart']):date('Y-m-d');
		                $dayEnd = isset($controller->request['dayEnd'])?trim($controller->request['dayEnd']):date('Y-m-d', strtotime('+ 1 month')); 
						$event_id = !empty($controller->request['event_id'])?$controller->request['event_id']:'';
		                $events = $modObj->getData($keyword,$sources,$num,array('city' => $city, 'dayStart'=>$dayStart, 'dayEnd'=>$dayEnd,'event_id' => $event_id,'orign_keyword' => $orign_keyword));
						$moduleCode = $Html->parseEventModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$events); 
						break;
				case 'SHOPPING' :
						$modObj = ShoppingModule_Class::getInstance($db);
						$category	= !empty($domainObj->domain_product_category)?$domainObj->domain_product_category:'All';
						$product_id = (!isset($controller->request['product_id']) || empty($controller->request['product_id']))?'':$controller->request['product_id'];
						$source = (!isset($controller->request['source']) || empty($controller->request['source']))?'':$controller->request['source'];
						$num  	= $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',10);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$products = $modObj->getData($keyword,$sources,$num,array('category' => $category));
						$moduleCode = $Html->parseShoppingModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$products);
						break;
				case 'SHOPPING_BYID' :
						$modObj = ShoppingModule_Class::getInstance($db);
						$category	= !empty($domainObj->domain_product_category)?$domainObj->domain_product_category:'All';
						$layoutPage = "$layoutPage-id";
						$product_id = (!isset($controller->request['product_id']) || empty($controller->request['product_id']))?'':$controller->request['product_id'];
						$source = (!isset($controller->request['source']) || empty($controller->request['source']))?'':$controller->request['source'];
						if (!empty($product_id) && !empty($source))
						{
							$products = $modObj->getData($keyword,$source,1,array('product_id' => $product_id,'category' => $category));
							$moduleCode = $Html->parseShoppingDetail($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$products);
						}
						break;
				case 'QUESTION' :
						//qustion&answer
						$imgObj = ImageModule_Class::getInstance($db);
						$modObj = QuestionAnswerModule_Class::getInstance($db);
						$modObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
						$question_id = (!isset($controller->request['question_id']) || empty($controller->request['question_id']))?'':$controller->request['question_id'];
						$keyword = empty($keyword)? $orign_keyword:$keyword;
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',3);
						$QAs = $modObj->getData($keyword, $sources, $num, array('domain_id' => $domain_id,'orign_keyword' => $altkw));
						$moduleCode = $Html->parseQuestionModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$QAs);
						break;
				case 'QUESTION_BYID' :
				case 'QAEXPERT_BYID' :
					    //qustion&answer
						$imgObj = ImageModule_Class::getInstance($db);
						$modObj = QuestionAnswerModule_Class::getInstance($db);
						$modObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
						$layoutPage = "$layoutPage-id";
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
						$question_id = (empty($controller->request['question_id']))?'':$controller->request['question_id'];
						$answers = stripos($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage), '{ANSWER_') !== false;
						$expert  = $answers && ($module == 'QAEXPERT_BYID');

						if(!empty($question_id))
						{
							$QAs = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id, 'question_id' => $question_id, 'orign_keyword' => $altkw));
							$moduleCode = $Html->parseQuestionModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$QAs);
							$question_id = isset($QAs[0]['question_id'])?$QAs[0]['question_id']:'';
						}
						else
						{
							$keyword = empty($keyword)? $orign_keyword:$keyword;
							$QAs = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id,'orign_keyword' => $altkw,'answers' => $answers,'expert' => $expert));
							$moduleCode = $Html->parseQuestionModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$QAs);
							$question_id = isset($QAs[0]['question_id'])?$QAs[0]['question_id']:'';
						}
						//Answers
						if (!empty($question_id) && $answers)
						{
							if (stripos($moduleCode, '{ANSWER_TYPE_') === false)
							{
								$answersArray = $modObj->getQuestionAnswers($question_id);
								$moduleCode = $Html->parseQuestionAnswers($moduleCode,$answersArray);
							}
							// new  
							else 
							{
								$typeArray = Module_Class::cssModulesFromTags($moduleCode,'ANSWER_TYPE',array('{','ANSWER_TYPE_','_END}','}'));
								$answersArray = $modObj->getQuestionAnswers($question_id);
								$moduleCode = $Html->parseTypeAnswers($moduleCode,$answersArray, $typeArray);
							}
						}
						//comments
						$extrabox = '';
						if (!empty($question_id) && (stripos($moduleCode, '{QUESTION_COMMENTS}') !== false))
						{
							$commentsArray = $modObj->getQuestionComments($domain_id,$question_id,$layoutRow['layout_comment_num']);
							$commentCode = $Html->parseComment($layoutRow['layout_comment'],$commentsArray,$extrabox);
							$moduleCode = str_replace('{QUESTION_COMMENTS}', $commentCode, $moduleCode);
							$replace['QUESTION_ID'] = $question_id;
						}
						break;
				case 'GOAL' :
						//qustion&answer
						$imgObj = ImageModule_Class::getInstance($db);
						$modObj = GoalModule_Class::getInstance($db);
						$modObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
						$goal_id = (!isset($controller->request['goal_id']) || empty($controller->request['goal_id']))?'':$controller->request['goal_id'];
						$keyword = empty($keyword)? $orign_keyword:$keyword;
						$num  = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'perPage',3);
						$Goals = $modObj->getData($keyword, $sources, $num, array('domain_id' => $domain_id,'orign_keyword' => $altkw));
						$moduleCode = $Html->parseGoalModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$Goals);
						break;
				case 'GOAL_BYID' :
					    //qustion&answer
						$imgObj = ImageModule_Class::getInstance($db);
						$modObj = GoalModule_Class::getInstance($db);
						$modObj->avatar = $imgObj->getDefaultAvatar($config->imageLibrary);
						$layoutPage = "$layoutPage-id";
						$sources = $modObj->getLayoutSettings($listLayoutModules,$layoutPage,'sources',$modObj->sourceList);
						$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$domainObj->domain_keyword:$orign_keyword;
						$goal_id = (empty($controller->request['goal_id']))?'':$controller->request['goal_id'];

						if(!empty($goal_id))
						{
							$Goals = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id, 'goal_id' => $goal_id, 'orign_keyword' => $altkw));
							$moduleCode = $Html->parseGoalModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$Goals);
							$goal_id = isset($Goals[0]['goal_id'])?$Goals[0]['goal_id']:'';
						}
						else
						{
							$keyword = empty($keyword)? $orign_keyword:$keyword;
							$Goals = $modObj->getData($keyword, $sources, 1, array('domain_id' => $domain_id,'orign_keyword' => $altkw));
							$moduleCode = $Html->parseGoalModule($modObj->getLayoutData('layout',$listLayoutModules,$layoutPage),$Goals);
							$goal_id = isset($Goals[0]['goal_id'])?$Goals[0]['goal_id']:'';
						}

						//comments
						$extrabox = '';
						if (!empty($goal_id) && (stripos($moduleCode, '{GOAL_COMMENTS}') !== false))
						{
							$commentsArray = $modObj->getGoalComments($domain_id,$goal_id,$layoutRow['layout_comment_num']);
							$commentCode = $Html->parseComment($layoutRow['layout_comment'],$commentsArray,$extrabox);
							$moduleCode = str_replace('{GOAL_COMMENTS}', $commentCode, $moduleCode);
							$replace['GOAL_ID'] = $goal_id;
						}
						break;
				default: $modObj = null;
							// Check if is a specific Widget required {WIDGET#n_MODULE}
							if (stripos($module, '#') !== false)
							{
								$parts = explode('#',$module);
								$subModule = empty($parts[0])?'':$parts[0];
								$layoutID = (empty($parts[1]) || !is_numeric($parts[1]))?0:$parts[1];
								if (!empty($layoutID))
								{
									switch ($subModule)
									{
										case 'WIDGET' :	$modObj = WidgetModule_Class::getInstance($db);
										                $subLayout = $modObj->getLayoutDataByID('layout',$layoutID);
										                if (!empty($subLayout))
										                { 		
															$moduleCode = $Html->parseWidgetModule($subLayout, $domainObj->domain_url);	
															// get the module js
															$mJS = $modObj->getLayoutDataByID('layout_js',$layoutID);
															if (!empty($mJS))
																$jsModules .= " $mJS".PHP_EOL;
															// get the module css
															$mCSS = $modObj->getLayoutDataByID('layout_css',$layoutID);
															if (!empty($mCSS))
																$cssCode .= " $mCSS".PHP_EOL;
										                }
														break;
									}
								}
							}
						break;
			}
			// get the module js
			if (is_object($modObj)) 
			{
				$mJS = $modObj->getLayoutData('layout_js',$listLayoutModules,$layoutPage);
				if (!empty($mJS))
					$jsModules .= " $mJS".PHP_EOL;
			}
			// get the module css
			if (is_object($modObj))
			{
				$mCSS = $modObj->getLayoutData('layout_css',$listLayoutModules,$layoutPage);
				if (!empty($mCSS))
					$cssCode .= " $mCSS".PHP_EOL;
			}

			if (isset($urlModule[$module]) && isset($replace[$urlModule[$module]])) 
			{
				$replace[$urlModule[$module]] = $moduleCode;
			} else {
				$replace[$module.'_MODULE'] = $moduleCode;
			}
			$htmlCode = $Html->replaceHtmlTags($htmlCode,$replace);
					
		}
		
		if (!empty($cssCode)) 
		{
			$htmlCode = $Html->insertHtmlCode($htmlCode,'css',$cssCode );
		}
		if (!empty($jsModules)) 
		{
			$htmlCode = $Html->insertHtmlCode($htmlCode,'jsLoad',$jsModules);
		}
		
		// tag cleanup
		foreach($modules as $module) {
			$htmlCode = str_replace('{'.$module.'}','',$htmlCode);
		}
	}
	
	return $htmlCode;
}
?>