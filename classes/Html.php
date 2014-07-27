<?php
/**
 * Html template class
 * Author: Archie Huang on 29/01/2009
 * 
 */
// Rss parse class
class Html{

	var $Count=0;
    
	var $Template;
	
    /**
     * constructor : 
     * @return object
     */
	public function __construct()
	{
	}
		
	public function parseHtml($resultBase,$array)
	{
		if(is_array($array) && !empty($resultBase))
		{
			$replaceArray['PAGETITLE'] 			= $array['domain_title'] != '' ? $array['domain_title'] : $array['domain_url'];
			$replaceArray['TITLE'] 				= $array['domain_title'] != '' ? $array['domain_title'] : $array['domain_url'];
			$replaceArray['META_TAG'] 			= '';
			$replaceArray['META_KEYWORDS'] 		= $array['page_keyword'].','.$array['page_relates'];
			$replaceArray['META_TITLE'] 		= $array['domain_title'] != '' ? $array['domain_title'] : $array['domain_url'];
			$replaceArray['TITLE_LINK'] 		= 'http://'.$array['domain_url'];
			$replaceArray['RESULTS_IMG'] 		= !empty($array['result_pic'])? $array['result_pic']:$array['page_pic'];
			$replaceArray['LANDING_IMG'] 		= !empty($array['landing_pic'])? $array['landing_pic']:$array['page_pic'];
			
			//extra menu
			$replaceArray['MENU'] 				= @$array['menu'];
			
			$relatesArray 						= explode(',', $array['page_relates']);
			$menusLink 							= $array['page_menus_link'];
			$menusDisplayArray 					= $array['page_menus_display'];
			$menusKeyword 						= $array['page_menus_keyword'];
			
			$default_pic = !empty($array['result_pic'])?$replaceArray['RESULTS_IMG']:$replaceArray['LANDING_IMG'];
			for($j=1;$j<10;$j++)
			{
				$replaceArray['M_IMG_'.$j] 				= (isset($array['menu_pic_'.$j]) && !empty($array['menu_pic_'.$j]))? $array['menu_pic_'.$j] : $default_pic;
				//extra magzine
				$replaceArray['IMG_'.$j] 				= (isset($array['page_pic_'.$j]) && !empty($array['page_pic_'.$j]))? $array['page_pic_'.$j] : $default_pic;
			}
			
			for($j=1;$j<13;$j++)
			{
				$replaceArray['RELATED'.$j] = isset($relatesArray[$j-1]) ? ucfirst($relatesArray[$j-1]) : '';
			}
			for($k=1;$k<16;$k++)
			{
				$replaceArray['MENU_LINK'.$k] 	= isset($menusLink[$k-1]) ? $menusLink[$k-1] : '';
				$replaceArray['MENU'.$k] 		= isset($menusDisplayArray[$k-1]) ? $menusDisplayArray[$k-1] : '';
				$replaceArray['MENU_KEYWORD'.$k]= isset($menusKeyword[$k-1]) ? $menusKeyword[$k-1] : '';
			}
			
			$resultBase = $this->replaceHtmlTags($resultBase,$replaceArray);
		}
		return $resultBase;
	}

	public function parseFinalHtml($resultBase,$array)
	{
		$controller = Controller_Class::getInstance();
		$config = Config_Class::getInstance();
		if(is_array($array) && !empty($resultBase))
		{
			$replaceArray['KEYWORDS'] 			= @$array['page_keyword'];
			$replaceArray['MAPPING_KEYWORD']	= @$array['page_mapping'];
			$replaceArray['DOMAIN_KEYWORD']		= @$array['domain_keyword'];
			$replaceArray['SEARCH_TERM'] 		= @$array['page_keyword'];
			$replaceArray['DOMAIN'] 			= @$array['domain_url'];
			$replaceArray['COPYRIGHT'] 			= '&copy;'.date('Y').' '.$array['domain_url'].' All rights reserved.';
			$replaceArray['DOMAIN_ID']			= @$array['domain_id'];
			$replaceArray['REFERER_PAGE']		= $_SERVER['REQUEST_URI'];
			$replaceArray['HOME']				= $controller->baseURL;
			$replaceArray['THEME']				= $config->layoutTheme.$array['layout_folder'].(empty($array['layout_folder'])?'':'/');
			$replaceArray['THEME_IMAGE']		= $config->layoutTheme.$array['layout_folder'].(empty($array['layout_folder'])?'':'/').'images/';
			$replaceArray['CSS_LIBRARY']		= $config->cssLibrary;
			$replaceArray['ROOT_DOMAIN']		= $controller->root_domain;
			$replaceArray['CALL_PAGE']			= $controller->page;
			// replace image in sponsored listings
			$replaceArray['RESULTS_IMG'] 		= !empty($array['result_pic'])? $array['result_pic']:$array['page_pic'];
			$replaceArray['LANDING_IMG'] 		= !empty($array['landing_pic'])? $array['landing_pic']:$array['page_pic'];
			
			// Articles
			$replaceArray['ARTICLE_TITLE'] 		= isset($array['article_title'])?$array['article_title'] : '';
			$replaceArray['ARTICLE_SUMMARY']	= isset($array['article_summary'])?$array['article_summary'] : '';
			$replaceArray['ARTICLE_CONTENT']	= isset($array['article_content'])?$array['article_content'] : '';
			$replaceArray['ARTICLE_ID'] 		= isset($array['article_id'])?$array['article_id'] : '';
			$replaceArray['ARTICLE_KEYWORD'] 	= isset($array['article_keyword'])?$array['article_keyword'] : '';
			for($j=1;$j<10;$j++)
			{
				$replaceArray['ARTICLE_ID'.$j] 			= isset($array['article_id'.$j])? $array['article_id'.$j] : '';
				$replaceArray['M_ARTICLE_ID_'.$j] 		= isset($array['article_id'.$j])? $array['article_id'.$j] : '';
				$replaceArray['M_ARTICLE_TITLE_'.$j] 	= isset($array['menu_article_title_'.$j])? $array['menu_article_title_'.$j] : '';
				$replaceArray['M_ARTICLE_SUMMARY_'.$j] 	= isset($array['menu_article_summary_'.$j])? $array['menu_article_summary_'.$j] : '';
				$replaceArray['M_ARTICLE_KEYWORD_'.$j] 	= isset($array['menu_article_keyword_'.$j])? $array['menu_article_keyword_'.$j] : '';
			}
			
			// comments in old SX2
			$replaceArray['COMMENTS']			= isset($array['article_comments'])?$array['article_comments']:'';
			$replaceArray['ANSWERS']			= isset($array['question_answers'])?$array['question_answers']:'';
			
			$replaceArray['KEYWORD_URL']		= @urlencode($array['page_keyword']);
			
			// sponsors
			for ($d=0; $d<=10; $d++)
			{
				$replaceArray["SPONSOR_LISTINGS_$d"]	= isset($array["SPONSOR_LISTINGS_$d"])?$array["SPONSOR_LISTINGS_$d"]:'';
			}
			
			// Directory entries
			for($d=1;$d<10;$d++)
			{
				$replaceArray['DIRECTORY_TITLE_'.$d] 	= isset($array['DIRECTORY_TITLE_'.$d])? $array['DIRECTORY_TITLE_'.$d] : '';
				$replaceArray['DIRECTORY_ID_'.$d] 		= isset($array['DIRECTORY_ID_'.$d])? $array['DIRECTORY_ID_'.$d] : '';
				$replaceArray['DIRECTORY_DESCRIPTION_'.$d] 	= isset($array['DIRECTORY_DESCRIPTION_'.$d])? $array['DIRECTORY_DESCRIPTION_'.$d] : '';
				$replaceArray['DIRECTORY_URL_'.$d] 		= isset($array['DIRECTORY_URL_'.$d])? $array['DIRECTORY_URL_'.$d] : '';
				$replaceArray['DIRECTORY_IMG_'.$d] 		= isset($array['DIRECTORY_IMG_'.$d])? $config->imageLibrary.$array['DIRECTORY_IMG_'.$d] : '';
				$replaceArray['DIRECTORY_FLAG_'.$d] 	= isset($array['DIRECTORY_FLAG_'.$d])? $array['DIRECTORY_FLAG_'.$d] : 0;
			}
			
			// Event entries
			$replaceArray["EVENT_ID"] 				    = @$array['event_id'];
			$replaceArray["EVENT_EVENTFUL_ID"]		    = @$array['event_eventful_id'];
			$replaceArray["EVENT_TITLE"] 	            = @$array['event_title'];
			$replaceArray["EVENT_KEYWORD"] 			    = @$array['event_keyword'];
			$replaceArray["EVENT_DESCRIPTION"] 		    = @$array['event_description'];
			$replaceArray["EVENT_URL"] 		    		= @$array['event_url'];
			$replaceArray["EVENT_START_TIME"] 		    = @$array['event_start_time'];
			$replaceArray["EVENT_STOP_TIME"] 			= @$array['event_stop_time'];
			$replaceArray["EVENT_VENUE_NAME"] 			= @$array['event_venue_name'];
			$replaceArray["EVENT_VENUE_URL"] 			= @$array['event_venue_url'];
			$replaceArray["EVENT_VENUE_ADDRESS"] 		= @$array['event_venue_address'];
			$replaceArray["EVENT_CITY_NAME"] 			= @$array['event_city_name'];
			$replaceArray["EVENT_IMG"] 	                = @$array['event_image_url'];
			$replaceArray["EVENT_IMG_WIDTH"] 			= @$array['event_image_width'];
            $replaceArray["EVENT_IMG_HEIGHT"] 			= @$array['event_image_height'];

			for($d=1;$d<13;$d++)
			{
				$replaceArray["EVENT_ID_$d"] 			= isset($array['event_id_'.$d])? $array['event_id_'.$d] : '';
				$replaceArray["EVENT_EVENTFUL_ID_$d"] 	= isset($array['event_eventful_id_'.$d])? $array['event_eventful_id_'.$d] : '';
				$replaceArray['EVENT_TITLE_'.$d] 		= isset($array['event_title_'.$d])? $array['event_title_'.$d] : '';
				$replaceArray["EVENT_KEYWORD_$d"] 		= isset($array['event_keyword_'.$d])? $array['event_keyword_'.$d] : '';
				$replaceArray['EVENT_DESCRIPTION_'.$d] 	= isset($array['event_description_'.$d])? $array['event_description_'.$d] : '';
				$replaceArray["EVENT_URL_$d"] 			= isset($array['event_url_'.$d])? $array['event_url_'.$d] : '';
				$replaceArray['EVENT_START_TIME_'.$d] 	= isset($array['event_start_time_'.$d])? $array['event_start_time_'.$d] : '';
				$replaceArray['EVENT_STOP_TIME_'.$d] 	= isset($array['event_stop_time_'.$d])? $array['event_stop_time_'.$d] : '';
				$replaceArray['EVENT_VENUE_NAME_'.$d] 	= isset($array['event_venue_name_'.$d])? $array['event_venue_name_'.$d] : '';
				$replaceArray['EVENT_VENUE_URL_'.$d] 	= isset($array['event_venue_url_'.$d])? $array['event_venue_url_'.$d] : '';
				$replaceArray['EVENT_VENUE_ADDRESS_'.$d]= isset($array['event_venue_address_'.$d])? $array['event_venue_address_'.$d] : '';
				$replaceArray['EVENT_CITY_NAME_'.$d] 	= isset($array['event_city_name_'.$d])? $array['event_city_name_'.$d] : '';
				$replaceArray['EVENT_IMG_'.$d] 			= isset($array['event_image_url_'.$d])? $array['event_image_url_'.$d] : '';
				$replaceArray['EVENT_IMG_WIDTH_'.$d] 	= isset($array['event_image_width_'.$d])? $array['event_image_width_'.$d] : '';
				$replaceArray['EVENT_IMG_HEIGHT_'.$d] 	= isset($array['event_image_height_'.$d])? $array['event_image_height_'.$d] : '';
			}
			
			// Question & Answer entries
			$replaceArray["QUESTION_ID"] 				= @$array['question_id'];
			$replaceArray["QUESTION_KEYWORD"] 			= @$array['question_keyword'];
			$replaceArray["QUESTION_CONTENT"] 		    = @$array['question_content'];
			$replaceArray["QUESTION_SUBJECT"] 	        = @$array['question_subject'];
			$replaceArray["QUESTION_DATE"] 			    = @$array['question_date'];
			$replaceArray["QUESTION_USERNAME"] 			= @$array['question_username'];
			$replaceArray["QUESTION_USER_PHOTO"] 		= @$array['question_user_photo'];
			$replaceArray["QUESTION_ANSWER"] 			= @$array['question_answer'];
			$replaceArray["QUESTION_ANSWER_SUMMARY"] 	= @$array['question_answer_summary'];
			$replaceArray["QUESTION_ANSWERER"] 			= @$array['question_answerer'];
			$replaceArray["QUESTION_COMMENTS"] 			= @$array['question_comments'];
			
			for($d=1;$d<10;$d++)
			{
				$replaceArray["QUESTION_ID_$d"] 		= isset($array['question_id_'.$d])? $array['question_id_'.$d] : '';
				$replaceArray["QUESTION_KEYWORD_$d"] 	= isset($array['question_keyword_'.$d])? $array['question_keyword_'.$d] : '';
				$replaceArray['QUESTION_CONTENT_'.$d] 	= isset($array['question_content_'.$d])? $array['question_content_'.$d] : '';
				$replaceArray['QUESTION_SUBJECT_'.$d] 	= isset($array['question_subject_'.$d])? $array['question_subject_'.$d] : '';
				$replaceArray['QUESTION_DATE_'.$d] 		= isset($array['question_date_'.$d])? $array['question_date_'.$d] : '';
				$replaceArray['QUESTION_USERNAME_'.$d] 	= isset($array['question_username_'.$d])? $array['question_username_'.$d] : '';
				$replaceArray['QUESTION_USER_PHOTO_'.$d]= isset($array['question_user_photo_'.$d])? ((stristr($array['question_user_photo_'.$d], 'http:')=== false)?$config->imageLibrary:'').$array['question_user_photo_'.$d] : '';
				$replaceArray['QUESTION_ANSWER_'.$d] 	= isset($array['question_answer_'.$d])? $array['question_answer_'.$d] : '';
				$replaceArray['QUESTION_ANSWER_SUMMARY_'.$d] 	= isset($array['question_answer_summary_'.$d])? $array['question_answer_summary_'.$d] : '';
				$replaceArray['QUESTION_ANSWERER_'.$d] 	= isset($array['question_answerer_'.$d])? $array['question_answerer_'.$d] : '';
			}

			for($d=1;$d<15;$d++)
			{
				$replaceArray["ANSWER_ID_$d"] 			= isset($array['answer_id_'.$d])? $array['answer_id_'.$d] : '';
				$replaceArray["ANSWER_SUBJECT_$d"] 		= isset($array['answer_subject_'.$d])? $array['answer_subject_'.$d] : '';
				$replaceArray["ANSWER_SHORT_ANSWER_$d"]	= isset($array['answer_short_answer'.$d])? $array['answer_short_answer'.$d] : '';
				$replaceArray["ANSWER_KEYWORD_$d"] 		= isset($array['answer_keyword_'.$d])? $array['answer_keyword_'.$d] : '';
				$replaceArray["ANSWER_CONTENT_$d"] 		= isset($array['answer_content_'.$d])? $array['answer_content_'.$d] : '';
				$replaceArray["ANSWER_LINK_$d"] 		= isset($array['answer_link_'.$d])? $array['answer_link_'.$d] : '';
				$replaceArray["ANSWER_TYPE_$d"] 		= isset($array['answer_type_'.$d])? $array['answer_type_'.$d] : '';
				$replaceArray["ANSWER_USER_NAME_$d"]	= isset($array['answer_user_name_'.$d])? $array['answer_user_name_'.$d] : '';
				$replaceArray["ANSWER_USER_PHOTO_$d"]	= isset($array['answer_user_photo_'.$d])? $array['answer_user_photo_'.$d] : '';
			}
			
			// Goal entries
			$replaceArray['GOAL_ID'] 			= isset($array['goal_id'])?$array['goal_id']:'';
			$replaceArray['GOAL_SUBJECT'] 		= isset($array['goal_subject'])?$array['goal_subject']:'';
			$replaceArray['GOAL_CONTENT']		= isset($array['goal_content'])?$array['goal_content']:'';
			$replaceArray['GOAL_STATUS']		= isset($array['goal_status'])?$array['goal_status']:'';
			$replaceArray['GOAL_KEYWORD']		= isset($array['goal_keyword'])?$array['goal_keyword']:'';
			$replaceArray['GOAL_START_DATE']	= isset($array['goal_start_date'])?$array['goal_start_date']:'';
			$replaceArray['GOAL_START_STATUS']	= isset($array['goal_start_status'])?$array['goal_start_status']:'';
			$replaceArray['GOAL_TARGET_DATE']	= isset($array['goal_target_date'])?$array['goal_target_date']:'';
			$replaceArray['GOAL_TARGET_STATUS']	= isset($array['goal_target_status'])?$array['goal_target_status']:'';
			$replaceArray['GOAL_COMPLETION']	= isset($array['goal_completion'])?$array['goal_completion']:'';
			$replaceArray['GOAL_COMPLETION_DATE']=isset($array['goal_completion_date'])?$array['goal_completion_date']:'';
			$replaceArray['GOAL_VISITOR']		= isset($array['goal_visitor'])?$array['goal_visitor']:'';
			$replaceArray['GOAL_USER_PHOTO']	= isset($array['goal_user_photo'])?((stristr($array['goal_user_photo'], 'http:')=== false)?$config->imageLibrary:'').$array['goal_user_photo']:'';
			$replaceArray['GOAL_COMMENTS']		= isset($array['goal_comments'])?$array['goal_comments']:'';
			
			for($d=1;$d<10;$d++)
			{
				$replaceArray['GOAL_ID_'.$d] 			= isset($array['goal_id_'.$d])?$array['goal_id_'.$d]:'';
				$replaceArray['GOAL_SUBJECT_'.$d] 		= isset($array['goal_subject_'.$d])?$array['goal_subject_'.$d]:'';
				$replaceArray['GOAL_CONTENT_'.$d]		= isset($array['goal_content_'.$d])?$array['goal_content_'.$d]:'';
				$replaceArray['GOAL_STATUS_'.$d]		= isset($array['goal_status_'.$d])?$array['goal_status_'.$d]:'';
				$replaceArray['GOAL_KEYWORD_'.$d]		= isset($array['goal_keyword_'.$d])?$array['goal_keyword_'.$d]:'';
				$replaceArray['GOAL_START_DATE_'.$d]	= isset($array['goal_start_date_'.$d])?$array['goal_start_date_'.$d]:'';
				$replaceArray['GOAL_START_STATUS_'.$d]	= isset($array['goal_start_status_'.$d])?$array['goal_start_status_'.$d]:'';
				$replaceArray['GOAL_TARGET_DATE_'.$d]	= isset($array['goal_target_date_'.$d])?$array['goal_target_date_'.$d]:'';
				$replaceArray['GOAL_TARGET_STATUS_'.$d]	= isset($array['goal_target_status_'.$d])?$array['goal_target_status_'.$d]:'';
				$replaceArray['GOAL_COMPLETION_'.$d]	= isset($array['goal_completion_'.$d])?$array['goal_completion_'.$d]:'';
				$replaceArray['GOAL_COMPLETION_DATE_'.$d]=isset($array['goal_completion_date_'.$d])?$array['goal_completion_date_'.$d]:'';
				$replaceArray['GOAL_VISITOR_'.$d]		= isset($array['goal_visitor_'.$d])?$array['goal_visitor_'.$d]:'';
				$replaceArray['GOAL_USER_PHOTO_'.$d]	= isset($array['goal_user_photo_'.$d])?((stristr($array['goal_user_photo_'.$d], 'http:')=== false)?$config->imageLibrary:'').$array['goal_user_photo_'.$d]:'';
			}

			// replace request tags
			foreach($_REQUEST as $key => $val)
			{
				$key = strtoupper($key);
				$replaceArray["REQUEST_$key"] 			= trim(urldecode($val));
			} 
			
			// replace post tags
			foreach($_POST as $key => $val)
			{
				$key = strtoupper($key);
				$replaceArray["POST_$key"] 			= trim(urldecode($val));
			} 
			
			// clean if not found
			$replaceArray['PAGE_TITLE'] 		= '';
			$replaceArray['PAGE_CONTENT'] 		= '';
			$replaceArray['PAGE_REPLACE_BEGIN']	= '';
			$replaceArray['PAGE_REPLACE_END']	= '';
			
			// Google analytics
			$replaceArray['ANALYTICS'] 		=  empty($array['domain_analytics'])? '' :
			                                   "<script type=\"text/javascript\">
												 var _gaq = _gaq || [];
 												 _gaq.push(['_setAccount', '".$array['domain_analytics']."']);
 												 _gaq.push(['_trackPageview']);

  												(function() {
    												var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    												ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    												var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  												})();
  												
  												function recordOutboundLink(link, category, action) {
  													try {
   														 _gat._getTrackerByName()._trackEvent(category, action);
   													}catch(err){}
  												}
											    </script>";
			
			$replaceArray['ANALYTICSCLICK'] = empty($array['domain_analytics'])? '' :"recordOutboundLink(this, 'Outbound Links', 'position'); return false;";
			for($d=0;$d<=12;$d++)
			{
				$replaceArray['ANALYTICSCLICK_'.$d] 	= str_replace('position',$d,$replaceArray['ANALYTICSCLICK']);
			}
			
			// Shopping tags
			$replaceArray['PRODUCT_ID']				= isset($array['product_id'])?$array['product_id']:'';
			$replaceArray['PRODUCT_LINK']				= isset($array['product_url'])?$array['product_url']:'';
			$replaceArray['PRODUCT_TITLE']				= isset($array['product_name'])?$array['product_name']:'';
			$replaceArray['PRODUCT_ITEMS']				= isset($array['product_num_items'])?$array['product_num_items']:'';
			$replaceArray['PRODUCT_CATEGORY']			= isset($array['product_category'])?$array['product_category']:'';
			$replaceArray['PRODUCT_PRICE']				= isset($array['product_price'])?$array['product_price']:'';
			$replaceArray['PRODUCT_IMAGE']				= isset($array['product_image'])?$array['product_image']:'';
			$replaceArray['PRODUCT_SOURCE']				= isset($array['product_source'])?$array['product_source']:'';
			$replaceArray['PRODUCT_MANUFACTURER']		= isset($array['product_manufacturer'])?$array['product_manufacturer']:'';
			$replaceArray['PRODUCT_DESCRIPTION']		= isset($array['product_description'])?$array['product_description']:'';
			$replaceArray['PRODUCT_DETAILS']			= isset($array['product_details'])?$array['product_details']:'';
			$replaceArray['PRODUCT_FEATURES']			= isset($array['product_features'])?$array['product_features']:'';
			$replaceArray['PRODUCT_LOWEST_PRICE']		= isset($array['product_lowest_price'])?$array['product_lowest_price']:'';
			$replaceArray['PRODUCT_DIMENSION']			= isset($array['product_dimension'])?$array['product_dimension']:'';
			$replaceArray['PRODUCT_WEIGHT']				= isset($array['product_weight'])?$array['product_weight']:'';
			$replaceArray['PRODUCT_LOWEST_USED_PRICE']	= isset($array['product_lowest_used_price'])?$array['product_lowest_used_price']:'';
			$replaceArray['PRODUCT_LOWEST_REFURBISHED_PRICE']= isset($array['product_lowest_refurbished_price'])?$array['product_lowest_refurbished_price']:'';
			$replaceArray['PRODUCT_DISCLAIMER']			= isset($array['product_disclaimer'])?$array['product_disclaimer']:'';
			$replaceArray['PRODUCT_DATETIME']			= isset($array['product_datetime'])?$array['product_datetime']:'';
			for ($d=1; $d<=10; $d++)
			{
				$replaceArray["PRODUCT_REVIEWSOURCE_$d"]	= isset($array["product_reviewsource_$d"])?$array["product_reviewsource_$d"]:'';
				$replaceArray["PRODUCT_REVIEWCONTENT_$d"]	= isset($array["product_reviewcontent_$d"])?$array["product_reviewcontent_$d"]:'';
			}
			
			for($d=1; $d<=20; $d++)
			{
					$replaceArray["PRODUCT_ID_$d"]			= isset($array["product_id_$d"])?$array["product_id_$d"]:'';
					$replaceArray["PRODUCT_LINK_$d"]		= isset($array["product_url_$d"])?$array["product_url_$d"]:'';
					$replaceArray["PRODUCT_TITLE_$d"]		= isset($array["product_name_$d"])?$array["product_name_$d"]:'';
					$replaceArray["PRODUCT_ITEMS_$d"]		= isset($array["product_num_items_$d"])?$array["product_num_items_$d"]:'';
					$replaceArray["PRODUCT_CATEGORY_$d"]	= isset($array["product_category_$d"])?$array["product_category_$d"]:'';
					$replaceArray["PRODUCT_PRICE_$d"]		= isset($array["product_price_$d"])?$array["product_price_$d"]:'';
					$replaceArray["PRODUCT_IMAGE_$d"]		= isset($array["product_image_$d"])?$array["product_image_$d"]:'';
					$replaceArray["PRODUCT_SOURCE_$d"]		= isset($array["product_source_$d"])?$array["product_source_$d"]:'';
					$replaceArray["PRODUCT_MANUFACTURER_$d"]= isset($array["product_manufacturer_$d"])?$array["product_manufacturer_$d"]:'';
					$replaceArray["PRODUCT_DESCRIPTION_$d"]	= isset($array["product_description_$d"])?$array["product_description_$d"]:'';
					$replaceArray["PRODUCT_DETAILS_$d"]		= isset($array["product_details_$d"])?$array["product_details_$d"]:'';
					if (isset($array["product_reviews_$d"]) && !empty($array["product_reviews_$d"]))
					{
						foreach ($array["product_reviews_$d"] as $i => $rev)
						{
							$j = $i + 1;
							$replaceArray["PRODUCT_REVIEWSOURCE_$d_$j"] 	= $rev["Source"];
							$replaceArray["PRODUCT_REVIEWCONTENT_$d_$j"] 	= $rev["Content"];
						}
					}
			}
			// move this at the end to replace 
			$replaceArray['IMAGE_LIBRARY']		= $config->imageLibrary;
			
			// The replace
			$resultBase = $this->replaceHtmlTags($resultBase,$replaceArray);
			// Cleanup request tags
			$request = Module_Class::modulesFromTags($resultBase,'REQUEST_');
			if (!empty($request))
			{
				unset($replaceArray);
				$replaceArray = array();			
				foreach($request as $key) $replaceArray[$key] = '';
				$resultBase = $this->replaceHtmlTags($resultBase,$replaceArray);
			}	
			$resultBase = $this->replace_tag($resultBase);
		}
		return $resultBase;
	}

	
	public function getDefaultImage($array)
	{
		$image = new stdClass();
		$image->title 	= '';
		$image->url 	= '';
		$image->keyword	= '';
		$image->link	= '';
		if(is_array($array))
		{
			$default_pic 		= !empty($array['result_pic'])? $array['result_pic']:$array['page_pic'];
			$default_pic 		= empty($default_pic) && !empty($array['landing_pic'])? $array['landing_pic']:$default_pic;
			
			$image->title 	= @$array['page_keyword'];
			$image->url 	= $default_pic;
			$image->keyword = @$array['page_keyword'];
			$image->link	= 'images.php?keywords='.$image->keyword;
		}
		return $image;
	}
	
	public function parseSponsored($htmlCode,$feedArray,$adNum='6',$extrahtml = '',$block=1)
	{
		$rvalchk = 0;
		$rkey1 = '';
		$rval1 = '';
		$returnCode = '';
		
		if(is_array($feedArray) && $htmlCode != '')
		{
			$feedArray = $this->parseGetBlock($feedArray,$block);
			$feedNum = count($feedArray);
			$adNum = $feedNum < $adNum ? $feedNum : $adNum;
			for($m=0;$m<$adNum;$m++)
			{
				$blockCode = $htmlCode;
				
				//print_r($feedArray[$m]);
				foreach($feedArray[$m] as $rkey => $rval)
				{
					if ($rkey == 'DEEPLINKS')
					{
					    $rval = $this->parseDeepLinks($rval);
					}
					
					$blockCode = str_replace("{".$rkey."}",$rval,$blockCode);
					$blockCode = str_replace('{POSITION_ID}','position_'.($m+1),$blockCode);
					$blockCode = str_replace('{ID}',($m+1),$blockCode);
					if ($rkey == "MASKING_URL"){
						$rvalchk = 1;					
					}
					
					if ($rkey == "CLICK_LINK"){
						$rkey1 = "MASKING_URL";
						$rval1 = $rval;
					}

					if ($rkey == "POSITION"){
						$blockCode = str_replace("{ANALYTICSCLICK}","{ANALYTICSCLICK_$rval}",$blockCode);
					}
				}
				if (!$rvalchk){
					$blockCode = str_replace("{".$rkey1."}",$rval1,$blockCode);
					$returnCode .= $blockCode;
				}else{
				
					$returnCode .= $blockCode;
				}
			}
			if($extrahtml != '')
				$returnCode .= $extrahtml;
		} else {
			$returnCode = $htmlCode;
		}
		$returnCode = $this->replace_tag($returnCode);
		return $returnCode;
	}

	private function parseGetBlock($feedArray,$block)
	{
		$feedBlock = array();
		if(is_array($feedArray))
		{
			foreach ($feedArray as $data)
			{
				if (!isset($data['block']) || ($data['block'] == $block))
				{
					$feedBlock[] = $data;
				}
			}
		}

		return $feedBlock;
	}

	private function parseDeepLinks($rval){		
		$column1 = '';
		$column2 = '';
		for($j=0; $j<sizeof($rval); $j++){
			if($j%2==0)
			    $column1 .= '<li><a target="_blank" href="'.$rval[$j]['SITE-LINK'].'" data-icl-cop="topnav" data-icl-coi="540" class="resultTitle">'.$rval[$j]['TITLE'].'</a></li>';
			else
				$column2 .= '<li><a target="_blank" href="'.$rval[$j]['SITE-LINK'].'" data-icl-cop="topnav" data-icl-coi="540" class="resultTitle">'.$rval[$j]['TITLE'].'</a></li>';
		}
		
		$dlinks = '<div id="deepLinksColumns"><ul class="deeplinks cleanList" id="deeplinksColumn1">'.$column1.'</ul><ul class="deeplinks cleanList" id="deeplinksColumn2">'.$column2.'</ul></div>';
		return $dlinks;
	}
	
	function parseSponsoredEach($htmlCode,$feedArray,$adNum=0)
	{
		$rvalchk = 0;
		$rkey1 = '';
		$rval1 = '';
		$returnCode = '';
					
		if(isset($feedArray[$adNum]) && is_array($feedArray[$adNum]) && $htmlCode != '')
		{
				$blockCode = $htmlCode;
				
				foreach($feedArray[$adNum] as $rkey => $rval)
				{
					if ($rkey == 'DEEPLINKS')
					{
					    $rval = $this->parseDeepLinks($rval);
					}
					
					$blockCode = str_replace("{".$rkey."}",$rval,$blockCode);
					$blockCode = str_replace('{POSITION_ID}','position_'.($adNum+1),$blockCode);
					$blockCode = str_replace('{ID}',($adNum+1),$blockCode);
					if ($rkey == "MASKING_URL"){
						$rvalchk = 1;					
					}
					
					if ($rkey == "CLICK_LINK"){
						$rkey1 = "MASKING_URL";
						$rval1 = $rval;
					}

					if ($rkey == "POSITION"){
						$blockCode = str_replace("{ANALYTICSCLICK}","{ANALYTICSCLICK_$rval}",$blockCode);
					}
				}
				if (!$rvalchk){
					$blockCode = str_replace("{".$rkey1."}",$rval1,$blockCode);
					$returnCode .= $blockCode;
				}else{
				
					$returnCode .= $blockCode;
				}
		}
		$returnCode = $this->replace_tag($returnCode);
		return $returnCode;
	}
	
		
	public function parseComment($htmlCode,$feedArray,$extrahtml = '')
	{
		$returnCode = '';
		$adNum = count($feedArray);
		if(is_array($feedArray) && $htmlCode != '')
		{
			for($m=0;$m<$adNum;$m++)
			{
				$blockCode = $htmlCode;
				foreach($feedArray[$m] as $rkey => $rval)
				{
					$blockCode = str_replace("{".$rkey."}",$rval,$blockCode);
				}
				$returnCode .= $blockCode;
			}
			if($extrahtml != '')
				$returnCode .= $extrahtml;
		} else {
			$returnCode = $htmlCode;
		}
		$returnCode = $this->replace_tag($returnCode);
		return $returnCode;
	}
	
	public function replace_tag($resultBase)
	{
		$resultBase = str_replace('&lt;', '<', $resultBase);
		$resultBase = str_replace('&gt;', '>', $resultBase);
		return $resultBase;
	}

	public function replaceTestingURL($htmlCode,$server,$path)
	{
		if (APPLICATION_ENVIRONMENT == 'TESTING') {
			$htmlCode = str_replace(array('//'.TESTING_DOMAIN,'//www.'.TESTING_DOMAIN,'//WWW.'.TESTING_DOMAIN),"//$server",$htmlCode);
		}
		
		return $htmlCode;
	}

	public function parseArticleModule($htmlCode,$articles)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($articles) && !empty($htmlCode))
		{
			foreach ($articles as $k => $article) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{ARTICLE_TITLE}',@$article['article_title'],$feedHtml);
				$feedHtml = str_replace('{ARTICLE_SUMMARY}',@$article['article_summary'],$feedHtml);
				$feedHtml = str_replace('{ARTICLE_CONTENT}',@$article['article_content'],$feedHtml);
				$feedHtml = str_replace('{ARTICLE_ID}',@$article['article_id'],$feedHtml);
				$feedHtml = str_replace('{ARTICLE_KEYWORD}',@$article['article_keyword'],$feedHtml);
				
				/* Replace Images tags by article*/
				$j = $k + 1;
				$feedHtml = str_replace('{IMAGE_TITLE}','{IMAGE_TITLE_'.$j.'}',$feedHtml);
				$feedHtml = str_replace('{IMAGE_URL}','{IMAGE_URL_'.$j.'}',$feedHtml);
				$feedHtml = str_replace('{IMAGE_KEYWORD}','{IMAGE_KEYWORD_'.$j.'}',$feedHtml);
				$feedHtml = str_replace('{IMAGE_LINK}','{IMAGE_LINK_'.$j.'}',$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}
		}
		return $htmlResponse;
	}
	
	public function parseImageModule($htmlCode,$images,$sources,$channels,$defaultImage)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$allImageHtml = '';
		$htmlResponse = '';
		if(is_array($images) && !empty($htmlCode) && !empty($sources)) 
		{
			// Get image block
			$htmlBlock = '';
			if (stripos($htmlCode, '{IMAGE_BLOCK') !== false)
			{
				$iniPos = stripos($htmlCode, '{IMAGE_BLOCK_BEGIN}');
				$endPos = stripos($htmlCode, '{IMAGE_BLOCK_END}');
				$htmlBlock 	= $htmlCode;
				while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
				{
					$htmlCode  	= substr($htmlCode, 0, $iniPos).'{IMAGE_BLOCK}'.substr ($htmlCode, $endPos + 17);
					$htmlBlock 	= substr($htmlBlock, $iniPos + 19, $endPos - ($iniPos + 19));
					$iniPos 	= stripos($htmlBlock, '{IMAGE_BLOCK_BEGIN}');
					$endPos 	= stripos($htmlBlock, '{IMAGE_BLOCK_END}');
				}
			}
			
			$src = 1;
			$k = 0;
			foreach($sources as $channel) {
				$channel = strtolower($channel);
				$imageHtml = '';
				foreach( $images as  $content) 
				{
					if ($channel == strtolower($content['content_source']))
					{
						// replace by block
						$feedHtml = $htmlBlock;
						$feedHtml = str_replace('{IMAGE_TITLE}', @$content['content_title'],$feedHtml);
						$feedHtml = str_replace('{IMAGE_URL}', @$content['content_photo_src'],$feedHtml);
						$feedHtml = str_replace('{IMAGE_KEYWORD}', @$content['content_keyword'],$feedHtml);
						$feedHtml = str_replace('{IMAGE_LINK}', @$content['content_link'],$feedHtml);
						$htmlResponse .= $feedHtml.PHP_EOL;
						// Replace by id
						$k++;
						$htmlCode = str_replace("{IMAGE_TITLE_$k}", @$content['content_title'],$htmlCode);
						$htmlCode = str_replace("{IMAGE_URL_$k}", @$content['content_photo_src'],$htmlCode);
						$htmlCode = str_replace("{IMAGE_KEYWORD_$k}", @$content['content_keyword'],$htmlCode);
						$htmlCode = str_replace("{IMAGE_LINK_$k}", @$content['content_link'],$htmlCode);

						$imageHtml .= '<li>';
						$imageHtml .= '<img src="'.$content['content_photo_src'].'" alt="'.stripslashes(strip_tags($content['content_title'])).'" />';
						$imageHtml .= $this->anchor($content['content_link'], '', array('target'=> '_blank',  'class' => "link"));
						$imageHtml .= '<span  class="description">'.stripslashes($content['content_title']).'</span>';
						$imageHtml .= '</li>';
					}	
				}
				$allImageHtml .= $imageHtml;
				$htmlCode = str_ireplace("{IMAGE_LIST_$src}", $imageHtml, $htmlCode);
				$url = '#';
				$chanelName = '';
				if (!empty($imageHtml)) {
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"Images from $channel";
				} 
				$htmlCode = str_ireplace("{IMAGE_SOURCELINK_$src}", $url, $htmlCode);
				$htmlCode = str_ireplace("{IMAGE_SOURCETITLE_$src}", $chanelName, $htmlCode);
				$src++;
				}
		}
		// replace the block
		$htmlCode = str_replace("{IMAGE_BLOCK}", $htmlResponse,$htmlCode);
		
		// replace all Image if required
		$htmlCode = str_ireplace("{IMAGE_LIST}", $allImageHtml, $htmlCode);
		
		// cleanup
		for($i=0; $i < 20 ; $i++) {
			$htmlCode = str_ireplace(array("{IMAGE_LIST_$i}","{IMAGE_SOURCELINK_$i}","{IMAGE_SOURCETITLE_$i}"), '', $htmlCode);
			$htmlCode = str_ireplace(array("{IMAGE_TITLE_$i}","{IMAGE_URL_$i}","{IMAGE_KEYWORD_$i}","{IMAGE_LINK_$i}"), array($defaultImage->title,$defaultImage->url,$defaultImage->keyword,$defaultImage->link), $htmlCode);
		}
		return $htmlCode;
	}

	public function parseVideoModule($htmlCode,$videos,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$videoList = '';
		if(is_array($videos) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) 
				{
					$channel = strtolower($channel);
					$videoHtml = '';
					foreach( $videos as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$videoHtml .= '<li>';
							$videoHtml .= $this->anchor('/display/video/result.php?hplink='.urlencode($content['content_link']), '<img src="'.$content['content_photo_src'].'" alt="'.stripslashes(strip_tags($content['content_title'])).'" />', array('target'=> '_self'));
							$videoHtml .= '<span  class="description"><b>'.ucwords($channel).' Video:</b>'.stripslashes($content['content_title']).'</span>';
							$videoHtml .= '</li>'.PHP_EOL;
						}	
					}
					if (!empty($videoHtml)) {
						$url 		= isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName	= isset($channels[$channel])?$channels[$channel]['channel_name']:"$channel ";
						$title[] 	= $this->anchor($url, ucwords($chanelName), array('target'=> '_blank'));
						$videoList  .= $videoHtml;
					} 
				}
				if (!empty($videoList)) {
					$htmlCode = str_ireplace('{VIDEO_SOURCES_TITLES}', implode(' ',$title), $htmlCode);
					$htmlCode = str_ireplace('{VIDEO_LIST}', $videoList, $htmlCode);
				} 
			}
		}
		return $htmlCode;
	}
	
	
	public function parsePollModule($htmlCode,$polldata,$voted=false)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:''; 
		$content = '';

		if(is_array($polldata) && !empty($polldata) && !empty($htmlCode))
		{						
				$url = '?';
				foreach($polldata as $k=>$v){
					$url .= $k."=".$v."&";	
				}
				$content = "<img src='helpers/chartGenerator.php".$url."' width='240px' />";
				
				if($voted)
				{
					$content = "<p style='font-size:15; font-weight:bold; color:#666;'>You have already made your vote today. Thanks.</p>".$content;
				}
		}		
		
		$htmlCode = str_replace('{POLL_RESULT}',$content,$htmlCode);
		return $htmlCode;
	}

	public function parseArticleFeedModule($htmlCode,$articles,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($articles) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				$size = count($articles) > 5?300: (count($articles) > 2?500:2000);
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $articles as  $content) 
					{
						// get the list of articles
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="articleFeed">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],$size,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{ARTICLE_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"Articles from $channel";
						$html = str_ireplace('{ARTICLE_SOURCELINK}', $url, $html);
						$html = str_ireplace('{ARTICLE_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{ARTICLE_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					} 
				}
			}
		}
		return $htmlResponse;
	}

	public function parseDirectoryModule($htmlCode,$directories,$sources)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($directories) && !empty($htmlCode) && !empty($sources))
		{
			foreach($sources as $channel) 
			{
				$channel = strtolower($channel);
				foreach( $directories as  $content) 
				{
					if ($channel == strtolower($content['content_source']))
					{
						$feedHtml = $htmlCode;
						$feedHtml = str_replace('{DIRECTORY_ID}',@$content['directory_id'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_TITLE}',@$content['directory_title'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_DESCRIPTION}',@$content['directory_description'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_URL}',@$content['directory_url'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_IMG}',@$content['directory_img'],$feedHtml);
						$feedHtml = str_replace('{DIRECTORY_FLAG}',@$content['directory_flag'],$feedHtml);
						$htmlResponse .= $feedHtml.PHP_EOL;
					}	
				}
			}
		}
		return $htmlResponse;
	}
	
	public function parseEventModule($htmlCode,$events)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($events) && !empty($htmlCode))
		{			
				foreach( $events as  $content) 
				{
						$feedHtml = $htmlCode;
						$feedHtml = str_replace('{EVENT_ID}', @$content['event_id'],$feedHtml);
						$feedHtml = str_replace('{EVENT_EVENTFUL_ID}', @$content['event_eventful_id'],$feedHtml);
						$feedHtml = str_replace('{EVENT_TITLE}', @$content['event_title'],$feedHtml);
						$feedHtml = str_replace('{EVENT_KEYWORD}', @$content['event_keyword'],$feedHtml);
						$feedHtml = str_replace('{EVENT_DESCRIPTION}', @$content['event_description'],$feedHtml);
						$feedHtml = str_replace('{EVENT_URL}', @$content['event_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_START_TIME}', @$content['event_start_time'],$feedHtml);
						$feedHtml = str_replace('{EVENT_STOP_TIME}', @$content['event_stop_time'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_NAME}', @$content['event_venue_name'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_URL}', @$content['event_venue_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_VENUE_ADDRESS}', @$content['event_venue_address'],$feedHtml);
						$feedHtml = str_replace('{EVENT_CITY_NAME}', @$content['event_city_name'],$feedHtml);
						$feedHtml = str_replace('{EVENT_IMG}', @$content['event_image_url'],$feedHtml);
						$feedHtml = str_replace('{EVENT_IMG_WIDTH}', @$content['event_image_width'],$feedHtml);
			            $feedHtml = str_replace('{EVENT_IMG_HEIGHT}', @$content['event_image_height'],$feedHtml);
						$htmlResponse .= $feedHtml.PHP_EOL;
				}
		}
		return $htmlResponse;
	}
	
	public function parseQuestionModule($htmlCode,$questions)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($questions) && !empty($htmlCode))
		{
			Model_Class::resetCssCounter();
			foreach ($questions as $question) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{CSS_CLASS}',Model_Class::printCssClass(),$feedHtml);
				$feedHtml = str_replace('{QUESTION_ID}',@$question['question_id'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_KEYWORD}',@$question['question_keyword'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_CONTENT}',@$question['question_content'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_SUBJECT}',@$question['question_subject'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_DATE}',@$question['question_date'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_USERNAME}',@$question['question_username'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_USER_PHOTO}',@$question['question_user_photo'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_ANSWER}',@$question['question_answer'],$feedHtml);
				$feedHtml = str_replace('{QUESTION_ANSWERER}',@$question['question_answerer'],$feedHtml);
				$answerSummary = '';
				$answerArray = !empty($question['question_answer'])?explode(" ", $question['question_answer']):array();
				for($i=0; $i<15 && $i<count($answerArray) ; $i++){ $answerSummary .=" ".$answerArray[$i]; }
				$feedHtml = str_replace('{QUESTION_ANSWER_SUMMARY}',@$answerSummary,$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseQuestionAnswers($htmlCode,$answers)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($answers) && !empty($htmlCode))
		{			
			// Get answers block
			$htmlBlock = '';
			if (stripos($htmlCode, '{ANSWER_BLOCK') !== false)
			{
				$iniPos = stripos($htmlCode, '{ANSWER_BLOCK_BEGIN}');
				$endPos = stripos($htmlCode, '{ANSWER_BLOCK_END}');
				$htmlBlock 	= $htmlCode;
				while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
				{
					$htmlCode  	= substr($htmlCode, 0, $iniPos).'{ANSWER_BLOCK}'.substr ($htmlCode, $endPos + 18);
					$htmlBlock 	= substr($htmlBlock, $iniPos + 20, $endPos - ($iniPos + 20));
					$iniPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_BEGIN}');
					$endPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_END}');
				}
			}
			// replace tags
			Model_Class::resetCssCounter();
			foreach( $answers as  $i => $content) 
			{
					// replace by block
					$feedHtml = $htmlBlock;
					$cssClass = Model_Class::printCssClass();
					$feedHtml = str_replace('{CSS_CLASS}',$cssClass,$feedHtml);
					$feedHtml = str_replace('{ANSWER_ID}', @$content['answer_id'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_SUBJECT}', @$content['answer_subject'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_SHORT_ANSWER}', @$content['answer_short_answer'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_KEYWORD}', @$content['answer_keyword'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_CONTENT}', @$content['answer_content'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_LINK}', @$content['answer_link'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_TYPE}', @$content['answer_type'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_USER_NAME}', @$content['answer_user_name'],$feedHtml);
					$feedHtml = str_replace('{ANSWER_USER_PHOTO}', @$content['answer_user_photo'],$feedHtml);
					$htmlResponse .= $feedHtml.PHP_EOL;
					// Replace by id
					$k = $i + 1;
					$htmlCode = str_replace('{CSS_CLASS}',$cssClass,$htmlCode);
					$htmlCode = str_replace("{ANSWER_ID_$k}", @$content['answer_id'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_SUBJECT_$k}", @$content['answer_subject'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_SHORT_ANSWER_$k}", @$content['answer_short_answer'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_KEYWORD_$k}", @$content['answer_keyword'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_CONTENT_$k}", @$content['answer_content'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_LINK_$k}", @$content['answer_link'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_TYPE_$k}", @$content['answer_type'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_USER_NAME_$k}", @$content['answer_user_name'],$htmlCode);
					$htmlCode = str_replace("{ANSWER_USER_PHOTO_$k}", @$content['answer_user_photo'],$htmlCode);
			}
			$htmlCode = str_replace("{ANSWER_BLOCK}", $htmlResponse,$htmlCode);
		}
		return $htmlCode;
	}
	
	public function parseTypeAnswers($htmlCode,$answers,$typeArray=array())
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($answers) && !empty($htmlCode) && is_array($typeArray) && !empty($typeArray))
		{			
			// Get answers block
			$htmlBlock = '';
			if (stripos($htmlCode, '{ANSWER_BLOCK') !== false)
			{
				$iniPos = stripos($htmlCode, '{ANSWER_BLOCK_BEGIN}');
				$endPos = stripos($htmlCode, '{ANSWER_BLOCK_END}');
				$htmlBlock 	= $htmlCode;
				while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
				{
					$htmlCode  	= substr($htmlCode, 0, $iniPos).'{ANSWER_BLOCK}'.substr ($htmlCode, $endPos + 18);
					$htmlBlock 	= substr($htmlBlock, $iniPos + 20, $endPos - ($iniPos + 20));
					$iniPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_BEGIN}');
					$endPos 	= stripos($htmlBlock, '{ANSWER_BLOCK_END}');
				}
			}
			
			foreach( $typeArray as $k => $type)
			{
				$iniPos = stripos($htmlBlock, '{ANSWER_TYPE_'.strtoupper($type).'}');
				$endPos = stripos($htmlBlock, '{ANSWER_TYPE_'.strtoupper($type).'_END}');
				$length = strlen('{ANSWER_TYPE_'.strtoupper($type).'}');
				$typeBlock = substr($htmlBlock, $iniPos + $length, $endPos - ($iniPos + $length));
				// replace tags
				if (!empty($typeBlock))
				{
					foreach( $answers as  $i => $content) 
					{
						if (!empty($content['answer_type']) && strtolower($content['answer_type']) == strtolower($type))	
						{	
							// replace by block
							$feedHtml = $typeBlock;
							$feedHtml = str_replace('{ANSWER_ID}', @$content['answer_id'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_SUBJECT}', @$content['answer_subject'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_SHORT_ANSWER}', @$content['answer_short_answer'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_KEYWORD}', @$content['answer_keyword'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_CONTENT}', @$content['answer_content'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_LINK}', @$content['answer_link'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_TYPE}', @$content['answer_type'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_USER_NAME}', @$content['answer_user_name'],$feedHtml);
							$feedHtml = str_replace('{ANSWER_USER_PHOTO}', @$content['answer_user_photo'],$feedHtml);
							$htmlResponse .= $feedHtml.PHP_EOL;
						}								
					}
				}
			}
						
			$htmlCode = str_replace("{ANSWER_BLOCK}", $htmlResponse,$htmlCode);
		}
		return $htmlCode;
	}
	
	public function parseGoalModule($htmlCode,$goals)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($goals) && !empty($htmlCode))
		{
			Model_Class::resetCssCounter();
			foreach ($goals as $goal) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{CSS_CLASS}',Model_Class::printCssClass(),$feedHtml);
				$feedHtml = str_replace('{GOAL_ID}',@$goal['goal_id'],$feedHtml);
				$feedHtml = str_replace('{GOAL_SUBJECT}',@$goal['goal_subject'],$feedHtml);
				$feedHtml = str_replace('{GOAL_CONTENT}',@$goal['goal_content'],$feedHtml);
				$feedHtml = str_replace('{GOAL_STATUS}',@$goal['goal_status'],$feedHtml);
				$feedHtml = str_replace('{GOAL_KEYWORD}',@$goal['goal_keyword'],$feedHtml);
				$feedHtml = str_replace('{GOAL_START_DATE}',@$goal['goal_start_date'],$feedHtml);
				$feedHtml = str_replace('{GOAL_START_STATUS}',@$goal['goal_start_status'],$feedHtml);
				$feedHtml = str_replace('{GOAL_TARGET_DATE}',@$goal['goal_target_date'],$feedHtml);
				$feedHtml = str_replace('{GOAL_TARGET_STATUS}',@$goal['goal_target_status'],$feedHtml);
				$feedHtml = str_replace('{GOAL_COMPLETION}',@$goal['goal_completion'],$feedHtml);
				$feedHtml = str_replace('{GOAL_COMPLETION_DATE}',@$goal['goal_completion_date'],$feedHtml);
				$feedHtml = str_replace('{GOAL_VISITOR}',@$goal['goal_visitor'],$feedHtml);
				$feedHtml = str_replace('{GOAL_USER_PHOTO}',@$goal['goal_user_photo'],$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseShoppingModule($htmlCode,$products)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($products) && !empty($htmlCode))
		{
			foreach ($products as $product) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{PRODUCT_ID}',@$product['product_id'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LINK}',@$product['product_url'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_TITLE}',@$product['product_name'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_ITEMS}',@$product['product_num_items'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_CATEGORY}',@$product['product_category'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_PRICE}',@$product['product_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_IMAGE}',@$product['product_image'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_SOURCE}',@$product['product_source'],$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseShoppingDetail($htmlCode,$products)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($products) && !empty($htmlCode))
		{
			foreach ($products as $product) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{PRODUCT_ID}',@$product['product_id'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LINK}',@$product['product_url'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_TITLE}',@$product['product_name'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_ITEMS}',@$product['product_num_items'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_CATEGORY}',@$product['product_category'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_PRICE}',@$product['product_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_IMAGE}',@$product['product_image'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_SOURCE}',@$product['product_source'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_MANUFACTURER}',@$product['product_manufacturer'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DESCRIPTION}',@$product['product_description'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DETAILS}',@$product['product_details'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_FEATURES}',@$product['product_features'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_PRICE}',@$product['product_lowest_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DIMENSION}',@$product['product_dimension'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_WEIGHT}',@$product['product_weight'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_USED_PRICE}',@$product['product_lowest_used_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_LOWEST_REFURBISHED_PRICE}',@$product['product_lowest_refurbished_price'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DISCLAIMER}',@$product['product_disclaimer'],$feedHtml);
				$feedHtml = str_replace('{PRODUCT_DATETIME}',@$product['product_datetime'],$feedHtml);

				foreach ($product['product_reviews'] as $i => $rev)
				{
					$j = $i + 1;
					$feedHtml = str_replace("{PRODUCT_REVIEWSOURCE_$j}",$rev['Source'],$feedHtml);
					$feedHtml = str_replace("{PRODUCT_REVIEWCONTENT_$j}",$rev['Content'],$feedHtml);
				}
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}
	
	public function parseBlogModule($htmlCode,$nodes)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		if(is_array($nodes) && !empty($htmlCode))
		{
			foreach ($nodes as $node) 
			{
				$feedHtml = $htmlCode;
				$feedHtml = str_replace('{NODE_ID}',@$node['node_id'],$feedHtml);
				$feedHtml = str_replace('{NODE_LINK}',@$node['node_link'],$feedHtml);
				$feedHtml = str_replace('{NODE_TITLE}',@$node['node_title'],$feedHtml);
				$feedHtml = str_replace('{NODE_CREATED}',@$node['node_created'],$feedHtml);
				$feedHtml = str_replace('{NODE_TYPE}',@$node['node_type'],$feedHtml);
				$feedHtml = str_replace('{NODE_COMMENT_COUNT}',@$node['node_comment_count'],$feedHtml);
				$feedHtml = str_replace('{NODE_IMAGE}',@$node['node_image'],$feedHtml);
				$feedHtml = str_replace('{NODE_AUTHOR}',@$node['node_author'],$feedHtml);
				$htmlResponse .= $feedHtml.PHP_EOL;
			}	
		}
		return $htmlResponse;
	}

	public function parseBlogDetail($htmlCode,$nodeDetail)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';

		$htmlCode = str_replace('{NODE_ID}',@$nodeDetail['node_id'],$htmlCode);
		$htmlCode = str_replace('{NODE_LINK}',@$nodeDetail['node_link'],$htmlCode);
		$htmlCode = str_replace('{NODE_TITLE}',@$nodeDetail['node_title'],$htmlCode);
		$htmlCode = str_replace('{NODE_CREATED}',@$nodeDetail['node_created'],$htmlCode);
		$htmlCode = str_replace('{NODE_TYPE}',@$nodeDetail['node_type'],$htmlCode);
		$htmlCode = str_replace('{NODE_COMMENT_COUNT}',@$nodeDetail['node_comment_count'],$htmlCode);
		$htmlCode = str_replace('{NODE_IMAGE}',@$nodeDetail['node_image'],$htmlCode);
		$htmlCode = str_replace('{NODE_AUTHOR}',@$nodeDetail['node_author'],$htmlCode);
				
		$htmlResponse = '';
		if(is_array($nodeDetail) && !empty($htmlCode))
		{		
			$htmlBlock = '';
			if (stripos($htmlCode, '{BLOG_BLOCK') !== false)
			{
					$iniPos = stripos($htmlCode, '{BLOG_BLOCK_BEGIN}');
					$endPos = stripos($htmlCode, '{BLOG_BLOCK_END}');
					$htmlBlock 	= $htmlCode;
					while ($iniPos !== false && $endPos !== false && $endPos > $iniPos)
					{
						$htmlCode  	= substr($htmlCode, 0, $iniPos).'{BLOG_BLOCK}'.substr ($htmlCode, $endPos + 18);
						$htmlBlock 	= substr($htmlBlock, $iniPos + 20, $endPos - ($iniPos + 20));
						$iniPos 	= stripos($htmlBlock, '{BLOG_BLOCK_BEGIN}');
						$endPos 	= stripos($htmlBlock, '{BLOG_BLOCK_END}');
					}
			}
				
			if(is_array($nodeDetail) && !empty($htmlCode))
			{
				foreach ($nodeDetail['comments'] as $comment) 
				{
					$feedHtml = $htmlBlock;
					$feedHtml = str_replace('{COMMENT_SUBJECT}',@$comment['comment_subject'],$feedHtml);
					$feedHtml = str_replace('{COMMENT_NID}',@$comment['comment_nid'],$feedHtml);
					$feedHtml = str_replace('{COMMENT_CID}',@$comment['comment_cid'],$feedHtml);
					$feedHtml = str_replace('{COMMENT_CREATED}',@$comment['comment_created'],$feedHtml);
					$feedHtml = str_replace('{COMMENT_CONTENT}',@$comment['comment_content'],$feedHtml);
					$feedHtml = str_replace('{COMMENT_AUTHOR}',@$comment['comment_author'],$feedHtml);
													
					$htmlResponse .= $feedHtml.PHP_EOL;	
				}	
			}
		}
		
		$htmlCode = str_replace("{BLOG_BLOCK}", $htmlResponse,$htmlCode);

		return $htmlCode;
	}

	private function cutArticle ($content,$size,$link) {
		$content = stripslashes($content);
		if (($size > 0) && (strlen($content) > $size)) {
			//$content = substr($content, 0, $size).'[ '.$this->anchor($link, 'read more', array('target'=> '_blank')).' ]';
		}
		$content = $content.' [ '.$this->anchor($link, 'read more', array('class' => 'readmore', 'target' => '_blank')).' ]';
		return $content;
	}
	
	public function parseRssModule ($htmlCode,$rss,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($rss) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$rssHtml = isset($rss[$channel])?$rss[$channel]:'';
					if (!empty($rssHtml)) 
					{
						$html = str_ireplace('{RSS_LIST}', $rssHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:$channel;
						$html = str_ireplace('{RSS_SOURCELINK}', $url, $html);
						$html = str_ireplace('{RSS_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{RSS_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;					}
				}
			}
		}
		return $htmlResponse;
	}

	
	public function parseNewsModule($htmlCode,$news,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($news) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $news as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="News">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],0,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{NEWS_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"News from $channel";
						$html = str_ireplace('{NEWS_SOURCELINK}', $url, $html);
						$html = str_ireplace('{NEWS_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{NEWS_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					}
				}
			}
		}
		return $htmlResponse;
	}
	
	
	public function parseForumModule($htmlCode,$forums,$sources,$channels)
	{
		$htmlCode = !empty($htmlCode)?$htmlCode:'';
		$htmlResponse = '';
		
		if(is_array($forums) && !empty($htmlCode))
		{
			if(!empty($sources)) 
			{
				foreach($sources as $channel) {
					$channel = strtolower($channel);
					$feedHtml = '';
					foreach( $forums as  $content) 
					{
						if ($channel == strtolower($content['content_source']))
						{
							$content['content_photo_src'] = isset($content['content_photo_src'])?trim($content['content_photo_src']):'';
							$feedHtml .= '<li><div class="Forum">';
							$feedHtml .= !empty($content['content_photo_src'])?'<img src="'.$content['content_photo_src'].'" alt="'.stripslashes($content['content_title']).'" />':'';
							$feedHtml .= '<h2>'.stripslashes($content['content_title']).'</h2>';
							$feedHtml .= $this->cutArticle($content['content_main_content'],0,$content['content_link']);
							$feedHtml .= '</div></li>'.PHP_EOL;
						}	
					}
					if (!empty($feedHtml)) {
						$html = str_ireplace('{FORUM_LIST}', $feedHtml, $htmlCode);
						$url = isset($channels[$channel])?$channels[$channel]['channel_url']:'';
						$chanelName = isset($channels[$channel])?$channels[$channel]['channel_name']:"People from $channel";
						$html = str_ireplace('{FORUM_SOURCELINK}', $url, $html);
						$html = str_ireplace('{FORUM_SOURCETITLE}', $chanelName, $html);
						$html = str_ireplace('{FORUM_SOURCE}', $channel, $html);
						$htmlResponse .= $html.PHP_EOL;
					} 
				}
			}
		}
		return $htmlResponse;
	}
	
	
	public function parseTopicModule($htmlCode,$topics, $moduleStr){
		$moduleStr = strtolower($moduleStr);
		for($i=0; $i<16; $i++){
			$search[] = '{RELATED'.$i.'}';
		}
		$replace = explode(',', $topics);
		$htmlCode = str_replace($search, $replace, $htmlCode);
		$htmlCode = str_replace('{MODULES}', $moduleStr, $htmlCode);
		return $htmlCode;
	}
	
	public function parseWidgetModule($htmlCode,$domain){
		$htmlCode = str_replace('{SUBSCRIBE_DOMAIN}', $domain, $htmlCode);
		return $htmlCode;
	}
	
	public function parseDisplayVideo($video)
	{
		
		$htmlCode = empty($video)?'':
			'<object width="520" height="385">
				<param name="movie" value="'.$video.'"></param>
				<param name="allowFullScreen" value="true"></param>
				<param name="allowscriptaccess" value="always"></param>
				<embed src="'.$video.'" 
					type="application/x-shockwave-flash" 
					allowscriptaccess="always" 
					allowfullscreen="true" 
					width="520" 
					height="385">
				</embed>
			 </object>';
		return $htmlCode;
	}
	
	public function replaceHtmlTags($htmlCode,$replaceArray) 
	{
		if (is_array($replaceArray)) 
		{
			foreach($replaceArray as $rkey => $rval)
			{
				$htmlCode = str_replace("{".$rkey."}",$rval,$htmlCode);
			}
		}
		return $htmlCode;
	}
	
	public function insertHtmlCode($htmlCode,$type,$Code) 
	{
		if (!empty($Code)) 
		{
			switch ($type) {
				case 'js' :	$htmlCode = str_ireplace("&lt;/head&gt;",'<script language="javascript">'.$Code.'</script>&lt;/head&gt;',$htmlCode).PHP_EOL;
							break;
				
				case 'css':	$htmlCode = str_replace('&lt;/head&gt;','<style>'.$Code.'</style>&lt;/head&gt;',$htmlCode).PHP_EOL;
							break;
							
				case 'jsLoad' :	$htmlCode = str_ireplace("&lt;/body&gt;",'&lt;/body&gt;<script language="javascript"> $(window).load(function () { '.$Code.' }); </script>',$htmlCode).PHP_EOL;
							break;
			}
		}
		return $htmlCode;
	}
	/**
	 * Anchor Link
	 *
	 * Creates an anchor based on the local URL.
	 *
	 * @access	public
	 * @param	string	the URL
	 * @param	string	the link title
	 * @param	mixed	any attributes
	 * @return	string
	 */
	public function anchor($uri = '', $title = '', $attributes = '')
	{
		$title = (string) $title;
		$site_url = (!preg_match('|^\w+://| i', $uri)) ? $this->__fixURL($uri) : $uri;

		if (trim($title) == '')
		{
			$title = '';
		}

		if ($attributes != '')
		{
			$attributes = $this->__parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}
	
	private function __fixURL ($uri) 
	{
		if (empty($uri)) {
			$uri = '{DOMAIN}';
		} else {
			$uri = str_replace('\\','/',$uri);
			$parts = explode("/",$uri);
			$uri   = (!preg_match('|^/(.)+| i', $uri)) ?'{HOME}'.implode('/', $parts):implode('/', $parts);
		}
		return $uri;
	}
	/**
	 * Parse out the attributes
	 *
	 * Some of the functions use this
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	private function __parse_attributes($attributes, $javascript = FALSE)
	{
		if (is_string($attributes))
		{
			return ($attributes != '') ? ' '.$attributes : '';
		}

		$att = '';
		foreach ($attributes as $key => $val)
		{
			if ($javascript == TRUE)
			{
				$att .= $key . '=' . $val . ',';
			}
			else
			{
				$att .= ' ' . $key . '="' . $val . '"';
			}
		}

		if ($javascript == TRUE AND $att != '')
		{
			$att = substr($att, 0, -1);
		}

		return $att;
	}
	
}


?>