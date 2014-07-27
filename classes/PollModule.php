<?php
/**	APPLICATION:	SX25
*	FILE:			pollModule
*	DESCRIPTION:	Interactive with database - it means a  database
*	CREATED:		18 October 2010 Gordon Ye
*	UPDATED:
*   USAGE:          communicate database tables votes and polls.
*/

class PollModule_Class extends  Module_Class
{
	protected	$moduleName = 'Poll';
	protected	$sourceList = array('db');
	protected	$settings   = array();
	
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
		$layout = ' <!-- POLL Module -->
                    <div class="modulePoll" id="poll">                                                          

                    <table align="center" width="280px" cellspacing="2" cellpadding="4" border="0" bgcolor="#FFFFFF">
					<form name="poll_form" action="functions.php"><tbody>
					
					<tr bgcolor="#cd6839"><td align="center"><font size="-1" face="Verdana" color="#FFFFFF"><b>What would you like for lunch today ?</b></font></td></tr>
					
					<tr><td align="left">
					<table><tbody>
					
					<tr><td align="right"><input type="radio" value="Japanese" name="pr"></td><td><font size="-1" face="Verdana" color="#000000">Japanese</font></td></tr>
					
					<tr><td align="right"><input type="radio" value="Thai" name="pr"></td><td><font size="-1" face="Verdana" color="#000000">Thai</font></td></tr>
					
					<tr><td align="right"><input type="radio" value="Vietnamese" name="pr"></td><td><font size="-1" face="Verdana" color="#000000">Vietnamese</font></td></tr>
					
					<tr><td align="right"><input type="radio" value="Other" name="pr"></td><td><font size="-1" face="Verdana" color="#000000">Other</font></td></tr>
					
					
					</tbody></table>
					</td></tr>
					
					<tr><td align="center">
					        <input type="submit" value="Vote" class="wgpollbutton" onclick="submit_poll();"> 
					
					        <input type="hidden" name="action" value="submit_poll"> 
					        <input type="hidden" name="type" value="radio">
					        <input type="hidden" name="options" value="Japanese,Thai,Vietnamese,Other">
					        <input type="hidden" name="domain_id" value="{DOMAIN_ID}" />
					        <input type="hidden" name="module_layout_id" value="32" />
					        <input type="hidden" name="referer" value="{REFERER_PAGE}" />
					
					
					</td></tr>
					<tr><td align="center"><div class="errorText" id="MssgTxt1725"></div></td></tr>
					</tbody>
					</form>
					</table>
					
					{POLL_RESULT}
					</div>
					';
		    $js = '';
		    $css = '
		    .wgpollbutton {
			    background-color: #92B901;
			    color: #FFFFFF;
			    font: bold 11px Verdana;
			}
			samples.asp #4 (line 511)
			input {
			    margin-bottom: 2px;
			    margin-top: 2px;
			}
				
			#poll_content{display: block; width: 280px; text-align: left; float: left;}
		    ';
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
    
	public function isVoted($module_layout_id,$flagVote=false)
	{
		$idList = (isset($_COOKIE["voted"]) && !empty($_COOKIE["voted"]))?$_COOKIE["voted"]:'';
		$ids = explode(',',$idList);
		$voted = in_array($module_layout_id, $ids);
		
		if ($flagVote)
		{
			if (!$voted)
				$ids[] = $module_layout_id;
			setcookie("voted", implode(',',$ids), time() + 3600);
		}
		return $voted;
	}
	
	/**
	 *
	 *set active 0 in votes table
	 *
	 */
	public function closeDomainPoll($domain_id, $module_layout_id)
	{    
		$aQuery = "UPDATE poll_result SET active='0' WHERE domain_id ='".$domain_id."' AND module_layout_id ='".$module_layout_id."' ";
		$this->_db->update_sql($aQuery);
		return ;
	}

	public function getPollId($domain_id, $module_layout_id) 
	{
		if($id = $this->_db->select_one("SELECT poll_result_id FROM poll_result WHERE domain_id ='".$domain_id."' AND module_layout_id ='".$module_layout_id."' limit 1")) 
		{
			return $id;
		}
		else
		{
			return false;
		}
	}
	
	public function getPollResultById($domain_id, $module_layout_id)
	{
		$cQuery = "SELECT poll_result FROM poll_result WHERE domain_id ='".$domain_id."' AND module_layout_id ='".$module_layout_id."' ";
		$cResults = $this->_db->select_one($cQuery);
		$pollArray = unserialize($cResults);
		return $pollArray;
	}
	
	public function savePollResultById($poll_new, $domain_id, $module_layout_id)
	{
		if (empty($domain_id) || empty($module_layout_id))
			return false;

		$id = $this->getPollId($domain_id, $module_layout_id);
		$poll_results = serialize($poll_new);
		if(empty($id))
		{
			$cQuery = "INSERT INTO poll_result (poll_result, domain_id, module_layout_id) VALUES ('".$poll_results."','".$domain_id."','".$module_layout_id."') ";
			$id = $this->_db->insert_sql($cQuery);
		}
		else
		{
			$cQuery = "UPDATE poll_result SET poll_result='".$poll_results."' WHERE poll_result_id ='$id' ";
			$cResults = $this->_db->update_sql($cQuery);	
		}
		return $id;
	}
	
	/**
	 * get Poll
	 * 
	 * @param array   $extraParams domain_id etc. 
	 * 
	 * @return array
	 */
	public function getData($keyword,$sources,$numPolls,$extraParams=array())
	{
		$domain_id 			= isset($extraParams['domain_id'])?$extraParams['domain_id']:0;
		$module_layout_id 	= isset($extraParams['module_layout_id'])?$extraParams['module_layout_id']:0;
		$polls 				= $this->getPollResultById($domain_id, $module_layout_id);
		return $polls;
	}
	
}

?>
