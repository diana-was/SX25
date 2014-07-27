<?php
/**	APPLICATION:	SX25
*	FILE:			EventModule_Class
*	DESCRIPTION:	display domain - EventModule_Class read directories from database
*	CREATED:		
*	UPDATED:									
*/

class EventModule_Class extends  Module_Class
{
	protected	$moduleName = 'Event';
	protected	$sourceList = array('eventful');
	private     $app_key = "kQKGr7VcKNZqX87n";
	private     $user='princetonit';
	private     $password='1234abcd';
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
			<!-- Event Module -->
			<div class="event_item"> <a href="#"><img alt="alt" src="{EVENT_IMG}" class="img-indent"></a>
<span class="event_title">{EVENT_TITLE}</span>{EVENT_DESCRIPTION}<br><div style="clear: both;"></div></div>';
			$css = '
					/*Module Event Div*/
			.event_item {
				float: left;
				margin-right: 34px;
				margin-top: 28px;
				width: 201px;
				overflow: hidden;
			}
			
			.img-indent {
				float: left;
				margin-bottom: 7px;
				margin-right: 17px;
			}
			
			.event_title {
				color: #89181E !important;
				display: block;
				font-size: 22px;
				line-height: 22px;
				padding: 3px 0;
				clear: left;
			}
					
			';
			
			$js = '';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
    public function getOneEventByKeyword($keyword, $altKeyword='', $start, $alterkw='')
	{
		$data = $this->getEventsByKeyword($keyword, $altKeyword, $start, 1, $alterkw);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = false;
		}
		
		return $pRow;
	}

	public function getEventsByKeyword($keyword, $altKeyword='', $city='', $numEvents=12, $dayStart='', $dayEnd='', $offset='')
	{
		$keyword= strtolower(trim($keyword));
		$altKeyword = strtolower(trim($altKeyword));
		$cy 	= (!empty($city))? " AND (event_city_name ='$city' or event_country_name ='$city' or event_region_name ='$city') ":"";
		$start 	= !empty($dayStart)? " AND event_start_time >= '".date('Y-m-d',strtotime($dayStart))."'":'';
		$end 	= !empty($dayEnd)? " AND event_start_time <= '".date('Y-m-d',strtotime($dayEnd))."'":'';
		$limit 	= ($numEvents > 0)?" LIMIT $numEvents ":'';
		$offset = !empty($offset)? " OFFSET  $offset ":'';
		$aQuery = "SELECT * FROM events WHERE event_keyword = '$keyword' $cy $start $end order by event_start_time ASC $limit $offset"; 
		$aResults = $this->_db->select($aQuery);
		$result = array();

		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
	
		if(sizeof($result)==0) 
		{
			if ($this->getEventsOnline($keyword, $city, $numEvents, $dayStart, $dayEnd))
				$result = $this->getEventsByKeyword($keyword, '', $city, $numEvents, $dayStart, $dayEnd);
			elseif ($altKeyword != $keyword && !empty($altKeyword))
				$result = $this->getEventsByKeyword($altKeyword, '', $city, $numEvents, $dayStart, $dayEnd);
		}

		return $result;
	}
	
	public function getEventById($event_id)
	{
		$aQuery = "SELECT * FROM events WHERE event_id = '".$event_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		
		return $pRow;
	}
	
	/**
	 * get Events
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numEvants Events to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numEvents,$extraParams = array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		
		$event_id 	= (isset($extraParams['event_id'])&&!empty($extraParams['event_id']))?$extraParams['event_id']:0;
		$city 		= isset($extraParams['city'])?trim($extraParams['city']):'';
		$dayStart 	= isset($extraParams['dayStart'])?trim($extraParams['dayStart']):date('Y-m-d');
		$dayEnd 	= isset($extraParams['dayEnd'])?trim($extraParams['dayEnd']):date('Y-m-d', strtotime('+ 1 month'));
		$alterkw	= (isset($extraParams['orign_keyword'])&&!empty($extraParams['orign_keyword']))?$extraParams['orign_keyword']:'';
				
		foreach($sources as $n => $source) 
		{
			$source = strtolower($source);
				switch ($source) {
					case 'eventful': 
								if (!empty($event_id)) {
									$data[] = $this->getEventById($event_id);
							   	} else {
									//echo "<br />$keyword, $city, $numEvents, $dayStart, $dayEnd";
									$data = $this->getEventsByKeyword($keyword, $alterkw, $city, $numEvents, $dayStart, $dayEnd); 
							   	}
							   	break;
				}
		}
		
		return $data;
	}
	
	public function getEventsOnline($keyword, $location='United States', $numEvents=12, $dayStart='', $dayEnd='')
	{
		$keyword 	= strtolower(trim($keyword)); 
		$location 	= strtolower(trim($location));
		$start 		= date('Ymd00',strtotime($dayStart));
		$end 		= date('Ymd00',strtotime($dayEnd));
		$period 	= $start.'-'.$end;
		
		$request = "http://api.eventful.com/rest/events/search?app_key=".$this->app_key."&user=".$this->user."&password=".$this->password."&keywords=".urlencode($keyword)."&location=".urlencode($location)."&date=".$period."&page_size=".$numEvents;

		//$result = file_get_contents($request);
		$this->_curlObj->createCurl('get',$request);
		$result = $this->_curlObj->__toString();
		$xml = !empty($result)?simplexml_load_string($result):'';
		
		$flag = false;
		if (!empty($xml) && !empty($xml->events->event))
		{
			foreach ($xml->events->event as $key => $value )
			{
				 $event_id 		= $value['id'];
				 $title 		= mysql_real_escape_string($value->title);
				 $event_url 	= $value->url;
				 $description 	= mysql_real_escape_string($value->description);
				 $start_time 	= strtotime($value->start_time);
				 $start_time 	= !empty($start_time)? $start_time:strtotime(date('Y-m-d H:i:s')); 
				 $stop_time 	= !empty($value->stop_time)? strtotime($value->stop_time):'';
				 $venue_url 	= $value->venue_url;
				 $venue_name 	= mysql_real_escape_string($value->venue_name);
				 $venue_address = $value->venue_address;
				 $city_name 	= $value->city_name;
				 $region_name 	= $value->region_name;
				 $postal_code 	= $value->postal_code;
				 $country_name 	= $value->country_name;
				 $latitude 		= $value->latitude;
				 $longitude 	= $value->longitude;
				 $image_url 	= $value->image->medium->url;
				 $image_width 	= $value->image->medium->width;
				 $image_height 	= $value->image->medium->height;			
			
				 $earray = array( 'event_eventful_id' => $event_id
				 				, 'event_title' => $title
				 				, 'event_description' => $description
				 				, 'event_keyword' => $keyword
				 				, 'event_url' => $event_url
				 				, 'event_start_time' =>$start_time
				 				, 'event_venue_url' =>$venue_url
				 				, 'event_venue_name' =>$venue_name
				 				, 'event_venue_address' =>$venue_address
				 				, 'event_city_name' =>$city_name
				 				, 'event_region_name' =>$region_name
				 				, 'event_postal_code' =>$postal_code
				 				, 'event_country_name' =>$country_name
				 				, 'event_latitude' =>$latitude
				 				, 'event_longitude' =>$longitude
				 				, 'event_image_url' =>$image_url
				 				, 'event_image_width' =>$image_width
				 				, 'event_image_height' =>$image_height
				 				); 
				 if (!empty($stop_time))
				 	$earray['event_stop_time'] =  $stop_time;
				 				
				 $result = $this->saveEvents($earray);
				 
				 if($result)
				      $flag = true;
				 	
			} 
		}
		return $flag;
	}
	
	
	public function saveEvents($array)
	{	
		$result = $this->_db->select_one("SELECT * FROM events WHERE event_keyword like '".$array['event_keyword']."' and event_eventful_id='".$array['event_eventful_id']."'");
		if(!$result){	
			return $id = $this->_db->insert_array('events', $array);
		}
		else
			return false;
	}
	
}