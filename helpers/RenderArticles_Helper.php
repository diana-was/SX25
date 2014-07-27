<?php

/**
 * The magic wand**
 * Render the Articles if the layout have the {ARTICLE_TITLE} tags
 * leave the data in the $pRow array
 */
function renderArticles ($htmlCode, $keyword, $orign_keyword, $pRow, $isIndex=false) 
{
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db = db_class::getInstance();
	$articleObj = ArticleModule_Class::getInstance($db);
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$imgObj = ImageModule_Class::getInstance($db);
	$Html = new Html();
	
	$layoutRow = $domainObj->getLayout();
	$domain_id = $domainObj->domain_id;
	if(!empty($htmlCode)) 
	{
		//get the default images if missing in the articles
		if(!isset($pRow['menu_pic_1']) || empty($pRow['menu_pic_1']))  $pRow['menu_pic_1'] = $imgObj->getDefaultImage($config->imageLibrary);
		if(!isset($pRow['menu_pic_2']) || empty($pRow['menu_pic_2']))  $pRow['menu_pic_2'] = $imgObj->getDefaultImage($config->imageLibrary);
		if(!isset($pRow['menu_pic_3']) || empty($pRow['menu_pic_3']))  $pRow['menu_pic_3'] = $imgObj->getDefaultImage($config->imageLibrary);
		
		$mKeyArray = isset($pRow['article_menus'])?$pRow['article_menus']:array();
		$articles = Module_Class::modulesFromTags($htmlCode,'ARTICLE_TITLE',array('{','M_ARTICLE_TITLE_','}'));
		$articles = array_merge($articles,Module_Class::modulesFromTags($htmlCode,'ARTICLE_SUMMARY',array('{','M_ARTICLE_SUMMARY_','}')));
		$articles = array_merge($articles,Module_Class::modulesFromTags($htmlCode,'ARTICLE_CONTENT',array('{','M_ARTICLE_CONTENT_','}')));
		$articles = array_unique($articles);
		if (in_array('ARTICLE_TITLE', $articles))
		{
			$pos = array_search('ARTICLE_SUMMARY', $articles);	
			if ($pos !== false) unset($articles[$pos]);
			$pos = array_search('ARTICLE_CONTENT', $articles);	
			if ($pos !== false) unset($articles[$pos]);
		}
		if (in_array('ARTICLE_SUMMARY', $articles) && in_array('ARTICLE_CONTENT', $articles))
		{
			$pos = array_search('ARTICLE_CONTENT', $articles);	
			unset($articles[$pos]);
		}
		$used = array();
		foreach ($articles as $id) {
			switch ($id) 
			{
				case 'ARTICLE_TITLE' :	
				case 'ARTICLE_SUMMARY' :	
				case 'ARTICLE_CONTENT' :	
							$aRow = array();
							if(!empty($controller->request['article_id']))
								$aRow = $articleObj->getArticleById($controller->request['article_id']);
							if (empty($aRow))
								$aRow = $articleObj->getOneArticleByDomain($domain_id, $keyword, $isIndex);

							$pRow['article_title'] 	= @$aRow['article_title'];
							$pRow['article_summary']= @$aRow['article_summary'];
							$pRow['article_content']= @$aRow['article_content'];
							$pRow['article_keyword']= @$aRow['article_keyword'];
							$pRow['article_id'] 	= @$aRow['article_id'];
							
							//comments
							$tags = Module_Class::modulesFromTags($htmlCode,'COMMENTS');
							if (($pRow['article_id'] != '') && in_array('COMMENTS',$tags))
							{
								$commentsArray = $articleObj->getArticleComments($domain_id,$pRow['article_id'],$layoutRow['layout_comment_num']);
								$pRow['article_comments'] = $Html->parseComment($layoutRow['layout_comment'],$commentsArray,'');
							}
							break;
				default :	if (is_numeric($id))
							{
								$id = trim($id);
								$k = (int) $id;
								$k = $k - 1;
								if(isset($mKeyArray[$k]))
								{
									$aRow = $articleObj->getOneArticleByDomain($domain_id, $mKeyArray[$k],'','',$used);
									if (empty($aRow)) {
										if (!isset($artList)) {
									 		$artList = $articleObj->getArticlesByDomain($domain_id,'%',0,'','',$used);
										}
										if (isset($artList[$k])) {
									 		$aRow = $artList[$k];
										}
									}
									$pRow["menu_article_title_$id"] 	= @$aRow['article_title'];
									$pRow["menu_article_summary_$id"] 	= @$aRow['article_summary'];
									$pRow["menu_article_keyword_$id"] 	= @$aRow['article_keyword'];
									$pRow["article_id$id"] 				= @$aRow['article_id'];
									$used[]								= @$aRow['article_id'];
								} 
								else 
								{
									if (!isset($artList)) {
									 	$artList = $articleObj->getArticlesByDomain($domain_id,'%',0);
									}
									if (isset($artList[$k])) {
										$pRow["menu_article_title_$id"] 	= @$artList[$k]['article_title'];
										$pRow["menu_article_summary_$id"] 	= @$artList[$k]['article_summary'];
										$pRow["menu_article_keyword_$id"] 	= @$artList[$k]['article_keyword'];
										$pRow["article_id$id"] 		  		= @$artList[$k]['article_id'];
									}
								}
							}
							break;
			}
		}
	}
	return $pRow;
}
?>