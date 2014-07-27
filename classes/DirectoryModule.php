<?php
/**	APPLICATION:	SX25
*	FILE:			directoryModule
*	DESCRIPTION:	display domain - DirectoryModule_Class read directories from database
*	CREATED:		18 October 2010 by Diana De vargas
*	UPDATED:									
*/

class DirectoryModule_Class extends  Module_Class
{
	protected	$moduleName = 'Directory';
	protected	$sourceList = array('db','mydb');
	
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
			<!-- Directory Module -->
			<div class="moduleDirectory">
				<div class="MDTitle DivURLtop">{DIRECTORY_TITLE}</div>
				<div class="MDText DivURLtext">{DIRECTORY_DESCRIPTION}</div>
				<div class="MDLink DivURLbottom"><a class="URLbottom" href="business.php?Keyword={DIRECTORY_TITLE}&url={DIRECTORY_URL}&directory_id={DIRECTORY_ID}" target="_blank">{DIRECTORY_URL}</a></div>
			</div>';
			$css = '
					/*Module directory Div*/
					.moduleDirectory {
					    position: relative;
					    margin: 0 0 30px 10px;
					    padding: 10px 0 0;
					    clear:both;
					    display: block;
					    font-style:normal;
					    font-size:1em;
					    font-weight: normal;
					}
					
					/*title*/
					.MDTitle {
					    margin: 0 0 10px 0px;
					    font-style:normal;
					    font-size:1.2em;
					    font-weight: bold;
					}
					
					/*content*/
					.MDText {
					}
					
					/*link*/
					.MDLink {
					}
			';
			$js = '';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 6, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
    public function getOneDirectoryByKeyword($keyword, $start=0, $alterkw='')
	{
		$data = $this->getDirectoriesByKeyword($keyword, 1, $start, $alterkw);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = false;
		}
		
		return $pRow;
	}

	public function getDirectoriesByKeyword($keyword, $numDirectories=20, $start=0, $alterkw='',$source='db')
	{
		$keyword = strtolower(trim($keyword));
		$alterkw = strtolower(trim($alterkw));
		$limit = ($numDirectories > 0)?" LIMIT $numDirectories ":'';
		$offset = (!empty($start))?" OFFSET $start ":'';
		$only_flag = trim(strtolower($source)) == 'mydb';
		
		$aQuery = "SELECT * FROM directories WHERE directory_keyword like '$keyword' ORDER BY directory_flag DESC, RAND() $limit $offset";
		$aResults = $this->_db->select($aQuery);

		if (!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
		{
	    	$where = (empty($alterkw) || ($keyword == $alterkw))?' ORDER BY directory_flag DESC, RAND() ':" WHERE directory_keyword like '$alterkw' ORDER BY directory_flag DESC, directory_title ASC ";
		    $aQuery = "SELECT * FROM directories $where $limit $offset";
			$aResults = $this->_db->select($aQuery);
	
			if ((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) && !empty($alterkw) && ($keyword != $alterkw)) 
			{
				$aQuery = "SELECT * FROM directories ORDER BY directory_flag DESC, RAND() $limit $offset";
				$aResults = $this->_db->select($aQuery);
				$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC');
			}
		}
		
		$result = array();
		if (isset($aRow) && !empty($aRow) && (!$only_flag || $aRow['directory_flag'] == 1)) 
		{
			$aRow['content_source'] = $source;
			$result[] = $aRow;
		}
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
		{
			if (!$only_flag || $aRow['directory_flag'] == 1)
			{
				$aRow['content_source'] = $source;
				$result[] = $aRow;
			}
		}
		return $result;
	}
	
	public function getDirectoryById($directory_id)
	{
		$aQuery = "SELECT * FROM directories WHERE directory_id='".$directory_id."' ";
		$aResults = $this->_db->select($aQuery);
		$pRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		
		return $pRow;
	}
	
	/**
	 * get Directories
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numDirectories directories to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numDirectories,$extraParams = array())
	{
		$data = array();
		if (!is_array($sources)) 
		{ 
			$sources = array($sources);
		}
		// extra parameters			
		$domain_id 	 = isset($extraParams['domain_id'])?$extraParams['domain_id']:0;
		$directory_id = isset($extraParams['directory_id'])?$extraParams['directory_id']:0;
		$orign_keyword = isset($extraParams['orign_keyword'])?$extraParams['orign_keyword']:'';
		
		$numImg = round($numDirectories/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) 
		{
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) 
			{
				switch ($source) 
				{
					case 'mydb':
					case 'db': 	if ($directory_id > 0) 
									$data = $this->getDirectoryById($directory_id);
							   	else 
									$data 	= $this->getDirectoriesByKeyword($keyword, $plus, 0, $orign_keyword, $source);
							   	break;
					default :
							   	break;
				}
			}
		}
		
		return $data;
	}
	
}