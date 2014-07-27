<?php
/**	APPLICATION:	SX25
*	FILE:			Visitor.php
*	DESCRIPTION:	display visitor data from database
*	CREATED:		20 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Visitor_Class extends Model_Class
{
	private 	$_db;
	private static $_Object; 
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db)
	{
		$this->_db = $db; 
		self::$_Object = $this;
		return self::$_Object;
	}


    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance(db_class $db) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }

    
	public function get_visitorId($email,$from_domain) 
	{
		if($id = $this->_db->select_one("select id From visitors where email = '$email' and from_domain = '$from_domain' limit 1")) 
		{
			return $id;
		}
		else
		{
			return false;
		}
	}

    
	public function get_visitors($from_domain)
	{
		$output = array();
		$sql = "select * From visitors where from_domain = '$from_domain' ORDER BY email ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC'))
		{
			$array=array();
			if (!empty($row['interest']))
			{
				$set = json_decode($row['interest']);
				foreach($set as $member=>$data)
				{
					$array[$member]=$data;
				}
			}
			$row['interest'] = $array;
			$output[] = $row;
		}
		return $output;
	}

	public function get_visitorDomains($email) 
	{
		$output = array();
		$sql = "select from_domain From visitors where email = '$email' ORDER BY from_domain ";

		$result = $this->_db->select($sql);
		while($row = $this->_db->get_row($result, 'MYSQL_ASSOC')){
			$output[] = $row['from_domain'];
		}
		return $output;
	}
	
	public function get_visitorInfo($id) 
	{
		$output = array();
		$sql = "select * From visitors where id = '$id' Limit 1 ";

		$result = $this->_db->select($sql);
		if($row = $this->_db->get_row($result, 'MYSQL_ASSOC'))
		{
			$array=array();
			if (!empty($row['interest']))
			{
				$set = json_decode($row['interest']);
				foreach($set as $member=>$data)
				{
					$array[$member]=$data;
				}
			}
			$row['interest'] = $array;
			$output = $row;
		}
		return $output;
	}
	
	/*
	 *  Save the visitord data in the visitors table
	 *  
	 *  array = an array with the field => value. Special field interest is an array stored in json format.
	 *  id  = id to update, 0 if new visitor
	 *  
	 *  return id or false if fail
	 */
	public function save_visitor($array, $id=0)
	{
		if (empty($array['email']) || empty($array['from_domain']) ||  (!empty($array['interest']) && !is_array($array['interest'])))
			return false;

		$idR = $this->get_visitorId($array['email'], $array['from_domain']);
		$id = empty($id)?$idR:$id;
				
		if (isset($array['interest']))
		{
			if (!empty($idR))
			{
				$info = $this->get_visitorInfo($idR);
				$interest = empty($array['interest'])?$info['interest']:array_merge($info['interest'],$array['interest']);
			}
			else
			{
				$interest = empty($array['interest'])?array():$array['interest'];
			}
			foreach ($interest as $k => $v)
			{
				if (empty($v))
					unset($interest[$k]);
			}
			$array['interest'] = json_encode($interest);
		}
		
		if(empty($id))
		{
			$id = $this->_db->insert_array('visitors', $array);
		}
		else
		{
			if ($idR != $id)
			{
				$this->del_visitor($id);
				$id = $idR;
			}
			$this->_db->update_array('visitors', $array, "id='".$id."'");
		}
		return $id;
	}

	public function del_visitor($id)
	{
		$dQuery = "DELETE FROM visitors WHERE id = '$id'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}
    
}