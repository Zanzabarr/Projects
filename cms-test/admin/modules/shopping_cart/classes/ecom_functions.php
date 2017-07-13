<?php
class ecom_functions {

	// returns all associated products
	// if no parameter passed, returns all products
	public static function get_products($data = false)
	{
		if( $data && ! is_array($data) ) 
		{
			my_log("ecom_category: get_products. Wrong Parameter Type: array expected.");
			return false;
		}	
		
		// create category clause if category is passed in
		if (isset($data['category']))
		{
			//find the category range
			$categoryClause="
				  AND cat.id IN (
					SELECT inNode.`category_data_id`
					FROM `ecom_category` AS inNode, `ecom_category` AS outNode, `ecom_category_data` AS `dat`
					WHERE `dat`.url = '{$data['category']}'
					  AND outNode.category_data_id = `dat`.id
					  AND inNode.lft >= outNode.lft
					  AND inNode.rgt <= outNode.rgt
				)
			";
			
		} else $categoryClause = '';
		
		if (isset($data['search']))
		{
			$search = trim($data['search']);
			$searchClause = "
				  AND (
					prod.`title` LIKE '%{$search}%'
					OR prod.`seo_keywords` LIKE '%{$search}%'
				)
			";
		} else $searchClause = "";
		
		if(isset($data['filter'])) {
			switch($data['filter']) {
				case "1":
					$filter = "prod.price ASC";
					break;
				case "2":
					$filter = "prod.price DESC";
					break;
			}
		} else {
			$filter = "prod.featured DESC, prod.url, img.`posn`";
		}

		// set the LIMIT data
		$start = isset($data['start'])  && is_pos_int($data['start'],true)  ? $data['start'] :0;
		// 	if no length is set, return all records (limit with huge number)
		$length = isset($data['length']) && is_pos_int($data['length'])  ? $data['length'] : 123456;
		// if a start value is supplied, build the LIMIT clause	
		$limitClause = ($start || $length < 123456) ? "
			LIMIT {$start}, {$length}" : '';

		// if requesting featured elements, build the featured clause
		$featuredClause = isset($data['featured']) && is_bool($data['featured']) && $data['featured'] ? "
			  AND featured='1'" : '';
		
		// build the collections sub-query
		$collections = isset($data['collection']) && is_array($data['collection']) ? $data['collection'] : false;

		if($collections)
		{	
			$collectionClause = "
			  AND prod.id IN(
				SELECT DISTINCT  pINc.product_id 
				FROM ecom_prod_in_col AS pINc, ecom_collection AS col
				WHERE col.id = pINc.collection_id
				  AND(
					col.url = '" . trim($collections[0]) . "'";
			for($i = 1; $i < count($collections); $i++)
			{
				$collectionClause .= "
					OR col.url = '" . trim($collections[$i]) . "'
				";
			}
			$collectionClause .= "  )
			  ) ";
		} else $collectionClause = '';

		
		// lets build the core query
		$result = logged_query_assoc_array("
			SELECT cat.url as cat_url, prod.*, img.name as img_name, img.desc AS img_desc, img.html_desc AS img_html_desc, img.alt AS img_alt, img.posn AS img_posn
			FROM `ecom_product` AS prod
			LEFT JOIN `ecom_category_data` AS cat
			ON cat.id = prod.category_id
			LEFT JOIN `ecom_image` AS img
			ON img.item_id = prod.id
			WHERE prod.id > 0
			  AND prod.status > 0 {$categoryClause}{$featuredClause}{$collectionClause}{$searchClause} 
			ORDER BY {$filter} {$limitClause}
		",null,0,array());
		
		$arOut = array();
		$last_id = -1;
		foreach ($result as $row)
		{
			
			$cur_id = $row['id'];
			if($cur_id != $last_id) 
			{
				// set the base data
				$arOut[$cur_id] = $row;
				// this retains img data for the first image returned
			}	
			//start the image array
			if($row['img_posn'] )
			{
				$arOut[$cur_id]['images'][] = array(
					'name'  	=>  $row['img_name'],
					'desc'  	=>  $row['img_desc'],
					'html_desc'	=>  $row['img_html_desc'],
					'alt'  		=>  $row['img_alt'],
					'posn'  	=>  $row['img_posn'],
					'item_id'	=>	$cur_id
				); 
			}
			$last_id = $cur_id;
			
		}
		
		return $arOut;
	}

	// returns product's category/ array of images, and product data
	public static function getProductByUrl($url)
	{
		$url = trim($url);
		$productData = logged_query_assoc_array("
			SELECT cate.name as cat_name, cate.url as cat_url, prod.*, 
				   img.name AS img_name, img.posn as img_posn, img.`desc` AS img_desc, img.`html_desc` AS img_html_desc, img.alt AS img_alt
			FROM `ecom_product` AS prod
			LEFT JOIN `ecom_category_data` AS cate
			  ON prod.`category_id` = cate.`id`
			LEFT JOIN `ecom_image` AS img
			  ON prod.id = img.item_id
			WHERE prod.`id` > 0
			  AND prod.`url` = :url
			ORDER BY img.posn
		",null,0,array(":url" => $url));
		
		if (!$productData || count($productData) == 0) return false;
		
		// build an associative array of products with the id as the key
		//   present the collection names as an array: col_names
		$arProduct= $productData[0];

		// fill the images array
		foreach($productData as $row)
		{	if($row['img_posn'])
			{
				$arImg[] = array(
					'name'		=> $row['img_name'],
					'desc'		=> $row['img_desc'],
					'html_desc'	=> $row['img_html_desc'],
					'alt'		=> $row['img_alt'],
					'posn'		=> $row['img_posn'],
					'item_id'	=> $productData[0]['id']
				);
			}
			
		}			
		$arProduct['images'] = isset($arImg) ? $arImg : false;
		
		return $arProduct;
		
	}

	// just get the base info
	public static function get_basic_product($url)
	{
		$url = trim($url);
		$productData = logged_query_assoc_array("
			SELECT *
			FROM `ecom_product` 
			WHERE `id` > 0
			  AND `url` = :url
		",null,0,array(":url" => $url));
		
		if (!$productData || count($productData) == 0) return false;
		return $productData[0];
	}

	//Get the price per quantity
	public static function getpricefromQ($url, $quantity) {
		$url = trim($url);
		$item = logged_query_assoc_array("SELECT * FROM `ecom_product` WHERE url = :url",null,0,array(":url" => $url));
		$item = $item[0];
		if($item['sale']>0) {
			return $item['sale'];
		}elseif($item['q2'] == 0){
			return $item['price'];
		}elseif($quantity > 0 && $quantity <= $item['q2']){
			return $item['price'];
		}elseif($quantity >= $item['q3'] && $quantity <= $item['q4']){
			return $item['price2'];
		}elseif($quantity >= $item['q5'] && $quantity <= '99999999'){
			return $item['price3'];
		}else{
			return "Call for Info";
		}
	}

	// $total_weight is the total weight of all purchase items
	// $options must be an assoc array of cart options
	public static function calculate_shipping($total_weight, $options)
	{

		// if shipping is completely free, we are already done!
		if($options['free_shipping']) return 0;

		// find the ship_per value
		$gross = $total_weight * $options['ship_per'];
		
		// cover free shipping
		if ($options['free_ship'] > 0 && $gross > $options['free_ship']) $gross = $options['free_ship'];
		
		// cover min shipping
		if ($options['min_ship'] > 0 && $gross < $options['min_ship']) $gross = $options['min_ship'];
		
		// cover max shipping
		if ($options['max_ship'] > 0 && $gross > $options['max_ship']) $gross = $options['max_ship'];
		
		return $gross;
		
	}

	public static function productSingleImage($wrapName, $image, $filetype = 'thumb', $allowDesc = true)
	{
		global $_config;
		?>
		<div class="<?php echo $wrapName; ?>">
			<a class="product_group" href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $image['url']; ?>" title="<?php echo $allowDesc ? $image['desc'] : '';?>" rel="item_<?php echo $image['item_id']; ?>">
				<div class="singleImage" >
					<img src="<?php echo $_config['upload_url']; ?>ecom/<?php echo $filetype; ?>/<?php echo $image['name']; ?>" />
				</div>
			</a>
		</div>
	<?php	
	}

	// 	returns an array of collection data, including product count
	// 	if an id is provided, returns only one row
	//		otherwise returns data for all collections
	//	if includeDrafts is set to false, doesn't return collections that are in draft mode
	public static function get_col_data($id = false, $includeDrafts = true)
	{
		// if $id is provided, returns data for that id, otherwise returns all
		$andClause 		= $id ? "  AND col.id = {$id}" : "";
		$draftsClause	= ! $includeDrafts ? "  AND col.`status`=1" : "";
		
		$query = "
			SELECT col.*,count(prod.id) as product_count
			FROM `ecom_collection` as col
			LEFT JOIN`ecom_prod_in_col` AS pINc
			  ON col.id = pINc.collection_id
			LEFT JOIN `ecom_product` as prod
			  ON prod.id = pINc.product_id
			WHERE col.id > 0
			{$andClause}
			{$draftsClause}
			GROUP BY col.title
		";
		$result = logged_query_assoc_array($query,null,0,array());
		return $result;
	}



	//returns an array of all active collections (no drafts)
	// if id is set, only returns that collection's data
	public static function get_active_collections($id = false)
	{
		// if $id is provided, returns data for that id, otherwise returns all
		$andClause = $id ? "  AND col.id = {$id}" : "";	
		
		return logged_query_assoc_array("
			SELECT col.*
			FROM `ecom_collection` as col
			WHERE col.id > 0
			{$andClause}
		",null,0,array());
	}

	//	get all collection_ids associated with Product Id
	//	returns an array of ids (the id is stored in both the index and the value)
	public static function get_collection_ids($product_Id)
	{
		$result = logged_query("
			SELECT `collection_id` 
			FROM `ecom_prod_in_col`
			WHERE `product_id` = {$product_Id}
		",0,array());
		$arColId = array();
		foreach($result as $row)
		{
			$arColId[$row['collection_id']] = $row['collection_id'];
		}
		return $arColId;
	}


	// returns category/collections/product: id/title/url/status/featured/firstImage
	public static function get_product_overview($orderBy = 'ORDER BY prod.status DESC, prod.featured DESC', $singleId = false)
	{
		// if an id was passed, narrow search to a single row
		$andClause = $singleId ? "AND prod.id = {$singleId}" : '';

		// first, do the query to get all the info
		// the dates are provided in the select so they can be used for the orderBy
		$productData = logged_query("
			SELECT cate.name as cat_name, prod.category_id AS cat_id, coll.title AS col_name, prod.*
			FROM `ecom_product` AS prod
			LEFT JOIN `ecom_category_data` AS cate
				ON prod.`category_id` = cate.`id`
			LEFT JOIN`ecom_prod_in_col` AS pINc
				ON prod.`id` = pINc.`product_id`
			LEFT JOIN `ecom_collection` AS coll
				ON pINc.`collection_id` = coll.`id`
			WHERE prod.`id` > 0
			{$andClause}
			{$orderBy}
		",0,array());
		
		// build an associative array of products with the id as the key
		//   present the collection names as an array: col_names
		$arProduct = array();
		$lastRow = -1;
		if ($productData) : foreach($productData as $row)
		{
			if ( $lastRow != $row['id'])
			{
				$arProduct[$row['id']]['title'] 			= $row['title'];
				$arProduct[$row['id']]['url'] 				= $row['url'];
				$arProduct[$row['id']]['featured'] 			= $row['featured'];
				$arProduct[$row['id']]['status'] 			= $row['status'];
				$arProduct[$row['id']]['cat_name'] 			= $row['cat_name'];
				$arProduct[$row['id']]['cat_id'] 			= $row['cat_id'];			
				$arProduct[$row['id']]['desc'] 				= $row['desc'];
				$arProduct[$row['id']]['short_desc']		= $row['short_desc'];
				$arProduct[$row['id']]['seo_title'] 		= $row['seo_title'];
				$arProduct[$row['id']]['seo_keywords'] 		= $row['seo_keywords'];
				$arProduct[$row['id']]['seo_description']	= $row['seo_description'];
				$arProduct[$row['id']]['date_created']		= $row['date_created'];
				$arProduct[$row['id']]['date_updated']		= $row['date_updated'];
				$arProduct[$row['id']]['price'] 			= $row['price'];

				
				
				
				$arCol = $row['col_name'] ? array($row['col_name']) : array();
				$arProduct[$row['id']]['col_names'] = $arCol;
			} else $arProduct[$row['id']]['col_names'][] = $row['col_name'];
					
			$lastRow = $row['id'];		
		} endif;
		
		// 	if an id wasn't passed, return the id keyed array of results, 
		// 		otherwise, return the single row
		if ($singleId === false) return $arProduct;
		return $arProduct[$singleId];
	}



	// returns true if all required data is present
	public static function canActivateProduct($productData)
	{

			return	$productData['title'] 		!= '' &&
				$productData['url'] 			!= '' &&
				//$productData['cat_name'] 		!= '' &&
				//isset($productData['col_names'][0])	  &&
				//$productData['col_names'][0]	!= '' &&
				$productData['seo_title'] 		!= '' &&
				$productData['seo_keywords'] 	!= '' &&
				$productData['seo_description']	!= '' &&
				$productData['price']			!= '';

	}



	public static function showCollectionNames($colNames)
	{
		foreach($colNames as $colName)
		{
			
			echo "{$colName}<br>";
		}
	}

	public static function redirect_invalid_shop_page($shopPage)
	{
		global $_config;
		$location = "Location: {$_config['admin_url']}modules/shopping_cart/{$_config['shopping_cart']['default_page']}";
		if( ! self::valid_shopping_admin($shopPage) ) header($location);
	}

	public static function valid_shopping_admin($shopPage)
	{
		global $_config;
		return ( in_array($shopPage, $_config['shopping_cart']['valid_admin_pages'] ));
	}

	/* possible new validate class function */
	// grab data from comma separated numeric index and return as an array of ints: false if error

	public static function get_collection_by_valid_cat($cat_id)
	{
		$result = logged_query_assoc_array("
			SELECT `id`, `title` 
			FROM `ecom_collection`
			WHERE `id` > 0
			  AND `status` > 0
			  AND `valid_category` like '%,{$cat_id},%'
			ORDER BY `title`
		",null,0,array());

		return $result;
	}

	public static function get_all_collections($enabled_only = true)
	{
		$status = $enabled_only ? 1 : 0;
		$result = logged_query_assoc_array("
			SELECT * 
			FROM `ecom_collection`
			WHERE `id` > 0
			  AND `status` >= {$status}
			ORDER BY `title`
		",null,0,array());
		if($result === false)$result = array();
		return $result;	
	}

	public static function get_valid_sellers()
	{
		$result = logged_query_assoc_array("
			SELECT `id`, CONCAT(`first_name`, `last_name`) as `name`, `email` 
			FROM `members`
			WHERE `id` > 0
			  AND (`status` > 1)
		",null,0,array());
		return $result;
	}

	// return all enabled categories (disabled categories as well if $enabled = false
	public static function get_all_categories($enabled_only = true)
	{
		$status = $enabled_only ? 1 : 0;
		$result = logged_query_assoc_array("
			SELECT * 
			FROM `ecom_category_data`
			WHERE `id` > 0
			  AND `status` >= {$status}
			ORDER BY `name`
		", 'id',0,array());
		if($result === false)$result = array();
		return $result;
	}

	// returns a string of valid category classes
	public static function get_valid_category_classes($str_valid_collection, $arCategories, $class_prefix = 'cat_select')
	{
		$arValidCol = trim($str_valid_collection, "\,");
		$arValidCol = explode(",", $arValidCol);

		$return = $class_prefix . '_include';
		foreach($arCategories as $tmpcat)
		{
			if (!in_array($tmpcat['id'], $arValidCol)) $return .= ' ' . $class_prefix . '_exclude_' . $tmpcat['id'];
		}
		return $return;
	}
	
}
?>