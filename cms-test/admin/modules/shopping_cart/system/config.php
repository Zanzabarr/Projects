<?php
// config for the module
// Even if it is empty, every module must include a config file

####################################
##     PRODUCT IMAGE SETTINGS     ##
####################################
$_config['ecom_img_html_desc'] = false;		// enable html image descriptions (for presentation on the product page)
$_config['ecom_img_text_desc'] = true;		// enable simple text image descriptions (appear as banner under the image when
											// 		fullsize in products page
####################################
##         Product Types          ##
####################################
// THESE ARE LEFT AS EMPTY ARRAYS UNLESS USED
// BECAUSE THEY ARE REQUIRED FOR INTERNAL CHECKS, THEY CANNOT BE SET IN ADMIN, ONLY HERE

// either empty array if only one product type exists, otherwise an array of valid types
// all product types share the same table, unused entries in the table are left as null
$_config['product_type'] = array(
	1 => 'Shipped', 
	2 => 'Download'
//	3 => 'Heavy Equipment', 
//	4 => 'Trailers'
);
$_config['exclude_input'] = array (
//	1 => array('capacity', 'door', 'pin', 'bed_height', 'interior_height', 'height', 'width', 'expandable', 'floor', 'axle_type', 			'axle_composition'
//		), 
//	2 => array('capacity', 'door', 'pin', 'bed_height', 'interior_height', 'height', 'width', 'expandable', 'floor', 'axle_type', 			'axle_composition'),
//	3 => array('capacity', 'door', 'pin', 'bed_height', 'interior_height', 'height', 'width', 'expandable', 'floor', 'axle_type', 			'axle_composition'
//		), 
//	4 => array('capacity', 'door', 'pin', 'bed_height', 'interior_height', 'height', 'width', 'expandable', 'floor', 'axle_type', 			'axle_composition')
);	

####################################
##      PREFERENCES SETTINGS      ##
####################################
##  	 SET THESE IN CMS		  ##
##	ADMIN->PRODUCTS->PREFERENCES ##
####################################

//get preferences
$prefs = logged_query_assoc_array("SELECT * FROM ecom_preferences WHERE id=1",null,0,array());
foreach($prefs[0] as $k => $v) {
	$_config['shopping_cart']['preferences'][$k] = $v;
}

	####################################
	##      COLLECTION SETTINGS       ##
	####################################

	$_config['limit_collection'] = $_config['shopping_cart']['preferences']['limit_collection'];

	// in rare cases, we want each product to have only one category 
	//		(On product page, collection is selected from a dropdown instead of the default checkbox matrix)
	$_config['one_collection_per_product'] = $_config['shopping_cart']['preferences']['one_collection_per_product'];

	####################################
	##     PAYMENT/ORDER SETTINGS     ##
	####################################
// these  values are required if site accepts payments
	//will be 'live' if sending to real payment processor, 'dev' for sandbox
	$_config['environment'] = $_config['shopping_cart']['preferences']['environment'];
	
	//will be false if live, true if dev
	$_config['sandbox'] = $_config['environment']=="live" ? false : true;
	
	// enter the appropriate currency code (USD: US, CAD: Canada)
	$_config['currency'] = $_config['shopping_cart']['preferences']['currency'];

/* 	Including a note with payment. If set to "1," customer will not be prompted 
**	to include a note. This is optional; if omitted or set to "0," customer will 
**	be prompted to include a note.
*/
	$_config['no_note'] = $_config['shopping_cart']['preferences']['no_note'];
	// Optional label that will appear above the note field (maximum 40 characters)
	$_config['note_comment'] = $_config['shopping_cart']['preferences']['note_comment'];

/*	Shipping address. If set to "1," your customer will not be asked for a shipping address at Paypal. 
**	If set to "0," customer will be prompted to include a shipping address. 
**	If set to '2', address must be filled in (more reliable than 1)
*/	
	$_config['no_shipping'] = $_config['shopping_cart']['preferences']['no_shipping'];
	
// If there's a cart
	$hasCart = $_config['shopping_cart']['preferences']['hasCart']==1 ? true : false;
// if there's pricing
	$includePricing = $_config['shopping_cart']['preferences']['includePricing']==1 ? true : false;
// main email to receive order information
	$_config['orderemail'] = $_config['shopping_cart']['preferences']['orderemail'];
	
//gateway
	$_config['gateway'] = $_config['shopping_cart']['preferences']['gateway'];
	
//gateway preferences
	$gatewayprefs = logged_query_assoc_array("SELECT * FROM `ecom_{$_config['gateway']}_prefs` WHERE id = 1",null,0,array());
	//will be email address if gateway is paypal, store_id if moneris
	switch($_config['gateway']) {
		case "nogateway":
			break;
		case "paypal":
			$_config['store_id'] = $gatewayprefs[0][$_config['environment']."_paypal"];
			break;
		case "moneris":
			$_config['store_id'] = $gatewayprefs[0]['store_id'];
			break;
	}

####################################
##	   END PREFERENCES FROM CMS   ##
####################################
	
####################################
##     SHOPPING CART SETTINGS     ##
####################################
// set the cartname as it will appear in the frontend url
$_config['cartName'] = 'shopping';														
$_config['customNames'][$_config['cartName']] = 'shopping_cart';
$_config['path']['shopping_cart'] = $_config['site_path'] . $_config['cartName'] . '/';

####################################
##        COUPON SETTINGS         ##
####################################
// TODO: much of the frontend is in place, just needs to be confirmed in the cart.php
// THESE ARE TURNED OFF BECAUE THEY AREN'T PLAYING NICE WITH PAYPAL - NEEDS TO BE FIXED
$_config['coupons'] = false; // need to do work on the backend to be able to include these!

####################################
##         CODED SETTINGS         ##
####################################

// THESE BELOW RARELY CHANGE AND WE DON'T WANT CLIENTS CHANGING THEM IN ADMIN - KEEP THEM HERE

// dictates many things, largely what pages/options will be available in the backend and frontend
// at current, only the core pages are done.

// core pages are always valid and include:
/*	category.php
*	collections.php
*	edit_category.php
*	edit_collection.php
*	edit_product.php (many elements of the page need to be enabled)
*	options.php (many elements of the page need to be enabled)
*	product.php
*
* 	things that may be enabled
* 	cart
*	shipping_details
*	order info
* 	home.php (includes order info)
*/

//$_config['shopping_cart']['frontend']['default_page'] = 'orders.php'; //first page seen in module admin

// set up the config variables
$_config['shopping_cart']['valid_admin_pages'] = array();

// set up first page seen in Products admin
if ( $hasCart && $includePricing )
{
	$_config['shopping_cart']['default_page'] = 'orders.php';
	$_config['shopping_cart']['valid_admin_pages'][] = 'orders.php';
} else {
	$_config['shopping_cart']['default_page'] = 'product.php';
}

// mod_special is an array of key value pairs used to link a page (in edit_page.php) to a module slug
// $key = "Blog";		// Key is the name of the special page (as it appears as a select option) 
// $value = "Blog";		// value is the slug slug; 
//$_config['mod_special'][$key] = $value; 

// create option for shop base
$_config['mod_special'][ucwords($_config['cartName'])] = $_config['cartName'];

if( $hasCart )
{
	// checkout
	$_config['mod_special']['Checkout'] = $_config['cartName'] . '/checkout';
	// cart
	$_config['mod_special']['Shopping Cart'] = $_config['cartName'] . '/cart';
	$_config['mod_special']['Shopping Cart Success'] = $_config['cartName'] . '/success';
}

// featured items
$_config['mod_special']['Featured Products'] = $_config['cartName'] . '/featured';

// and now, the categories
// get all categories
$all = array();
$cart_cats = logged_query_assoc_array("
	SELECT CONCAT( REPEAT( '-', ( COUNT( parent.category_data_id ) -1 ) ) , data.name ) AS spaced_name, data.*, COUNT( parent.category_data_id ) AS depth
	FROM ecom_category AS node, ecom_category AS parent, ecom_category_data AS data
	WHERE `node`.`category_data_id` = `data`.`id`
	AND data.status =1
	AND data.id > 0
	AND node.lft BETWEEN parent.lft AND parent.rgt
	GROUP BY node.category_data_id
	ORDER BY node.lft
",null,0,array());

foreach($cart_cats as $cart_cat)
{

	$_config['mod_special']['Category: ' . $cart_cat['spaced_name'] ]	= $_config['cartName'] . '/category/' . $cart_cat['url'] ;

}

//	now for the collections:
$cart_coll = logged_query_assoc_array("SELECT `title` AS name, `id`, `url` FROM `ecom_collection` WHERE `status` = '1' AND `id` >0 ORDER BY `name` ASC",null,0,array());
if($cart_coll) : foreach($cart_coll as $row)
{
	$_config['mod_special'][ucwords('collection: ' . $row['name'])]	= $_config['cartName'] . '/collection/' . $row['url'] ;
} endif;
/*include_once($_config['admin_path'].'modules/shopping_cart/includes/functions.php');*/
?>