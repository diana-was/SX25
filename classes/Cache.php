<?php 
/**	APPLICATION:	SX25/News.php
*	FILE:			Cache.php
*	DESCRIPTION:	cache news content for 24 hours
*	CREATED:		15 October 2010 by Gordon Ye
*	UPDATED:									
*/



class Cache_Class
{
   
    private $db;   
    
    public function __construct(db_class $db) {
		$this->_db = $db;	
    }
	
	public function __set($property, $value){
		$this->$property = $value;
	}
	
	public function __get($property){
		if (isset($this->$property)){
			return $this->$property;
		}
	}
	
	public function getcache($keyword, $hours = 24) {
		$keyword = strtolower($keyword);
		$pQuery = "SELECT * FROM news WHERE keyword='".$keyword."' LIMIT 1";
		$pResults = $this->_db->select($pQuery);
		if(!$news=$this->_db->get_row($pResults)){			
				return false;
		}      
        //Expired cache
        if(strtotime($news['created_date']) < (time()-$hours*3600)) {
            $this->removecache($news['id']);
            return false;
        }
        
        //Get contents
        return stripslashes($news['content']);
    }
    
    
    
    /**
    * Remove specific cache file
    * Wildcard * supported to match any ENDING
    * @return void
    */
    public function removecache($id) {
		$pQuery = "DELETE FROM news WHERE id='".$id."' ";
		$pResults = $this->_db->delete($pQuery);
    }
    
    
    /**
    * Write cache file
    * @return bool
    */
    public function writecache($keyword, $content) {
		$keyword = strtolower($keyword);
		$content = addslashes($content);
		$pQuery = "INSERT INTO news (keyword, content) VALUES ('$keyword','$content')";
		$pResults = $this->_db->insert_sql($pQuery);
    }
	
	 public function update_sql($keyword, $content) {
		$content = addslashes($content);
		$current = time();
		$pQuery = "UPDATE news SET content='$content', created_date='$current' WHERE keyword = '$keyword'";
		$pResults = $this->_db->insert_sql($pQuery);
    }
    
    
    /**
    * Safen up file name. All other methods use this, ensures consistent access
    * @return string
    */
    private function filename($file) {
        $file = strtolower($file);
        $file = preg_replace('~[^a-z0-9/\\_\-]~', '', $file);
        return $file;    
    }
}

?>