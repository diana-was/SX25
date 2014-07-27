<?php

/**
 * The magic wand**
 * Render the Directories  if the layout have the {DIRECTORIES} tags
 * leave the data in the $pRow array
 */
function renderShopping ($htmlCode, $keyword, $orign_keyword, $pRow, $page) 
{
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db = db_class::getInstance();
	$shoppingObj = ShoppingModule_Class::getInstance($db);
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	$Html = new Html();
	
	$layoutRow = $domainObj->getLayout();
	$listLayoutModules = isset($layoutRow['layout_modules'])?$layoutRow['layout_modules']:null;
	$sources = $shoppingObj->getLayoutSettings($listLayoutModules,$page,'sources',$shoppingObj->sourceList);

	if(!empty($htmlCode))
	{
		$products = Module_Class::modulesFromTags($htmlCode,'PRODUCT_TITLE',array('{','PRODUCT_TITLE_','}'));
		$products = array_merge($products,Module_Class::modulesFromTags($htmlCode,'PRODUCT_ID',array('{','PRODUCT_ID_','}')));
		$products = array_unique($products);
		if (in_array('PRODUCT_TITLE', $products) && in_array('PRODUCT_ID', $products))
		{
			$pos = array_search('PRODUCT_ID', $products);	
			unset($products[$pos]);
		}
			
		$count = 0;
		foreach ($products as $id) 
		{
			if (is_numeric($id))
			{
				$count = ($count < $id)?$id:$count;
			} 
		}
		if (!empty($products))
		{
			$pList = ($count > 0)?$shoppingObj->getData($keyword, $sources, $count, array('category' => $orign_keyword)):array();
				
			foreach ($products as $id) 
			{

				switch ($id) 
				{
					case 'PRODUCT_TITLE' :	
					case 'PRODUCT_ID' :	
								$product_id = (!isset($controller->request['product_id']) || empty($controller->request['product_id']))?'':$controller->request['product_id'];
								$source = (!isset($controller->request['source']) || empty($controller->request['source']))?$sources:$controller->request['source'];
								$aRow = $shoppingObj->getData($keyword,$source,1,array('product_id' => $product_id,'category' => $orign_keyword));
								$aRow = (!empty($aRow) && is_array($aRow))?$aRow[0]:'';
								if (!empty($aRow))
								{
									$pRow['product_id'] 			= isset($aRow['product_id'])?$aRow['product_id']:'';
									$pRow['product_url']			= isset($aRow['product_url'])?$aRow['product_url']:'';
									$pRow['product_name']			= isset($aRow['product_name'])?$aRow['product_name']:'';
									$pRow['product_num_items']		= isset($aRow['product_num_items'])?$aRow['product_num_items']:'';
									$pRow['product_category']		= isset($aRow['product_category'])?$aRow['product_category']:'';
									$pRow['product_price']			= isset($aRow['product_price'])?$aRow['product_price']:'';
									$pRow['product_image']			= isset($aRow['product_image'])?$aRow['product_image']:'';
									$pRow['product_manufacturer']	= isset($aRow['product_manufacturer'])?$aRow['product_manufacturer']:'';
									$pRow['product_description'] 	= isset($aRow['product_description'])?@$aRow['product_description']:'';
									$pRow['product_details'] 		= isset($aRow['product_details'])?$aRow['product_details']:'';
									$pRow['product_weight']			= isset($aRow['product_weight'])?$aRow['product_weight']:'';
									$pRow['product_features']		= isset($aRow['product_features'])?$aRow['product_features']:'';
									$pRow['product_lowest_price']	= isset($aRow['product_lowest_price'])?$aRow['product_lowest_price']:'';
									$pRow['product_lowest_used_price'] = isset($aRow['product_lowest_used_price'])?$aRow['product_lowest_used_price']:'';
									$pRow['product_lowest_refurbished_price'] = isset($aRow['product_lowest_refurbished_price'])?$aRow['product_lowest_refurbished_price']:'';
									$pRow['product_dimension']		= isset($aRow['product_dimension'])?$aRow['product_dimension']:'';
									$pRow['product_disclaimer']		= isset($aRow['product_disclaimer'])?$aRow['product_disclaimer']:'';
									$pRow['product_source']			= isset($aRow['product_source'])?$aRow['product_source']:'';
									$pRow['product_datetime']		= isset($aRow['product_datetime'])?$aRow['product_datetime']:'';
									
									if (isset($aRow['product_reviews']) && !empty($aRow['product_reviews']))
									{
										foreach ($aRow['product_reviews'] as $i => $rev)
										{
											$j = $i + 1;
											$pRow["product_reviewsource_$j"] = $rev['Source'];
											$pRow["product_reviewcontent_$j"] = $rev['Content'];
										}
									}
								}
								break;
					default :	if (is_numeric($id))
								{
									$id = trim($id);
									$k = (int) $id;
									$k = $k - 1;
									// get question
									if (isset($pList[$k]))
									{
										$aRow = $pList[$k];
										$pRow["product_id_$id"] 			= @$aRow['product_id'];
										$pRow["product_url_$id"]			= @$aRow['product_url'];
										$pRow["product_name_$id"]			= @$aRow['product_name'];
										$pRow["product_num_items_$id"]		= @$aRow['product_num_items'];
										$pRow["product_category_$id"]		= @$aRow['product_category'];
										$pRow["product_price_$id"]			= @$aRow['product_price'];
										$pRow["product_image_$id"]			= @$aRow['product_image'];
										$pRow["product_source_$id"]			= @$aRow['product_source'];
										$pRow["product_manufacturer_$id"]	= isset($aRow['product_manufacturer'])?$aRow['product_manufacturer']:'';
										$pRow["product_description_$id"] 	= isset($aRow['product_description'])?@$aRow['product_description']:'';
										$pRow["product_details_$id"] 		= isset($aRow['product_details'])?$aRow['product_details']:'';
										$pRow["product_reviews_$id"] 		= isset($aRow['product_reviews'])?$aRow['product_reviews']:array();
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