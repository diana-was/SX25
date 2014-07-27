<?php

/**
 * Render the menu
 * 
 */
function renderMenu ($domain_id, $keyword, $pRow) 
{
	// Init objects
	$controller = Controller_Class::getInstance();
	$db = db_class::getInstance();
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$home = $controller->baseURL;

	//custom menu	
	$customMenu = $domainObj->getAllMenus($keyword);

	$relatedList 		= explode(',',$pRow['page_relates']); 
	$menusDisplayArray 	= array_merge($customMenu['page_menus_display'],$relatedList);
	$menusLinkArray 	= $customMenu['page_menus_link'];
	$menusKeywordArray 	= $customMenu['page_menus_keyword'];
	
	// set an article as default for related menu
	foreach($relatedList as $l) {
		$menusLinkArray[] = 'article';
		$menusKeywordArray[] = $l;
	}

	$menu = '<ul><li class="f"><a title="Home" href="'.$home.'index.php">Home</a></li>';	
	for($i=0; $i<count($menusLinkArray); $i++){
		$menu .= '<li><a title="'.$menusLinkArray[$i].'" href="'.$home.$menusLinkArray[$i].'/result.php?keyword='.$menusKeywordArray[$i].'">'.$menusDisplayArray[$i].'</a></li>';
	}
	$menu .= '</ul>';
	$pRow['menu'] = $menu;	

	$pRow['page_menus_display'] = $menusDisplayArray;
	$pRow['page_menus_link']	= $menusLinkArray;
	$pRow['page_menus_keyword'] = $menusKeywordArray;
	$pRow['article_menus'] 		= $customMenu['article_menus'];
	
	
	return $pRow;
}
?>