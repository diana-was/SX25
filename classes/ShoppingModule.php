<?php
/**	APPLICATION:	SX25
*	FILE:			ShoppingModule.php
*	DESCRIPTION:	front end - Shopping Module class
*					all classes should be named as Name_Class and the file as Name.php casesensitive
*	CREATED:		29 October 2010 by Diana De vargas
*	UPDATED:									
*/

class ShoppingModule_Class    extends  Module_Class
{
	const 		AWS_API_KEY = 'AKIAJE4BSINFL2JIGHXA';
	const 		AWS_API_SECRET_KEY = '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';
	protected	$moduleName = 'Shopping';
	protected	$sourceList = array('amazon','shopzilla');
	private     $SearchEngine;
	protected	$amazonCategories = array('All','Apparel','Appliances','ArtsAndCrafts','Automotive','Baby','Beauty','Blended','Books','Classical','DVD','DigitalMusic','Electronics','GourmetFood','Grocery','HealthPersonalCare','HomeGarden','Industrial','Jewelry','KindleStore','Kitchen','MP3Downloads','Magazines','Merchants','Miscellaneous','Music','MusicTracks','MusicalInstruments','MobileApps','OfficeProducts','OutdoorLiving','PCHardware','PetSupplies','Photo','Shoes','SilverMerchants','Software','SportingGoods','Tools','Toys','UnboxVideo','VHS','Video','VideoGames','Watches','Wireless','WirelessAccessories');
	
	
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
     * Get the ImageModule static object
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
			<!-- Shopping Module -->
			<div class="moduleShopping">
				<h3 class="product-name">
					<a title="{PRODUCT_TITLE}" href="result.php?k={PRODUCT_CATEGORY}&keywords={KEYWORDS}&product_id={PRODUCT_ID}&source={PRODUCT_SOURCE}">{PRODUCT_TITLE}</a>
				</h3>
				<div class="product-image">
                	<a title="{PRODUCT_TITLE}" href="result.php?k={PRODUCT_CATEGORY}&keywords={KEYWORDS}&product_id={PRODUCT_ID}&source={PRODUCT_SOURCE}"><img alt="{PRODUCT_TITLE}" src="{PRODUCT_IMAGE}" /></a>
                </div>                     
                <div class="product-bottom">           
	    			<div class="product-price">
	                    <span class="price">{PRODUCT_PRICE}</span>
	                </div>
				    <div class="product-button">
				    	<input type="submit" value="Add to Cart" onclick="" class="btn-cart" />
				    </div>      
				    <div style="clear:both;"></div>   
				</div>
		    </div>';
		$css = '
			.moduleShopping {
				background: #ccc;
				float: left;
				width: 202px;
				min-height: 230px;
				-moz-border-radius: 5px;
				padding: 10px;
				margin: 10px;
			}
			.product-image  {
				background: #ffffff;
				display: block;
				width: 180px;
				height: 130px;
				padding: 10px;
                                margin: 0px;
				border: 1px solid #ccc;
				-moz-border-radius: 5px;
			}
			.product-image a img  {
				width: 180px;
				height: 130px;
				border: 0px solid #ccc;
			}
			.product-name {
				margin: 0px 0px 7px 0px;
			}
			.product-name a {
				color: #3B3F49;
				font-size: 0.8em;
				font-weight: bold;
				text-decoration: none;
				text-transform: uppercase;
			}
			.product-bottom {
			}
			.product-price {
				margin: 5px 0px 0px 0px;
				float: left;
			}
			.product-button {
				margin: 5px 0px 0px 0px;
				float: right;
			}
			.btn-cart {
				background: #3B3F49;
				font-size: 0.8em;
				color: #ffffff; 
				border: 1px solid #000;
				-moz-border-radius: 5px;
			}
			
			';
		   	$this->layout['name'] 		= 'Default layout '.$this->moduleName;
	        $this->layout['layout'] 	= $layout;
	        $this->layout['settings']	= array('perPage' => 10, 'sources' => $this->sourceList);
	        $this->layout['layout_css'] = $css;
	        $settings = json_encode($this->layout['settings']);
	        $pQuery = "INSERT INTO modulelayouts (`modulelayout_module_id`,`modulelayout_name`,`modulelayout`,`modulelayout_settings`,`modulelayout_default`, `modulelayout_css`) values 
					  ('".$this->moduleID."','Default layout ".$this->moduleName."','".$this->__encodeTags($layout)."','".$settings."',1,'".$this->__encodeTags($css)."')";
			$this->layout['id'] = $this->_db->insert_sql($pQuery);
	}
    
	/**
	 * get shopping from Amazon search
	 */
	public function getAmazonShopping($keyword , $category, $per_page = 5, $ResponseGroup ='ItemAttributes,Images')
	{
		$exist = false;
		foreach ($this->amazonCategories as $cat)
		{
			if (strcasecmp($category,$cat) == 0)
			{
				$exist = true;
				$category = $cat;
			}
		}
		$category = $exist?$category:'All';
		$keyword = trim($keyword);
		$data = array();
		$params = array(
			'AssociateTag'	=> 'cutprilap-20',
	        'Operation'     => 'ItemSearch',
	        'SearchIndex'   => $category,
	        'Keywords'      => $keyword,
	        'ResponseGroup' => $ResponseGroup
	    );
	    $xml = $this->_aws_signed_request($params);

		//$response = file_get_contents($url);	
		//$xml = simplexml_load_string($response);
		//echo '<pre>'; print_r($xml); echo '</pre>';
		 
		if (!empty($xml) && !empty($xml->Items->Item))
		{	 	
			$amazon_products = $xml->Items->Item;
			//echo '<pre>'; print_r($amazon_products); echo '</pre>'; 
			$count = 0;
			foreach ( $amazon_products as $key => $value )
			{			
				$data[] = array(
							'product_url' 			=> mysql_real_escape_string((string) $value->DetailPageURL),
							'product_name' 			=> mysql_real_escape_string((string) $value->ItemAttributes->Title),
							'product_num_items' 	=> mysql_real_escape_string((string) $value->ItemAttributes->NumberOfItems),
							'product_category' 		=> mysql_real_escape_string((string) $value->ItemAttributes->ProductGroup),
							'product_price' 		=> $value->ItemAttributes->ListPrice->CurrencyCode.' '.(string) $value->ItemAttributes->ListPrice->FormattedPrice,
							'product_image' 		=> (string) $value->MediumImage->URL,
							'product_id' 			=> (string) $value->ASIN,
					 		'product_source'		=> 'amazon'	       
				);
				$count++;
				if ($count >= $per_page) break;
				//echo '<pre>'; print_r($value); echo '</pre><br><br>';
			}
		}		
 
		return $data;
	}
	
	/**
	 * get shopping from Shopzilla search
	 */
	public function getShopzillaShopping($keyword , $category, $per_page = 5, $ResponseGroup ='ItemAttributes,Images')
	{		
		$data = array();
		$xml = $this->shopzilla_request($keyword, $per_page, '');

		if (!empty($xml) && !empty($xml->Products->Product))
		{
			$count = 0;
			foreach ( $xml->Products->Product as $key => $value )
			{			 			 
				 	$data[] = array(
								'product_url' 			=> (string) $value->url,
								'product_name' 			=> (string) $value->title,
								'product_description' 	=> (string) $value->description,
								'product_num_items' 	=> (string) $value->PriceSet->stores,
								'product_category' 		=> (string) $value['categoryId'],
								'product_manufacturer' 	=> (string) $value->manufacturer,
								'product_price' 		=> (string) $value->PriceSet->maxPrice,
								'product_lowest_price' 	=> (string) $value->PriceSet->minPrice,
								'product_image' 		=> (string) $value->Images->Image[2],
								'product_id' 			=> (string) $value['id'],
								'product_details' 		=> (string) $value->url,
								'product_source'		=> 'shopzilla'	    
					);
					$count++;
					if ($count >= $per_page) break;
				 
				 $count++;
			}
		}
		return $data;
	}
	
	private function shopzilla_request($keyword, $quantity, $productId)
	{
		$apiKey='e5647b2d2c8677378b9e99226e8c6e24';
		$publisherId='61945';
		$productId = !empty($productId) ? $productId:'';
		$keyword = urlencode($keyword);
		$request = "http://catalog.bizrate.com/services/catalog/v1/us/product?apiKey=".$apiKey."&publisherId=".$publisherId."&placementId=1&categoryId=&keyword=".$keyword."&productId=".$productId."&productIdType=&offersOnly=&merchantId=&brandId=&biddedOnly=&minPrice=&maxPrice=&minMarkdown=&zipCode=&freeShipping=&start=0&results=".$quantity."&backfillResults=0&startOffers=0&resultsOffers=0&sort=relevancy_desc&attFilter=&attWeights=&attributeId=&resultsAttribute=10&resultsAttributeValues=10&showAttributes=&showProductAttributes=&minRelevancyScore=100&maxAge=&showRawUrl=&imageOnly=&format=xml&callback=callback";
		
		//return $response = file_get_contents($request);	
		$this->_curlObj->createCurl('get',$request);
		$response = $this->_curlObj->__toString();
		return !empty($response)?simplexml_load_string($response):'';
	}
	
	private function shopzillaItemDetail($productId,$source)
	{
		$data = array();
		$xml = $this->shopzilla_request('', '1', $productId);
		//$xml = simplexml_load_string($response);
		
		if (!empty($xml) and !empty($xml->Products->Product))
		{
			$count = 0;
			foreach ( $xml->Products->Product as $key => $value )
			{			 			 
				 	$data[] = array(
								'product_url' 			=> (string) $value->url,
								'product_name' 			=> (string) $value->title,
								'product_description' 	=> (string) $value->description,
								'product_num_items' 	=> (string) $value->PriceSet->stores,
								'product_category' 		=> (string) $value['categoryId'],
								'product_manufacturer' 	=> (string) $value->manufacturer,
								'product_price' 		=> (string) $value->PriceSet->maxPrice,
								'product_lowest_price' 	=> (string) $value->PriceSet->minPrice,
								'product_image' 		=> (string) $value->Images->Image[2],
								'product_id' 			=> (string) $value['id'],
								'product_details' 		=> (string) $value->url,
								'product_source'		=> 'shopzilla',
				 				'product_disclaimer'	=> 'Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [relevant Shopzilla Site(s), as applicable] at the time of purchase will apply to the purchase of this product.',
				 				'product_datetime'		=> '(as of '.date('Y-m-d H:i T').')',	 														
					);
				 	$count++;
			}
		}
		return $data;	
	}

	function amazonItemDetail($asin)
	{
		$data = array();
		$params = array(
			'AssociateTag'	=> 'pocpccen-20',
			'Operation'     => 'ItemLookup',
	        'IdType'        => "ASIN",
	        'ItemId'        => $asin,
	        'ResponseGroup' => "Medium,OfferFull"
	    );		
		
	    $xml = $this->_aws_signed_request($params);
	  		
	    //$response = file_get_contents($url);		
		//$xml = simplexml_load_string($response); 
		//echo '<pre>'; print_r($xml); echo '</pre>';
		 
		if (!empty($xml) && !empty($xml->Items->Item))
		{	 	
			$amazon_products = $xml->Items->Item;
			//echo '<pre>'; print_r($amazon_products); echo '</pre>'; 
			
			foreach ( $amazon_products as $key => $value )
			{		
				//echo '<pre>'; print_r($value->Offers); echo '</pre>';
				
				$details = '';
				foreach ($value->ItemLinks->ItemLink as $link) 
				{
					if ($link->Description == 'Technical Details')
						$details = (string) $link->URL;
				}

				// reviews
				$description = '';
				$reviews = array();
				if (!empty($value->EditorialReviews))
				{
					foreach ($value->EditorialReviews->EditorialReview as $rev) {
						if ($rev->Source == 'Product Description')
							$description = (string) $rev->Content;
						else 
							$reviews[] = (array) $rev;
					}
				}
				// features
				$product_features = '<ul class="product_features">';
				if(!empty($value->ItemAttributes->Feature))
				{
					foreach($value->ItemAttributes->Feature as $feature)
					{
						$product_features .= '<li>'.$feature.'</li>';
					}
				}

				foreach ($value->ItemAttributes->children() as $att) 
				{
					$name = $att->getName();
					if (($name != 'Feature') && ($name != 'Title') && ($name != 'NumberOfItems') && ($name != 'Manufacturer') && ($name != 'ProductGroup') && ($name != 'ListPrice') && ($name != 'OfferSummary') && ($name != 'ItemDimensions') && ($name != 'ProductTypeName') && ($name != 'ProductTypeSubcategory') && ($name != 'LegalDisclaimer')
						&& !empty($value->ItemAttributes->$name))
					{
						$product_features .= '<li><b>'.trim(preg_replace('/([A-Z])/',' ${1}',$name)).':</b> '.$value->ItemAttributes->$name.'</li>';
					}
				}	
				$product_features .= '</ul>';
				
				// Dimensions
				if (isset($value->ItemAttributes->ItemDimensions->Height) && isset($value->ItemAttributes->ItemDimensions->Length) && isset($value->ItemAttributes->ItemDimensions->Width))
					$product_dimension = "H ".($value->ItemAttributes->ItemDimensions->Height/100)." / L ".($value->ItemAttributes->ItemDimensions->Length/100)." / W ".($value->ItemAttributes->ItemDimensions->Width/100)." inches";
				else
					$product_dimension = '';
				
				$data[] = array(
							'product_url' 			=> mysql_real_escape_string((string) $value->DetailPageURL),
							'product_name' 			=> mysql_real_escape_string((string) $value->ItemAttributes->Title),
							'product_num_items' 	=> mysql_real_escape_string((string) $value->ItemAttributes->NumberOfItems),
							'product_manufacturer'	=> mysql_real_escape_string((string) $value->ItemAttributes->Manufacturer),
							'product_category' 		=> mysql_real_escape_string((string) $value->ItemAttributes->ProductGroup),
							'product_price' 		=> (isset($value->ItemAttributes->ListPrice->CurrencyCode)?$value->ItemAttributes->ListPrice->CurrencyCode:'').' '.(string) $value->ItemAttributes->ListPrice->FormattedPrice,
							'product_lowest_price' 	=> isset($value->OfferSummary->LowestNewPrice)?$value->OfferSummary->LowestNewPrice->CurrencyCode.' '.(string) $value->OfferSummary->LowestNewPrice->FormattedPrice:$value->ItemAttributes->ListPrice->FormattedPrice,
							'product_lowest_used_price'=> isset($value->OfferSummary->LowestUsedPrice)?$value->OfferSummary->LowestUsedPrice->CurrencyCode.' '.(string) $value->OfferSummary->LowestUsedPrice->FormattedPrice:'',
							'product_lowest_refurbished_price'=> isset($value->OfferSummary->LowestRefurbishedPrice)?$value->OfferSummary->LowestRefurbishedPrice->CurrencyCode.' '.(string) $value->OfferSummary->LowestRefurbishedPrice->FormattedPrice:'',
							'product_weight' 	    => isset($value->ItemAttributes->ItemDimensions->Weight)?($value->ItemAttributes->ItemDimensions->Weight/100).' <span class="product_weight">pounds</span>':'',
							'product_image' 		=> (string) $value->LargeImage->URL,
							'product_id' 			=> (string) $value->ASIN,
							'product_reviews' 		=> $reviews,
							'product_description'	=> $description,
							'product_dimension'	    => $product_dimension,
							'product_details' 		=> $details,
							'product_features' 		=> $product_features,
							'product_disclaimer'	=> (isset($value->ItemAttributes->LegalDisclaimer)?$value->ItemAttributes->LegalDisclaimer.'<br>':'').
			 										   'Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on [relevant Amazon Site(s), as applicable] at the time of purchase will apply to the purchase of this product.',
							'product_source'		=> 'amazon',      
			 				'product_datetime'		=> '(as of '.date('Y-m-d H:i T',strtotime("-1 day")).')',	 														
				);
			}
		}		
		return $data;
	}
	
	
	/**
	 *   Parameters:
	 *    $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
	 *    $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
	 *                       "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
	 *    $public_key - your "Access Key ID"
	 *    $private_key - your "Secret Access Key"
	 */
	function _aws_signed_request($params)
	{
	    // some paramters
	    $method = "GET";
	    $host = "ecs.amazonaws.com";
	    $uri = "/onca/xml";
	    
	    // additional parameters
	    $params["Service"] = "AWSECommerceService";
	    $params["AWSAccessKeyId"] = self::AWS_API_KEY;
		//affliate tracking
		//$params["AssociateTag"] = $associate_tag;
	    // GMT timestamp
	    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
	    // API version
	    $params["Version"] = "2009-03-31";
	    
	    // sort the parameters
	    ksort($params);
	    // create the canonicalized query
	    $canonicalized_query = array();
	    foreach ($params as $param=>$value)
	    {
	        $param = str_replace("%7E", "~", rawurlencode($param));
	        $value = str_replace("%7E", "~", rawurlencode($value));
	        $canonicalized_query[] = $param."=".$value;
	    }
	    $canonicalized_query = implode("&", $canonicalized_query);
	    
	    // create the string to sign
	    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
	    
	    // calculate HMAC with SHA256 and base64-encoding
	    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, self::AWS_API_SECRET_KEY , True));
	    
	    // encode the signature for the request
	    $signature = str_replace("%7E", "~", rawurlencode($signature));
	    
	    // create request
	    $canonicalized_query = str_replace('&Timestamp=','&CUT_XYZ59&Timestamp=',$canonicalized_query); //to use in PROXY marked part not to include in key
	    $url = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
	     
	    // Call the site and get the data
		$this->_curlObj->createCurl('get',$url);
		$response =$this->_curlObj->__toString();
		return !empty($response)?simplexml_load_string($response):'';
	}
	
	public function getData($keyword,$sources,$numProducts,$extraParams=array())
	{
		$data = array();
		if (!is_array($sources)) { 
			$sources = array($sources);
		}
		// extra parameters			
		$category 	 = (isset($extraParams['category'])&&!empty($extraParams['category']))?$extraParams['category']:'All';
		$product_id  = isset($extraParams['product_id'])?trim($extraParams['product_id']):'';

		$numImg = $numProducts; //round($numProducts/count($sources));
		if ($numImg <= 0) return $data;
		
		foreach($sources as $n => $source) {
			$source = strtolower($source);
			$plus = $numImg + (($n * $numImg) - count($data));
			if ($plus > 0) {
				switch (strtolower($source)) {
					case 'amazon': if (!empty($product_id)) {	
						
										$data = array_merge($data,$this->amazonItemDetail($product_id,$source));
									}
									else 
									{
								   		$data = array_merge($data,$this->getAmazonShopping($keyword,$category,$plus));
									}
								   break;
								   
					case 'shopzilla': if (!empty($product_id)) {	
						
										$data = array_merge($data,$this->shopzillaItemDetail($product_id,$source));
									}
									else 
									{
								   		$data = array_merge($data,$this->getShopzillaShopping($keyword,$category,$plus));
									}
								   break;
				}
			}
		}
		return $data;
	}

}