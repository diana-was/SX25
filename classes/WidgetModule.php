<?php
/**	APPLICATION:	SX25
*	FILE:			topicWidget Class
*	DESCRIPTION:	generate relative topic list which usually display on side bar.
*	CREATED:		04 Jan 2011 Gordon Ye
*	UPDATED:
*   USAGE:          generate relative topic.
*/

class WidgetModule_Class extends  Module_Class
{
	protected	$moduleName = 'Widget';
	protected	$sourceList = array();
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
		$layout = '<!-- Widget Module -->
					<div class="head">
					<h2>Subscribe Newsletter</h2>
					</div>
					<div class="mcontent">               	
					    <div class="etf" style="height:28px;">
					        <input type="text" value="fill email here" style="width: 250px;" id="semail2" name="email">
					        <input type="hidden" value="{SUBSCRIBE_DOMAIN}" name="domain">
					        <button id="send_email" class="send_button" style="margin-left:10px">Subscribe</button>
					    </div>
					    <div id="msg4" class="etf" style="color:red"></div>                    
					</div>';
		$js = '	
				$("#semail2").focus(function() {
					$("#semail2").val("");
				});
				
				$("#send_email").click(function() {
					var semail= $("#semail2").val();
					if(!echeck(semail)|| semail=="") {
						var err ="Please fill a valid email.<br />";
						$("#msg4").html(err).show().fadeIn("slow");
					        setTimeout(function () {$("#msg4").fadeOut("slow");}, 3000);			
					}else{
						$(".send_button").hide(300);
						$.post("functions.php?email="+semail+"&action=subscribe_news", function(data){
						$("#msg4").html("Thank you very much for your subscription.").show().fadeIn("slow");
						setTimeout(function () {$("#msg4").fadeOut("slow");}, 3000);			
						});
					}
					return false;
				});
				
				function echeck(str) {
					 var at="@";
					 var dot=".";
					 var lat=str.indexOf(at);
					 var lstr=str.length;
					 var ldot=str.indexOf(dot);
					 if (str.indexOf(at)==-1){
					 return false;
					 }
					 if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
					 return false;
					 }
					 if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
					 return false;
					 }
					 if (str.indexOf(at,(lat+1))!=-1){
					 return false;
					 }
					 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
					 return false;
					 }
					 if (str.indexOf(dot,(lat+2))==-1){
					 return false;
					 }
					 if (str.indexOf(" ")!=-1){
					 return false;
					 }
					 return true;
				} 			';
			$css = '';			
	    	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['layout_js'] 	= $js;
	        $this->layout['layout_css'] = $css;
	        $this->layout['settings']	= array('perPage' => 1);
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_js`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($js)."','".$this->__encodeTags($css)."')";
	        $this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
	
	public function getData($keyword,$sources,$numRss,$extraParams=array())
	{		
		return '';
	}
	
}

?>