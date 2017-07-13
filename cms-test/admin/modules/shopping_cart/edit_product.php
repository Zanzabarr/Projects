<?php 
// initialize the page
$headerComponents = array('revisions');
$headerModule = 'shopping_cart';				// tells system where to look for includes folder and used to set header redirect folder
include('../../includes/headerClass.php');

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;
$isAdmin = $curUser['admin'] == 'yes';

//get shipping preferences
$ship_prefs = logged_query("SELECT * FROM ecom_ship_prefs WHERE id = 1",0,array());
$ship_prefs = $ship_prefs[0];

// instantiate the gallery
$galleryFile			= 'ecom';
$galleryUploadPath 		= $_config['upload_path'] . "ecom/";
$galleryUploadUrl		= $_config['upload_url'] . "ecom/";
$galleryTableName		= 'ecom_image';
$galleryTableId			= 'item_id';
$gallery = new gallery($galleryUploadPath, $galleryUploadUrl, $galleryTableName, $galleryTableId, $_config['ecom_img_text_desc'], $_config['ecom_img_html_desc'], $galleryFile );
$gallery->manage_title 	= "Product Images";	// Area Heading
$gallery->manage_tip	= "Upload Product images to the Staged area. Then place them in the Gallery section in the order you wish them to be displayed.";


// set up the exclusion data
// if the site has multiple product types, there needs to be a definition for each product

// provide an array of all fields that won't be entered into the db for this particular product type
$exclude_input = isset($_config['exclude_input']) ? $_config['exclude_input'] : array();


// set the db variables
$table 				= 'ecom_product';			// name of the table this page posts to
$mce_content_name	= 'desc';					// the tinyMCE editor always uses the id:content, 
												//   but db tables usually use something specific to that table for main content,
												//   eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$revision_table 	= 'ecom_product_rev';		// name of the revision table
$revision_table_id 	= 'ecom_prod_id'; 			// the id in the revision table that points to the id of the original table
$page_type			= 'ecom_product';			// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'index.php';				// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'prodid';					// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '';						// this variable is used to store the GET id value of above

$newPost	 		= false;					// records that this is not a new Post/Page 

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id);
$Revisions->setOrderByField('date_updated');


if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newPost = true;
	
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `ecom_product`
		WHERE `id` >0
		AND `url` REGEXP "^newproduct_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'newproduct_' . ++$slug_num;
	$result = logged_query("INSERT INTO `ecom_product` SET  title=:tmp_slug, url=:tmp_slug",0,array(":tmp_slug" => $tmp_slug));
	$page_get_id_val = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `ecom_product` WHERE `id` =:id",0,array(":id" => $page_get_id_val));
	$list = $list[0];
}
// if this arrived from revision history, get info
elseif ( $Revisions->isRevision() )
{
	// ensure the current user is entitled to change the version
	$new = logged_query_assoc_array("SELECT * FROM {$table} WHERE id = {$_GET[$page_get_id]} ORDER BY id DESC LIMIT 1",null,0,array()); 
	$list=$new[0];
	// if the current user isn't an admin and isn't the listed agent, kick out the current user
	// also, kick out non-admin if status = published
	if(! $isAdmin && ( ($curUser['user_id'] != $list['agent']) || $list['status'] ) ) {
		header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/{$parent_page}" );
		exit;
	}
	
	$page_get_id_val = trim($_GET[$page_get_id]);
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
	
	// we've already determined that the curUser is the assigned agent, 
	// if the logged in user isn't admin, make sure the 'agent' field is the logged in user
	if ($isAdmin) $list['agent'] = $curUser['user_id'];
	
	// get Collection data
	$collection = ecom_functions::get_collection_ids($page_get_id_val);
	$list['collection'] = $collection;
}
// if this is an edit of an existing post, get the post info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id])) {	
	$new = logged_query_assoc_array("SELECT * FROM {$table} WHERE id = {$_GET[$page_get_id]} ORDER BY id DESC LIMIT 1",null,0,array()); 
	$list=$new[0];
	// if the current user isn't an admin and isn't the listed agent, kick out the current user
	// also, kick out non-admin if status = published
	if(! $isAdmin && ( ($curUser['user_id'] != $list['agent']) || $list['status'] ) ) {echo 'hey, you do not belong';}

	$page_get_id_val = $_GET[$page_get_id];
	
	// get Collection data
	$collection = ecom_functions::get_collection_ids($page_get_id_val);
	$list['collection'] = $collection;
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/{$parent_page}" );
	exit;
}	

#==================
# process post
#==================
if(isset($_POST['submit-post'])){
	// set error types
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Published Version';
		$errorMessage = 'all fields must be complete, saved as draft';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Draft Version';
		$errorMessage = 'saved with incomplete fields on page';
	}
	
	// hack the weight
	$weightvalue = $ship_prefs['weight_type']=="LB" ? $_POST['weight_lb'] : $_POST['weight_kg'];
	if($ship_prefs['weight_type']=="LB") {
		$_POST['weight_kg'] = number_format($_POST['weight_lb']/2.2,2,".",",");
	} else {
		$_POST['weight_lb'] = number_format($_POST['weight_kg']*2.2,2,".",",");
	}
	
	// default product type is 1, if more than one type exists there must be validation/inclusion data for each
	// valid page types must be defined in /admin/modules/shopping_cart/system/config.php
	if($_POST['product_type']==2) $weightvalue=0;
	//validation keys
	$required[1] = array('category',  'title', 'url', 'price', 'seo_title', 'seo_description', 'seo_keywords');
	$required[2] = $required[1];
	
	$multi_input_index[1] = array();
	$multi_input_index[2] = array();
	
	$int_inputs[1] = array( );
	$int_inputs[2] = array( );

	$decimal_inputs[1] = array('price', 'weight_lb', 'weight_kg');
	$decimal_inputs[2] = array('price');

	$binary_inputs[1] = array();
	$binary_inputs[2] = array();
	
	// Convert Quantity max to 'infinite'
	if(isset($_POST['q2']) && $_POST['q2'] == '+'){
			$_POST['q2'] = '99999999';
		}elseif(isset($_POST['q4']) && $_POST['q4'] == '+'){
			$_POST['q4'] = '99999999';
		}elseif(isset($_POST['q6']) && $_POST['q6'] == '+'){
			$_POST['q6'] = '99999999';
		}

	// make sure the product type is a valid one!
	$_POST['product_type'] = 
		isset($_POST['product_type']) && 
		isset($_config['product_type']) &&	// array of valid product types in /admin/modules/shopping_cart/system/config.php
		count($_config['product_type']) &&
		array_key_exists($_POST['product_type'], $_config['product_type'])
		? $_POST['product_type'] : 1;
	$valid_prod_type = $_POST['product_type'];
	
	$testData = $_POST;
	unset($testData['collection'],$testData['submit-post'],$testData['opt']);
	$testform = new Form($testData);
	$testform->set_required_input($required[$valid_prod_type]);
	$testform->set_multi_check_input($multi_input_index[$valid_prod_type]);
	$testform->set_integer_input($int_inputs[$valid_prod_type]);
	$testform->set_decimal_input($decimal_inputs[$valid_prod_type]);
	$testform->set_binary_input($binary_inputs[$valid_prod_type]);
	$tmplist = $testform->validate();
	$errors = $testform->get_errors();
	// merge the form data with pre-existing data
	$list = array_merge($list, $tmplist);
	
	//*********************** special validation tests ***********************************//
	//if product is shipped, weight cannot = 0
	if($_POST['product_type']==1 && $weightvalue==0) {
		// set error messages
		$errors['weight_lb'][] = "Illegal Weight Value";
		$errors['weight_kg'][] = "Illegal Weight Value";
		$errorMessage = "Product Type is Shipped -- Weight must be greater than zero.";
	}
	// set empty array for collection data: default if errors occur
	$collection = array();
	if(isset($_POST['collection'])) 
	{
		// collection data may be passed as an array of ints or as a single integer
		//		If Products can be put in only one Collection, sets an integer: otherwise an array	
		// if it isn't an array, convert it
		if( is_array($_POST['collection']) )
		{
			//make sure array is valid:
			foreach($_POST['collection'] as $key => $val)
			{
				if(! is_pos_int($key) ) 
				{
					$errors['collection'][] = 'Illegal Collection Value';
					break;
				}
				$collection = $_POST['collection'];
			}
		}
		else // not array, do the conversion
		{
			// make sure we have a simple positive integer
			if(! is_pos_int($_POST['collection']) ) $errors['collection'][] = 'Illegal Collection Value';
			// good data, set $collection;
			else $collection[$_POST['collection']] = '1';
		}
	}
	$list['collection'] = $collection;

	// validate category
	if (! is_pos_int($list['category_id']) )
	{
		// set it to the first category
		$result = logged_query_assoc_array("SELECT `id`, `name` FROM `ecom_category_data` WHERE `id` > 0 LIMIT 1",null,0,array());
		$newId = $result[0]['id'];
		$newName = $result[0]['name'];
		$list['category_id'] = $newId;
		
		// set error messages
		$errors['category_id'][] ="Category does not exist";
		$errorMessage = "Category does not exist: arbitrarily set to '{$newName}' instead";
	}
	
	// validate uniqueness of url / title
	//get all existing products
	$products = logged_query_assoc_array("SELECT `id`, `url`, `title` FROM `{$table}`",null,0,array());
	$catUrl = array();
	$catTitle = array();
	foreach ($products as $product)
	{
		$catUrl[$product['url']] = $product;
		$catTitle[$product['title']] = $product;
	}
	// while not unique, append!
	$nameingError = false;
	while ( array_key_exists($list['url'], $catUrl) && ($_GET['option'] == 'create' || $catUrl[$list['url']]['id'] != $page_get_id_val ) ) 
	{
		$namingError = true;
		$list['url'] .= '-';
		$errors['url'][] = 'Url altered to be unique';
	}
	
	// if renaming occured, set alerts
	if (isset($namingError) && $namingError)
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Naming Error';
		$errorMessage = 'product name or url already exists, saved as draft with modified names';
	}

	// if an error was found, create the error banner
	
	if ($errors )
	{
		// translate errors to messages
		foreach($errors as $k => $v)
		{
			$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => $v[0]);
		}
	
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

	// save even if errors exist: but save as draft

	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
	{
		// grab the main data for the insert
		$post_data = $list;
		// remove extraneous data
		unset(
			$post_data['collection'], 
			$post_data['page_get_id_val'],
			$post_data['opt']
		);
		if( isset($exclude_input[$valid_prod_type]) ) : foreach($exclude_input[$valid_prod_type] as $exclude_field)
		{
			unset($post_data[$exclude_field]);
		} endif;
		// add in extra items
		$post_data['date_created'] = 'UTC_TIMESTAMP()';
		$post_data['date_updated'] = 'UTC_TIMESTAMP()';
		// insert into table
		$result = logged_array_insert($table, $post_data);
		$page_get_id_val = $result['insert_id'];
		$saveError = (bool) $result['error'];
		
		if(! $saveError)
		{
			// add in revision specific data
			$post_data[$revision_table_id] = $page_get_id_val;
			unset(
			$post_data['id']
			);
			$result = logged_array_insert($revision_table, $post_data);
			// don't worry if there was an error inserting into rev table
		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Product', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		
		
		// successfully created the page: no longer a new page!
		$newPost = false;
	}
	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
	{
		// get data to be posted to the db, and remove unwanted inputs
		$post_data = $list;
		unset(

			$post_data['page_get_id_val'], 
			$post_data['collection'], 
			$post_data['submit-post'], 
			$post_data['date_created'],
			$post_data['opt']
		);
		// for multi-product sites, remove product info irrelevant to this product type
		if( isset($exclude_input[$valid_prod_type]) ): foreach($exclude_input[$valid_prod_type] as $exclude_field)
		{
			unset($post_data[$exclude_field]);
		}endif;
		
		$post_data['date_updated'] = "UTC_TIMESTAMP()";
		$post_data['date_created'] = $list['date_created'];
		$page_get_id_val = trim($page_get_id_val);
		$post_data['id'] = $page_get_id_val;
		
		$where_clause = "WHERE `id` = '{$page_get_id_val}' LIMIT 1;";
		
		$result = logged_array_update($table, $post_data, $where_clause);
		$saveError = (bool) $result['error'];

		if(! $saveError)
		{
			// add in revision specific data
			$post_data['date_created'] = $list['date_created'];
			$post_data[$revision_table_id] = $page_get_id_val;
			unset($post_data['id']);
			$result = logged_array_insert($revision_table, $post_data);
			// don't worry if there was an error inserting into rev table
			$rev_ins_id = $result['insert_id'];
		}
		
		// because the admin's previously set data must be maintained,
		// if this isn't admin that is setting the data,
		// grab prior (where applicable) revision data for admin specific fields
		if (! $isAdmin && ! $saveError)
		{ //we need to maintain previous revision data if it exists
		
			// get old revision data
			$old_rev_data = logged_query_assoc_array("
				SELECT `status`, `featured`, `sale_price`
				FROM `{$revision_table}`
				WHERE `id` < '{$rev_ins_id}' 
				ORDER BY `id` DESC
				LIMIT 1;",null,0,array());
			if ($old_rev_data)
			{	
				$old_rev_data = $old_rev_data[0];
				logged_query("UPDATE `{$revision_table}` SET 
					`status` 				= '{$old_rev_data['status']}',
					`featured`		 		= '{$old_rev_data['featured']}',
					`sale_price`			= '{$old_rev_data['sale_price']}'
					WHERE `id` = '{$rev_ins_id}' LIMIT 1;",0,array());
			}
		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Product', 'message' => 'there was an error writing to the database', 'type' => 'error' );
	}
	
	// now update the collections data
	// first, delete all previous collections for this product
	logged_query("DELETE FROM `ecom_prod_in_col` WHERE `product_id` = {$page_get_id_val}",0,array());
	
	// now add all current collections for this product
	if( isset($collection) ) : foreach($collection as $colId => $garbage)
	{
		logged_query("INSERT INTO `ecom_prod_in_col` VALUES ({$page_get_id_val}, {$colId})",0,array());
	} endif;
	// now we can also update the product options if they'be been passed
	logged_query("DELETE FROM `ecom_product_options` WHERE `prod_id` = {$page_get_id_val}",0,array());
	//TODO: validate data
	if (isset($_POST['opt']) && count($_POST['opt'])) :foreach($_POST['opt'] as $k=>$v){
		foreach($v as $x=>$y){
			if($x !== 'name'){
				logged_query("INSERT INTO `ecom_product_options` (`prod_id`,`opt_name`,`option`,`price`,`weight`) 
				VALUES (:prod_id, :opt_name, :option, :price, :weight);",0,array(
					":prod_id" => $page_get_id_val,
					":opt_name" => $v['name'],
					":option" => $y['option'],
					":price" => $y['price'],
					":weight" => $y['weight']
				));
			}
		}
	} endif;
}
if (! isset($message)) $message=array();

// 	PRODUCT TYPE
// 	Only include if ecom has more than one type of product
if ( isset($_config['product_type']) && count($_config['product_type']) > 1 ) {
	$val = isset($list['product_type']) ? htmlspecialchars_decode($list['product_type']) : '';
	$input_product_type = new inputField( 'Product Type', 'product_type');
	$input_product_type->toolTip("Choose what kind of product this is." );
	$input_product_type->type('select');
	$input_product_type->selected($val);
	$input_product_type->arErr($message);

	//		create  options
	foreach ($_config['product_type'] as $key => $value)
	{
		$input_product_type->option(  
			$key, 
			$value 
		);
	}
}

// page title
$val = isset($list['title']) ? htmlspecialchars_decode($list['title']) : '';
$input_ecom_title = new inputField( 'Product Title', 'title' );
$input_ecom_title->toolTip('Title as it appears in the Product Homepage.');
$input_ecom_title->value( $val );
$input_ecom_title->extraClasses(array('test', 'testb'));
$input_ecom_title->extraAttributes(array('data-test' => 'info1', 'data-test-2' => 'info2'));
$input_ecom_title->counterMax(100);
$input_ecom_title->size('small');
$input_ecom_title->arErr($message);

// url	
$val = isset($list['url']) ? htmlspecialchars_decode($list['url']) : '';
$input_url = new inputField( 'Url', 'url' );
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value( $val );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);

// featured
$val = isset($list['featured']) ? htmlspecialchars_decode($list['featured']) : '';
$input_featured = new inputField( 'Featured', 'featured' );
$input_featured->toolTip('Make this one of the Featured Products.');
$input_featured->value( $val  );
$input_featured->type( 'checkbox' );
$input_featured->arErr($message);

// status
$val = isset($list['status']) ? htmlspecialchars_decode($list['status']) : '0';
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val );
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// seo title
$val = isset($list['seo_title']) ? htmlspecialchars_decode($list['seo_title']) : '';
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( $val );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$val = isset($list['seo_description']) ? htmlspecialchars_decode($list['seo_description']) : '';
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('up to 150 characters - Short sales pitch for this page - include keywords where possible');
$input_seo_description->type('textarea');
$input_seo_description->value( $val );
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$val = isset($list['seo_keywords']) ? htmlspecialchars_decode($list['seo_keywords']) : '';
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('3 to 5 phrases that you want people to type into Google to find this page');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value( $val );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);

// 	Category Select
$val = isset($list['category_id']) ? htmlspecialchars_decode($list['category_id']) : '';
$input_menu_posn = new inputField( 'Category', 'category_id');
$input_menu_posn->toolTip("Select the Category which this Product belongs to." );
$input_menu_posn->type('select');
$input_menu_posn->selected($val);
$input_menu_posn->arErr($message);
	
//		build the option set
$objCategory = new ecom_category();
$productCategories = $objCategory->get_nested();
//		create  options
foreach ($productCategories as $category)
{
	$input_menu_posn->option(  
		$category['id'], 
		$category['spaced_name'] 
	);
}
/************************* item specific entries ***********************/
// price
$val = isset($list['price']) ? htmlspecialchars_decode($list['price']) : '';
$input_price = new inputField( 'Regular Price', 'price' );
// add relevant exclude classes
$input_price->extraClasses(get_exclude_classes('price', $exclude_input));
$input_price->value( $val );
$input_price->counterMax(15);
$input_price->size('small');
$input_price->arErr($message);

// sale
$val = isset($list['sale']) ? htmlspecialchars_decode($list['sale']) : '';
$input_sale = new inputField( 'Sale Price', 'sale' );	
// add relevant exclude classes
$input_sale->extraClasses(get_exclude_classes('sale', $exclude_input));
$input_sale->value( $val );
$input_sale->counterMax(15);
$input_sale->size('small');
$input_sale->arErr($message);

// price2
$val = isset($list['price2']) ? htmlspecialchars_decode($list['price2']) : '';
$input_price2 = new inputField( 'Price Per', 'price2' );	
// add relevant exclude classes
$input_price2->extraClasses(get_exclude_classes('price2', $exclude_input));
$input_price2->value( $val );
$input_price2->counterMax(15);
$input_price2->size('small');
$input_price2->arErr($message);

// price3
$val = isset($list['price3']) ? htmlspecialchars_decode($list['price3']) : '';
$input_price3 = new inputField( 'Price Per', 'price3' );	
// add relevant exclude classes
$input_price3->extraClasses(get_exclude_classes('price3', $exclude_input));
$input_price3->value( $val );
$input_price3->counterMax(15);
$input_price3->size('small');
$input_price3->arErr($message);

// weight
$wunit = $ship_prefs['weight_type'] == "LB" ? "weight_lb" : "weight_kg";
$val = isset($list[$wunit]) ? htmlspecialchars_decode($list[$wunit]) : '';
$input_weight = new inputField( 'Weight in '.$ship_prefs['weight_type'], $wunit );
// add relevant exclude classes
$input_weight->extraClasses(get_exclude_classes($wunit, $exclude_input));
$input_weight->value( $val );
$input_weight->counterMax(150);
$input_weight->size('small');
$input_weight->arErr($message);


// set the header varables and create the header
$pageResources = $gallery->headData();
$pageResources .="
<link rel='stylesheet' type='text/css' href='styles.css' />
<script type='text/javascript' src='js/edit_product.js'></script>
";//
$pageInit->createPageTop($pageResources);

$usedSlugs = array();  // array of slugs that have already been used
$slugs=logged_query("SELECT `url` FROM {$table}  WHERE id > 0",0,array());
if($slugs && count($slugs)>0) {
	foreach($slugs as $row) {
		$usedSlugs[] = $row['url'];
	}
}

?>
<script type="text/javascript">
	curSlug = "<?php echo isset($list['url']) ? $list['url'] : ''; ?>";
	usedSlugs = new Array();
<?php foreach($usedSlugs as $slug) : ?>
	usedSlugs.push("<?php echo $slug; ?>");
<?php endforeach; ?>
</script>

 <div class="page_container">
	<div id="h1"><h1>Products</h1></div>
    <div id="info_container">
	
		<?php
		// add subnav
		$pg = 'product';	include("includes/subnav.php");
		
		echo "<hr>";
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?{$page_get_id}={$page_get_id_val}&option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the Product.">Product Properties</h2><br />
			<div id="prop-toggle-wrap">
				<input type='hidden' name='category_id' value='12'>
			<?php
				// if there is more than one product type, choose it
				if(isset($input_product_type)) $input_product_type->createInputField();
			
				// name the title and url
				$input_ecom_title->createInputField();
				$input_url->createInputField();
				
				//choose the category
				$input_menu_posn->createInputField();
				
				// ------------------------- COLLECTION INPUT ----------------------------//

				// if there are collections set display the dropdown for single collection sites 
				// 		or multi-check for sites allowing multiples 
				
				$collect_options = ecom_functions::get_all_collections();

				//if this site allows collections to be limited to only certain categories, get the category data
				if(isset($_config['limit_collection']) && $_config['limit_collection']) $cats = ecom_functions::get_all_categories();

				if (count($collect_options))
				{
					// if this is a single collection max site: display the drop down
					if(isset($_config['one_collection_per_product']) && $_config['one_collection_per_product'])
					{
						$val = isset($list['collection']) ? $list['collection'] : '0';
						$input_collections = new inputField( 'Collection', 'collection');
						$input_collections->toolTip("Select the collection to which this product belongs");	
						$input_collections->type('select');
						$input_collections->selected( $val );
						
						//build option set
						foreach ($collect_options as $co){
							// build the include/exclude class string if collections have limited category availability
							if(isset($_config['limit_collection']) && $_config['limit_collection']) $str = ecom_functions::get_valid_category_classes($co['valid_category'], $cats);
							else $str = '';
							$input_collections->option( $co['id'] , $co['title'], array('class' => $str ) );
						}
						// display dropdown
						$input_collections->createInputField();
					}
					else  //this site allows multiple collections (standard): display the checkbox matrix
					{
						// build checkbox array
						$check_options =array();
						foreach ($collect_options as $co){
							// build the include/exclude class string if collections have limited category availability
							if(isset($_config['limit_collection']) && $_config['limit_collection']) $str = ecom_functions::get_valid_category_classes($co['valid_category'], $cats, 'cat_check_group');
							else $str = '';
							$check_options[] = array( 
								'id' 	=> 	$co['id'],
								'title' =>	$co['title'],
								'class' => 	$str
							);
						}

						// build the Collections table
						$checked_items = isset($list['collection']) ? $list['collection'] : array();
						if ($checked_items === false) $checked_items = array();
						$check_group_name 		= 'collection';
						$check_group_label 		= 'Collection';
						$check_group_table_id	= 'collection_table';
						$check_group_columns 	= 3;
						$check_group_title 		= 'Select the Collections to which this product belongs.';
						$as_radio				= false;			
						$check_group_class = '';
								
						//display it as a list
						display_checkbox_list(
							$check_options,
							$check_group_name,
							$check_group_label,
							$checked_items,
							$check_group_table_id,
							$check_group_title,
							$check_group_columns,
							$as_radio,
							$check_group_class
						);	
					}
				}
				
				// ------------------------ END COLLECTIONS --------------------------------//

				// admin fields: only admin can publish these
				if($isAdmin) $input_featured->createInputField();
				if($isAdmin) $input_status->createInputField();
				
				// agent	
				// get all known agents
				$all_agents = logged_query_assoc_array('
					SELECT U.user_id, U.username
					FROM auth_users AS U
					LEFT JOIN auth_users_permit AS P
					ON U.user_id = P.user_id 
					WHERE U.user_id = 1
					   OR P.module = "shopping_cart"
				',null,0,array());
				
				// if there is more than one valid agent(main user), create the agents dropdown
				if ($all_agents !== false && count($all_agents) > 1)
				{
					if($isAdmin)
					{
						$val = isset($list['agent']) ? htmlspecialchars_decode($list['agent']) : '0';
						$input_agent = new inputField( 'Agent', 'agent' );	
						$input_agent->toolTip('Select an Agent to represent this Sale');
						$input_agent->type('select');
						$input_agent->selected( $val );
						$input_agent->size('small');
						$input_agent->option( 0 ,"No Agent Selected" );
						
						foreach($all_agents as $cur_agent)
						{
							$input_agent->option( $cur_agent['user_id'] , $cur_agent['username']);
						}
						$input_agent->arErr($message);

						 $input_agent->createInputField();
					}
					else  // since we are here but not an admin, this must be the originator:					
					{			// make this user the agent
						echo "<input type='hidden' name='agent' value='{$curUser['user_id']}'>";					
					}
				}
				else // there is only one agent: main user, set the agent field to the main user
				{
					echo "<input type='hidden' name='agent' value='1'>";	
				}
				?>
			</div><!-- end prop_wrap -->		   
			
			<div id="pricing">
			
			 <?php $inf = '99999999'; ?>
			
				<h2 id="pricing-toggle" class="tiptip toggle" title="Product Pricing">Pricing</h2><br />
				<div id="pricing-toggle-wrap">
				 <div > 
                         <div id="quan1" >
                            <div class='input_wrap'>
								<label>Quantity </label>
								<div class="input_inner">
									<input id="q1" name="q1" type="text" class="quantity" value="0" readonly='readonly'/> 
									TO
									<input id="q2" name="q2" type="text" class="quantity" value="<? echo isset($list['q2']) && $list['q2'] && $list['q2'] != '99999999' ? $list['q2'] : '+' ; ?>" readonly='readonly' />
								</div>
							</div>
							<?php
								$input_price->createInputField();
							?>
							<div class="price_buttons">
								<a href="#" id="open2">Add Price</a>
							</div>
						 </div>
                         <div id="quan2" style="display:none;width:600px;">  
							 <div class='input_wrap'>
								<label>Quantity Group 2: </label>
								<div class="input_inner">
									<input id="q3" name="q3" type="text" class="quantity" value="<? echo isset($list['q3']) ? $list['q3'] : '' ; ?>" readonly='readonly' />
									TO
									<input id="q4" name="q4" type="text" class="quantity" value="<? echo isset($list['q4']) && $list['q4'] && $list['q4'] != '99999999' ? $list['q4'] : '+' ; ?>" readonly='readonly'  />
								</div>
							</div>
                            <?php
								$input_price2->createInputField();
							?>
							<div class="price_buttons">
								<a href="#" id="close2" style="display:none;">Delete Price - </a>
								<a href="#" id="open3">Add Price</a>
							</div>
                         </div>
                         <div id="quan3" style="display:none; width:600px;">
                            <div class='input_wrap'>
								<label>Quantity Group 3: </label>
								<div class="input_inner">
									<input id="q5" name="q5" type="text" class="quantity" value="<? echo isset($list['q5']) ? $list['q5'] : '' ; ?>" readonly='readonly'/>
									TO
									<input id="q6" name="q6" type="text" class="quantity" value="+" readonly='readonly'/>
								</div>
							</div>
							<?php
								$input_price3->createInputField();
							?>
							<div class="price_buttons">
								<a href="#" id="close3">Delete Price</a>
							</div>
						 </div>
                      </div>
					  <hr style="width:70%;margin-left:0;" />
					  <?php $input_sale->createInputField(); ?>
				</div>
			</div>
			
			<!-- specs wrap -->
			<div id="specifications">
				<h2 id="specs-toggle" class="tiptip toggle" title="Product Specifications">Specifications</h2><br />
				<div id="specs-toggle-wrap">
				<?php
				$input_weight->createInputField();
				$otherunit = $wunit == "weight_lb" ? "weight_kg" : "weight_lb";
				?>
				<input type="hidden" name="<?php echo $otherunit; ?>" id="<?php echo $otherunit; ?>" value="<?php echo isset($list[$otherunit]) ? $list[$otherunit] : 0; ?>" />
				<label class="tipRight" title="Appears on a page with multiple products.">Small Description</label>
				<?php if (isset($message['inline']) && array_key_exists('short_desc', $message['inline'])) : ?>
				<span class="<?php echo $message['inline']['short_desc']['type'] ;?>" style="margin-left:10px;"><?php echo $message['inline']['short_desc']['msg'] ; ?> </span>
				<?php endif; ?>
				
				<textarea style="width:65%;margin-left:2em;height:30px;margin-top:1em;" name="short_desc"><?php echo isset($list['short_desc']) ? strip_tags(htmlspecialchars_decode($list['short_desc'])) : ''; ?></textarea>
				<div class="clear"></div>
				<label class="tipRight" title="This information appears in the individual item description">Full Description</label>
				<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>" style="margin-left:10px;"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
				<?php endif; ?>
				
				<?php
				// create tinymce
				$jump_target = 'shopping/products/'.$list['url'];
			
				$editable = array(
					'editable_class' => 'mceEditor',
					'attributes' => array(
						'name' => 'desc',
						'id' => 'content',
						'data-jump-type' => "front",
						'data-jump' => "../../../".$jump_target
					),
					'secure_data' => array(
						'id-field' => 'id',				// req for save && upload
						'id-val' => $list['id']	// req for save && upload
					)
				);
				$wrapper = getContentWrapper($editable);
				echo $wrapper['open'];
				echo isset($list['desc']) ? htmlspecialchars_decode($list['desc']) : '' ;
				echo $wrapper['close'];
				?>
				
				<div class='clear'></div>
				<label class="tipRight" title="This information appears in the individual item description">Technical Specifications</label>
				
				<?php
				$editable = array(
					'editable_class' => 'mceEditor',
					'attributes' => array(
						'name' => 'specs',
						'id' => 'specs',
						'data-jump-type' => "front",
						'data-jump' => "../../../".$jump_target
					),
					'secure_data' => array(
						'id-field' => 'id',				// req for save && upload
						'id-val' => $list['id']	// req for save && upload
					)
				);
				$wrapper = getContentWrapper($editable);
				echo $wrapper['open'];
				echo isset($list['specs']) ? htmlspecialchars_decode($list['specs']) : '' ;
				echo $wrapper['close'];
				?>


			</div><!-- end specs toggle -->
			</div><!-- end specs_wrap -->
			
			<!-- options wrap -->
			<div id="options">
				<h2 id="options-toggle" class="tiptip toggle" title="Product Options">Product Options</h2>
				<div id="options-toggle-wrap">	
				<script type="text/javascript">
					var setcount = 0;
				</script>		
				
<?php					//------------------------------------------------------------------------------options
			if ( isset($list['id']) && is_pos_int($list['id']) ) 
			{
				$opts = logged_query_assoc_array("SELECT * FROM `ecom_product_options` WHERE `prod_id` = '{$list['id']}' ORDER BY `id` asc  ",null,0,array());
			}
			else $opts = array();
						
						//-- get distinct option names
						$main_opt = array();
						foreach($opts as $k=>$v){
    						if (!in_array($v['opt_name'],$main_opt)) array_push($main_opt,$v['opt_name']);

						}
						$maincount = 0;
						foreach($main_opt as $main){
							
							// option group
						echo '
						<label for="'.$maincount.'_set">Option:</label>
                        <input id="'.$maincount.'_set"  style="margin-left:105px;" name="opt[options_'.$maincount.'][name]" type="text" value="'.$main.'"/>
						<img src="img/delete.png" alt="delete" class="deleteoptionset" id="deloption_'.$maincount.'"/>
						<input name="add" type="button" value="Add Option" class="addop" id="'.$maincount.'_addop">
                        <br>
						';
						$subcount = 0;
							
							foreach($opts as $k=>$v){
								if ($v['opt_name'] == $main){
								++$subcount;
								echo '
								<label for="option'.$maincount.'_'.$subcount.'" class="set_'.$maincount.'" style="margin-left:185px;"></label>
								<p id="option'.$maincount.'_'.$subcount.'" class="option'.$maincount.'">Option:</p><input id="option'.$maincount.'_'.$subcount.'" name="opt[options_'.$maincount.']['.$subcount.'][option]" type="text" class="medinput focus'.$subcount.'" value="'.$v['option'].'"/>
								<p id="option'.$maincount.'_'.$subcount.'" class="option'.$maincount.'">Price Difference:</p><input id="option'.$maincount.'_'.$subcount.'" name="opt[options_'.$maincount.']['.$subcount.'][price]" type="text" class="smallinput" value="'.$v['price'].'"/>
								<p id="option'.$maincount.'_'.$subcount.'" class="option'.$maincount.'">Weight Difference:</p><input id="option'.$maincount.'_'.$subcount.'" name="opt[options_'.$maincount.']['.$subcount.'][weight]" type="text" class="smallinput" value="'.$v['weight'].'"/>
								<img src="img/delete.png" alt="delete" class="deleteoption" id="del_option'.$maincount.'_'.$subcount.'"/>
								<br />
								';
								?>
						<script type="text/javascript">
						setcount += 1;
						</script>
                        <?php
								}
							}
						++$maincount;
						}
                        ?>
						<input type='hidden' value='<?php echo $maincount; ?>' id="maincount">
                        <br />
                        <input name="addset" type="button" value="Add Option Set" id="addset" class="tipRight" title="Save Product before adding each Option Set." />
                        <br /><br />
			</div><!-- end specs toggle -->
			</div><!-- end specs_wrap -->
<?php
			
//------------------------------------------------ end options
	?>								
			
			
			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" ;" title="Search Engine Optimization fields">Meta Tags</h2><br />
			<div id="seo-toggle-wrap">
			<?php
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
			?>
			</div><!-- end SEO area -->
                       
                            
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<?php if (! $newPost )  : ?>
			<input name="page_get_id_val" type="hidden" value="<?php echo $page_get_id_val; ?>"/>
			<?php endif ?>
			<input name="submit-post" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>


		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<hr />
		
		<?php // include the uploader 
		
		$uploadTitle = "Product Uploads";
		$prodImageTitle = "Product Pictures";
		if ( ! $newPost )
		{
			// include the product image gallery section
			$gallery->buildGalleryArea($page_get_id_val);
		
			// $uploadPage and uploadPageType must be set before including uploads (they are page_id & page_type in tables: pictures & uploads
			if(isset($list['product_type']) && $list['product_type']==2) {
				$uploadPage = $page_get_id_val;
				$uploadPageType = $page_type;
				$uploadTitle = "Upload Downloadable Product Here";
				include("{$_config['components']}uploading/uploads.php");
			}
		} else {
			echo "<div class='clearFix'></div><h2 class='tiptip' id='image-toggle' style='color: grey; display:inline-block;' title='New Product must be saved before Product Images can be uploaded.'>{$prodImageTitle}</h2>";
			echo "<div class='clearFix'></div><h2 class='tiptip' id='image-toggle' style='color: grey; display:inline-block;' title='New Product must be saved before images and files can be uploaded.'>{$uploadTitle}</h2>";
		}
		?>
		
		<?php	// ----------------------------------- revisions -------------------------------------

		// add GET data to the return url
		$extraGET = array($page_get_id => $page_get_id_val);

		// build the Revision Area
		$Revisions->createRevisionsArea($page_get_id_val, array(),$extraGET);
		// end revisions
?>	
	</div>
</div>	
	

<?php 
//phpinfo();
include($_config['admin_includes'] . "footer.php"); 

// returns an array of classnames indicating which product types this field is not valid for
// returns empty array if only one product type
// parms	fieldname:		name of the input field to get array of classes
// 			exclude_input:	multi-dim array (one index for each product type) (array of input fields that product ignores)
function get_exclude_classes($fieldname, $exclude_input = array() )
{
	global $_config;
	// if there is only one product type exclusion classes aren't required
	if( !isset($_config['product_type']) || count($_config['product_type']) < 2 ) return array();
	
	// start with an include class for all of them; used to re-open all items on toggling
	$classnames = array('product_include');
	// get the product type keys
	foreach ($_config['product_type'] as $type_key => $value)
	{
		//check in each of the exclude arrays for exclusions
		if( isset($exclude_input[$type_key]) && in_array($fieldname, $exclude_input[$type_key]) )
			$classnames[] = "product_exclude_" . $type_key;
	}
	return $classnames;	
}