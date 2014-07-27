<?php
/**	APPLICATION:	SX25
*	FILE:			articleModule
*	DESCRIPTION:	display domain - ArticleModule_Class read articles from database
*	CREATED:		18 October 2010 by Diana De vargas
*	UPDATED:									
*/

class ArticleModule_Class extends  Module_Class
{
	protected	$moduleName = 'Article';
	protected	$sourceList = array('db','all');
	protected	$MaxArticles = 20;
	
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
     * Get the VideoModule static object
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
			<!-- Article Module -->
			<div class="moduleArticle">
				<h6>{ARTICLE_TITLE}</h6>
				<div>{ARTICLE_CONTENT}</div>
			</div>';
			$css = '
					/*Module article Div*/
					.moduleArticle {
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
					.moduleArticle h6 {
					    margin: 0 0 10px 0px;
					    font-style:normal;
					    font-size:1.2em;
					    font-weight: bold;
					}
					
					/*content*/
					.moduleArticle div {
					}
			';
			$js = '';
			$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 1, 'sources' => $this->sourceList);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
    public function getOneArticleByDomain($domain_id,$keyword,$default='',$offset='',$exclude=array())
	{
		$data = $this->getArticlesByDomain($domain_id,$keyword,1,$default,$offset,$exclude);

		if (is_array($data) && count($data) > 0) {
			$pRow = $data[0];
		} else {
			$pRow = array();
			$pRow['article_id'] 	= '';
			$pRow['article_title'] 	= '';
			$pRow['article_summary']= '';
			$pRow['article_content']= '';
		}
		
		return $pRow;
	}

	public function getArticlesByDomain($domain_id,$keyword,$numArticles=1,$default='',$offset='',$exclude=array())
	{
		$limit = ($numArticles > 0)?" LIMIT $numArticles ":'';
		$limit .= (!empty($limit) && !empty($offset))?" OFFSET $offset ":'';
		$andExclude = empty($exclude)?'':' AND article_id not in ('.implode(',',$exclude).')';
		if ($default!='') 
		{
			$search = $default?'1':'0';
			$aQuery = "SELECT * FROM articles WHERE article_domain_id='".$domain_id."' AND article_keyword like '$keyword' AND article_default = $search $andExclude $limit ";
			
			$aResults = $this->_db->select($aQuery);
			if(!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) 
			{
				$search = $default?'0':'1';
				$aQuery = "SELECT * FROM articles WHERE article_domain_id='".$domain_id."' AND article_keyword like '$keyword' AND article_default = $search $andExclude $limit ";
				$aResults = $this->_db->select($aQuery);
				if(!$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC')){
					$search = $default?'1':'0';
					$aQuery = "SELECT * FROM articles WHERE article_domain_id='".$domain_id."' AND article_default = $search $andExclude $limit";
					$aResults = $this->_db->select($aQuery);
					$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
				}
			}
		}
		else
		{
			$aQuery = "SELECT * FROM articles WHERE article_domain_id='".$domain_id."' AND article_keyword like '$keyword' $andExclude $limit ";
			
			$aResults = $this->_db->select($aQuery);
			if((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) || ($numArticles > 0 && $this->_db->row_count < $numArticles)) 
			{
				$aQuery = "SELECT * FROM articles WHERE article_domain_id='".$domain_id."' $andExclude $limit";
				$aResults = $this->_db->select($aQuery);
				$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
			}
		}
		$result = array();
		if (isset($aRow) && !empty($aRow)) {
			$result[] = $aRow;
		}
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		return $result;
	}
	
	public function getArticleById($article_id)
	{
		$aQuery = "SELECT * FROM articles WHERE article_id='".$article_id."' ";
		$aResults = $this->_db->select($aQuery);
		$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
		
		$pRow = array();
		if ($aRow)
		{
			$pRow['article_id'] 	 = @$aRow['article_id'];
			$pRow['article_title'] 	 = @$aRow['article_title'];
			$pRow['article_summary'] = @$aRow['article_summary'];
			$pRow['article_content'] = @$aRow['article_content'];
		}
		return $pRow;
	}

	public function getArticlesByKeyword($keyword,$numArticles=1,$offset='')
	{
		$limit = ($numArticles > 0)?" LIMIT $numArticles ":' LIMIT  '.$this->MaxArticles.' ';
		$limit .= (!empty($limit) && !empty($offset))?" OFFSET $offset ":'';

		/* Search full text */
		$aQuery = "SELECT * FROM articles WHERE MATCH (article_keyword,article_title,article_content) AGAINST ('$keyword') $limit ";
		$aResults = $this->_db->select($aQuery);

		if((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) || ($numArticles > 0 && $this->_db->row_count < $numArticles)) 
		{
			/* Search keyword */
			$aQuery = "SELECT * FROM articles WHERE article_keyword like '$keyword' $limit ";
			$aResults = $this->_db->select($aQuery);
			
			/* Search ALL */
			if((!$aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) || ($numArticles > 0 && $this->_db->row_count < $numArticles)) 
			{
				$aQuery = "SELECT * FROM articles WHERE MATCH (article_keyword,article_title,article_content) AGAINST ('$keyword'  WITH QUERY EXPANSION) $limit ";
				$aResults = $this->_db->select($aQuery);
				$aRow=$this->_db->get_row($aResults, 'MYSQL_ASSOC');
			}
		}
		$result = array();
		if (isset($aRow) && !empty($aRow)) {
			$result[] = $aRow;
		}
		while ($aRow = $this->_db->get_row($aResults, 'MYSQL_ASSOC')) {
			$result[] = $aRow;
		}
		/* Reorder results, first the articles with the keyword in the title */
		$ordered = array();
		$len = count($result);
		for ($i = 0; $i < $len; $i++)
		{
			if (stristr($result[$i]['article_title'],$keyword) !== FALSE)
			{
				$ordered[] = $result[$i];
				unset($result[$i]);
			}
		}
		$ordered = array_merge($ordered,$result);
		return $ordered;
	}
	
	
	public function getArticleComments($domain_id,$article_id,$maxComments=10)
	{
		$comment = Comment_Class::getInstance($this->_db);
		return $comment->get_comments('articles',$article_id,$domain_id,$maxComments);
	}
	
	/**
	 * get Articles
	 * 
	 * @param string  $keyword
	 * @param array   $sources list of sources to search from 
	 * @param integer $numArticles articles to download 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numArticles,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		// extra parameters	
		$domain_id 	= isset($extraParams['domain_id'])?$extraParams['domain_id']:0;
		$empty 		= isset($extraParams['empty'])?$extraParams['empty']:'';
		$article_id = isset($extraParams['article_id'])?$extraParams['article_id']:0;
		$offset		= isset($extraParams['offset'])?$extraParams['offset']:'';
		
		$numImg = round($numArticles/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch ($source) {
					case 'db': 	if ($article_id > 0) {
									$data = $this->getArticleById($article_id);
							   	} else {
									$data = array_merge($data,$this->getArticlesByDomain($domain_id,$keyword,$plus,$empty));
							   	}
							   	break;
					case 'all': if ($article_id > 0) {
									$data = $this->getArticleById($article_id);
							   	} else {
									$data = array_merge($data,$this->getArticlesByKeyword($keyword,$plus,$offset));
							   	}
							   	break;
				}
			}
		}
		
		return $data;
	}
	
}