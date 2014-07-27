<?php
/**	APPLICATION:	SX25
*	FILE:			XmlAction.php
*	DESCRIPTION:	front end - XmlAction class
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

/**
 * Xml parse ...
 * $this->_xmlAction->parse($strInputXML);
 * 
 * @param <string> $strInputXML;
 * @return <array> 
 */

class XmlAction_Class
{
   public $arrOutput = array();

   public $resParser;

   public $strXmlData;
   
   public function parse($strInputXML) {
		   $this->resParser = xml_parser_create ();

		   xml_set_object($this->resParser,$this);

		   xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
		  
		   xml_set_character_data_handler($this->resParser, "tagData");

		   $this->strXmlData = xml_parse($this->resParser,$this->code2code($strInputXML));
	
		   if(!$this->strXmlData) {

			   die(sprintf("XML error: %s at line %d",

		   xml_error_string(xml_get_error_code($this->resParser)),

		   xml_get_current_line_number($this->resParser)));

	   }

	   xml_parser_free($this->resParser);
	  
	   return $this->arrOutput;
   }

   private function tagOpen($parser, $name, $attrs) {

	   $tag=array("name"=>$name,"attrs"=>$attrs);

	   array_push($this->arrOutput,$tag);

   }

   private function tagData($parser, $tagData) {

	   if(trim($tagData)) {
		   if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {

			   $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
		   }
		   else {

			   $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
		   }
	   }
   }
   
	private function tagClosed($parser, $name) {
	
	   $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
	
	   array_pop($this->arrOutput);
	
	}

	private function code2code($data)
	{
		$trans = array(
		  '&lt;'=>'&#60;',
		  '&gt;'=>'&#62;',
		  '&amp;'=>'&#38;');
		return strtr($data, $trans);
	}
}