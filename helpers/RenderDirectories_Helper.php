<?php

/**
 * The magic wand**
 * Render the Directories  if the layout have the {DIRECTORIES} tags
 * leave the data in the $pRow array
 */
function renderDirectories ($htmlCode, $keyword, $orign_keyword, $pRow) 
{
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	
	// Init objects
	$db = db_class::getInstance();
	$directoryObj = DirectoryModule_Class::getInstance($db);
	$altkw = (empty($orign_keyword) || ($orign_keyword == $keyword))?$pRow['domain_keyword']:$orign_keyword;
	
	// find directories
	if(!empty($htmlCode)) 
	{
		$directories = Module_Class::modulesFromTags($htmlCode,'DIRECTORY_TITLE',array('{','DIRECTORY_TITLE_','}'));
		$directories = array_merge($directories,Module_Class::modulesFromTags($htmlCode,'DIRECTORY_URL',array('{','DIRECTORY_URL_','}')));
		$directories = array_merge($directories,Module_Class::modulesFromTags($htmlCode,'DIRECTORY_DESCRIPTION',array('{','DIRECTORY_DESCRIPTION_','}')));
		$directories = array_values(array_unique($directories));
		$damount = sizeof($directories);
		
		if (!empty($directories))
		{
			$aRows = $directoryObj->getData($keyword, 'db', $damount, array('orign_keyword' => $altkw));
			
			foreach ($aRows as $k => $aRow) {	
				$id = $directories[$k];	
				$pRow["DIRECTORY_TITLE_$id"] 		= @$aRow['directory_title'];
				$pRow["DIRECTORY_ID_$id"] 		    = @$aRow['directory_id'];
				$pRow["DIRECTORY_DESCRIPTION_$id"] 	= @$aRow['directory_description'];
				$pRow["DIRECTORY_URL_$id"] 			= @$aRow['directory_url'];
				$pRow["DIRECTORY_IMG_$id"] 			= @$aRow['directory_img'];
				$pRow["DIRECTORY_FLAG_$id"] 		= @$aRow['directory_flag'];
				
			}
		}
	}
	
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	return $pRow;
}
?>