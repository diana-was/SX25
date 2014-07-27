<?php

/**
 * The magic wand**
 * Render the Events  if the layout have the {EVENTS} tags
 * leave the data in the $pRow array
 */
function renderEvent($htmlCode, $keyword, $alterkw, $pRow, $page) 
{
	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array	
	// Init objects
	$controller = Controller_Class::getInstance();
	$config = Config_Class::getInstance();
	$db = db_class::getInstance();
	$eventObj = EventModule_Class::getInstance($db);
	$domainObj = Domain_Class::getInstance($db, $controller->domain);
	
	$layoutRow = $domainObj->getLayout();
	$listLayoutModules = isset($layoutRow['layout_modules'])?$layoutRow['layout_modules']:null;
	$sources = $eventObj->getLayoutSettings($listLayoutModules,$page,'sources',$eventObj->sourceList);
	
	// find directories
	if(!empty($htmlCode)) 
	{
		$events = Module_Class::modulesFromTags($htmlCode,'EVENT_TITLE',array('{','EVENT_TITLE_','}'));
		$events = array_merge($events,Module_Class::modulesFromTags($htmlCode,'EVENT_DESCRIPTION',array('{','EVENT_DESCRIPTION_','}')));
		$events = array_values(array_unique($events));
		sort($events);
		$event_id = !empty($controller->request['event_id'])?$controller->request['event_id']:'';
		$source = (!isset($controller->request['source']) || empty($controller->request['source']))?$sources:$controller->request['source'];
		if (in_array('EVENT_TITLE', $events) && in_array('EVENT_DESCRIPTION', $events))
		{
			$pos = array_search('EVENT_DESCRIPTION', $events);	
			unset($events[$pos]);
		}
			
		$count = (in_array('EVENT_TITLE', $events) || in_array('EVENT_DESCRIPTION', $events))?1:0;
		foreach ($events as $id) 
		{
			if (is_numeric($id))
			{
				$count = ($count < $id)?$id:$count;
			} 
		}
		
		if (!empty($events))
		{
			$data = $eventObj->getData($keyword, $source, $count, array('event_id' => $event_id,'orign_keyword' => $alterkw));
			if (!empty($data))
			{
				// Unique event
				if (in_array('EVENT_TITLE', $events) || in_array('EVENT_DESCRIPTION', $events))
				{
					$pos = in_array('EVENT_TITLE', $events)?array_search('EVENT_TITLE', $events):array_search('EVENT_DESCRIPTION', $events);	
					unset($events[$pos]);
					sort($events);
					$aRow = array_shift($data);
					$pRow['event_id'] 		    = @$aRow['event_id'];
					$pRow['event_eventful_id']  = @$aRow['event_eventful_id'];
					$pRow['event_title'] 		= @$aRow['event_title']; 
					$pRow['event_description'] 	= @$aRow['event_description'];
					$pRow['event_url'] 			= @$aRow['event_url'];
					$pRow['event_keyword'] 		= @$aRow['event_keyword'];
					$pRow['event_start_time'] 	= @$aRow['event_start_time'];
					$pRow['event_stop_time'] 	= @$aRow['event_stop_time'];
					$pRow['event_venue_name']	= @$aRow['event_venue_name'];
					$pRow['event_venue_url']	= @$aRow['event_venue_url'];
					$pRow['event_venue_address']= @$aRow['event_venue_address'];
					$pRow['event_city_name'] 	= @$aRow['event_city_name'];
					$pRow['event_image_url']    = @$aRow['event_image_url'];
					$pRow['event_image_width'] 	= @$aRow['event_image_width'];
		            $pRow['event_image_height']	= @$aRow['event_image_height'];
				}	
				// Varios events
				foreach ($data as $k => $aRow) 
				{	
					$id = $events[$k];	
					$pRow["event_id_$id"] 		    = @$aRow['event_id'];
					$pRow["event_eventful_id_$id"]  = @$aRow['event_eventful_id'];
					$pRow["event_title_$id"] 		= @$aRow['event_title']; 
					$pRow["event_description_$id"] 	= @$aRow['event_description'];
					$pRow["event_url_$id"] 			= @$aRow['event_url'];
					$pRow["event_keyword_$id"] 		= @$aRow['event_keyword'];
					$pRow["event_start_time_$id"] 	= @$aRow['event_start_time'];
					$pRow["event_stop_time_$id"] 	= @$aRow['event_stop_time'];
					$pRow["event_venue_name_$id"]	= @$aRow['event_venue_name'];
					$pRow["event_venue_url_$id"]	= @$aRow['event_venue_url'];
					$pRow["event_venue_address_$id"]= @$aRow['event_venue_address'];
					$pRow["event_city_name_$id"] 	= @$aRow['event_city_name'];
					$pRow["event_image_url_$id"]    = @$aRow['event_image_url'];
					$pRow["event_image_width_$id"] 	= @$aRow['event_image_width'];
		            $pRow["event_image_height_$id"]	= @$aRow['event_image_height'];
				}
			}
		}
	}

	// IMPORTANT!!!!   $pRow has all the data for the domain to display. Be carfull not to delete this array
	return $pRow;
}

?>