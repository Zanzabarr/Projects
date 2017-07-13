<?php
// start building userPath
$basePath = $_config['site_path'].'shopping_cart';
include_once("includes/functions.php");

// initialize category object
$objCategory = new ecom_category();

$userPath = $basePath;
$index = 1;
while( get_uri( $index ) )
{
	$userPath .= '/'.$uri[$index];
	$index++;
}
//get $info
	$pageload=logged_query("SELECT * FROM `pages` WHERE `slug` = :page_base AND `id` > 0",0,array(":page_base" => $page_base));
	$info=$pageload;
// parse the uri
// we have only a handful of pages that could apply:
// product list (could be called by collection/categor[y|ies]/all[default]/featured or a combination)
// individual product (/product/product_name)
// cart page
// checkout page
// success page
// shipping page
// order page
// if none of the above: page not found ? full list ?

$hasCart = $_config['shopping_cart']['preferences']['hasCart'] == 1 ? true : false;

$cart_command = get_uri(1);
//if( ! $cart_command || $cart_command == 'category' ) $scData = doCat($uri);
if( $cart_command == 'products' ) $scData = doProduct($uri);
elseif( $cart_command == 'cart' && $hasCart ) $scData = doCart($uri);
//if( $uri[1] == 'category' ) $scData = doCat($uri);
elseif( $cart_command == 'checkout' && $hasCart ) $scData = doCheckout($uri);
elseif( $cart_command == 'success' && $hasCart ) $scData = doSuccess($uri);
elseif( $cart_command == 'shipping' && $hasCart ) $scData = doShipping($uri);
elseif( $cart_command == 'order' && $hasCart ) $scData = doOrder($uri);
elseif( $cart_command == 'payment' && $hasCart ) $scData = doPayment($uri);
else $scData = doProducts($uri);

// set seo data for use in the main head
if(isset( $scData))
{
	$seot = $scData['seo']['title'];
	$seod = $scData['seo']['desc'];
	$seok = $scData['seo']['keys'];
}
/*
echo "<div style='text-align:left;'>";
var_dump($scData);
if ( isset($scData['errors']) && count($scData['errors']) > 0) echo $scData['errors'];
echo "</div>";
*/

// set the search array data if found
$searchError = isset($scData['errors']) && count($scData['errors']) > 0 ? $scData['errors'] : false;
$searchData = isset($scData['search_data']) ? $scData['search_data'] : false; 
if (isset($scData['heading']))
{
	$heading = array(
		'name'	=> $scData['heading']['name'],
		'desc'	=> $scData['heading']['desc']
	);
}
if (isset($scData['item_data'])) $item_data = $scData['item_data'];

// links that must occur after the main head links (jquery included in main head)
// get them from the appropriate theme's head
include_once('head_links.php');
// creates $moduleLinks
?>


<?php  //---------------------------------------------functions-----------------------------------------------------//
function doProduct($uri)
{
	// if product url supplied, get item info and do item page,
	//	otherwise, default to products list
	$noSEO = false;
	if (get_uri(2))
	{
		// get seo_data
		$safeUrl = trim($uri[2]);
		$x = ecom_functions::getProductByUrl($safeUrl);

		$return['page'] = 'product.php';
		$return['item_data'] = $x;
		if (! $x) 
		{	
			$return['page'] = 'products.php';
			$noSEO = true;
			$return['errors'][] = 'product_not_found';
		}	
		
	}
	else
	{
		$noSEO = true;
		$return['page'] = 'products.php';
	}
	
	// get options data
	$y=logged_query("SELECT * FROM `ecom_preferences` WHERE `id` = '1'",0,array());
	$options=$y[0];
	
	$return['seo']['title'] 	= $noSEO ? $options['seo_title'] 		: $x['seo_title'];
	$return['seo']['desc']		= $noSEO ? $options['seo_description'] 	: $x['seo_description'];
	$return['seo']['keys']  	= $noSEO ? $options['seo_keywords'] 	: $x['seo_keywords'];
	$return['heading']['name'] 	= $noSEO ? $options['name'] 			: $x['title'];
	$return['heading']['desc'] 	= $noSEO ? $options['desc'] 			: $x['desc'];

	return $return;
}	
function doCart($uri)
{
	global $_config;
	$x = logged_query_assoc_array("SELECT * FROM `pages` WHERE `slug` = :slug AND `id`>0 AND `status`>0",null,0,array(":slug" => $_config['cartName']."/cart"));
	
	if(!$x)
	{
		$y=logged_query("SELECT * FROM `ecom_preferences` WHERE `id` = '1'",0,array());
		$x=$y[0];
		$x['content'] = '';
		$x['page_title'] = 'Shopping Cart';
	}
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['content'] = $x['content'];
	$return['title'] = $x['page_title'];
	$return['page'] = 'cart.php';
	
	return $return;
}

function doCheckout($uri)
{
	global $_config;
	$x = logged_query_assoc_array("SELECT * FROM `pages` WHERE `slug` = :slug AND `id`>0 AND `status`>0",null,0,array(":slug" => $_config['cartName']."/checkout"));
	
	if(!$x) 
	{
		$y=logged_query("SELECT * FROM `ecom_preferences` WHERE `id` = '1'",0,array());
		$x=$y[0];
		$x['content'] = '';
		$x['page_title'] = 'Checkout';
	}
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['content'] = $x['content'];
	$return['title'] = $x['page_title'];	
	$return['page'] = 'checkout.php';
	
	return $return;
}
function doShipping($uri)
{
	global $_config;

	//cart preferences
	$x=logged_query_assoc_array("SELECT `seo_title`,`seo_description`,`seo_keywords` FROM `ecom_preferences` WHERE `id` = '1'",null,0,array());
	$x = $x[0];
	
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['content'] = '';
	$return['title'] = 'Checkout';
	$return['page'] = 'shipping.php';
	
	return $return;
}
function doPayment($uri)
{
	global $_config;

	//cart preferences
	$x=logged_query_assoc_array("SELECT `seo_title`,`seo_description`,`seo_keywords` FROM `ecom_preferences` WHERE `id` = '1'",null,0,array());
	$x = $x[0];
	
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['content'] = '';
	$return['title'] = 'Payment';
	$return['page'] = 'payment.php';
	
	return $return;
}
function doOrder($uri)
{
	global $_config;
	
	//cart preferences
	$x=logged_query_assoc_array("SELECT `seo_title`,`seo_description`,`seo_keywords` FROM `ecom_preferences` WHERE `id` = '1'",null,0,array());
	$x = $x[0];
	
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['content'] = '';
	$return['title'] = 'Submit Order';
	$return['page'] = 'order.php';
	
	return $return;
}
function doSuccess($uri)
{//TO DO
	global $_config;
	$x = logged_query_assoc_array("SELECT * FROM `pages` WHERE `slug` = :slug AND `id`>0 AND `status`>0",null,0,array(":slug" => $_config['cartName']."/success"));
	
	if(!$x) 
	{
		$y=logged_query("SELECT * FROM `ecom_preferences` WHERE `id` = '1'",0,array());
		$x=$y[0];
		$x['content'] = 'Your order has been successfully sent.';
		$x['page_title'] = 'Success';
	} else {
		$x = $x[0];
	}
	$return['seo']['title'] = $x['seo_title'];
	$return['seo']['desc'] = $x['seo_description'];
	$return['seo']['keys'] = $x['seo_keywords'];
	$return['page'] = 'success.php';
	$return['content'] = $x['content'];
	$return['title'] = $x['page_title'];
	unset($_SESSION['cart']);
	unset($_SESSION['cart_qty']);
	unset($_SESSION['cart_price']);
	
	return $return;
}  

function doProducts($uri)
{
	// the seo values are determined by the first value passed in uri
	// if command is category/collection use that: otherwise, use the default
	$mainCommand = get_uri(1);
	$noSEO = false;
	$return['page'] = 'products.php';
	$return['errors'] = array();
	
	if($mainCommand == 'category')
	{
		// get seo_data
		$safeUrl = trim($uri[2]);
		$y=logged_query("SELECT * FROM `ecom_category_data` WHERE `url` = :safeUrl",0,array(":safeUrl" => $safeUrl));
		$x=$y[0];
		if(! $x)
		{
			$noSEO = true;
			$return['errors'][] = 'category_not_found';
		}
	} elseif ( $mainCommand == 'collection') {
		// get seo_data
		$safeUrl = trim($uri[2]);
		$y=logged_query("SELECT *, title AS name FROM `ecom_collection` WHERE `url` = :safeUrl",0,array(":safeUrl" => $safeUrl));
		if(!empty($y)) {
			$x=$y[0];
		} else {
			$x = false;
		}
		if(!$x)
		{
			$noSEO = true;
			$return['errors'][] = 'collection_not_found';
		}
	} else $noSEO = true;
	
	if ( uri::exists('featured')) {
		// get seo_data
		global $_config;
		$x = logged_query_assoc_array("SELECT * FROM `pages` WHERE `slug` = :slug AND `id`>0 AND `status`>0",null,0,array(":slug" => $_config['cartName']."/featured"));
		$x = $x[0];
		if($x) 
		{
			$x['name'] =$x['page_title'];
			$x['desc'] =$x['content'];
			$noSEO = false;
		}
	}
	
	// get options data
	$y=logged_query("SELECT * FROM `ecom_preferences` WHERE `id` = '1'",0,array());
	$options=$y[0];
	
	
	$return['seo']['title'] 	= $noSEO ? $options['seo_title'] 		: $x['seo_title'];
	$return['seo']['desc']		= $noSEO ? $options['seo_description'] 	: $x['seo_description'];
	$return['seo']['keys']  	= $noSEO ? $options['seo_keywords'] 	: $x['seo_keywords'];
	$return['heading']['name'] 	= $noSEO ? $options['name'] 			: $x['name'];
	$return['heading']['desc'] 	= $noSEO ? $options['desc'] 			: $x['desc'];
	
	// parse uri for product search data
	// valid parms
	$validURIkeys = array('category', 'collection', 'featured', 'page');
	// cycle everything in the uri, checking for valid data: if any bad data exists, error it out
	$validURI = true;
	$curURI = 2; // this is the uri position of the current command
	
	//  if there is no main command, display featured items
	if (! $mainCommand && ! isset($_POST['search']) && !isset($_REQUEST['filter']) )
	{
		$return['search_data']['featured'] = false;
		return $return;
	}
	
	// the first uri element must be a valid uri key
	if (! in_array($mainCommand, $validURIkeys)&& ! isset($_POST['search']) && !isset($_REQUEST['filter']))
	{
		$return['errors'][] = 'invalid_uri';
		return $return;
	}	
	
	// get search data from the uri recursively
	// $data will hold the search parameters 
	$data = array();
	$errMsg = false;
	if(in_array($mainCommand, $validURIkeys) ) $errMsg = doCommand($mainCommand, $curURI, $options, $validURIkeys, $data);
//var_dump($errMsg);
	
	// add filter
	if(isset($_REQUEST['filter']) && $_REQUEST['filter'] != "0") {
		$data['filter'] = $_REQUEST['filter'];
	}

	// add search calls
	if (isset($_POST['search']) )
	{
		$data['search'] = trim($_POST['search']);
	}
	if ($errMsg) $return['errors'] = $errMsg;
	else $return['search_data'] = $data;
	return $return;
}

// do command: takes the command passed and that commands position in the uri as parameters
// returns the updated uri position/ data for the search function/ fail if failed...duh
// these are returned as a single assoc array
function doCommand(&$command, &$curURI, $cartOptions, &$validURIkeys, &$data)
{	
	$allURIkeys = array('category', 'collection', 'featured', 'page');
	
	// page must be the last command
	if( $command == 'page'  )
	{
		// have we already filtered this out?
		if(! in_array($command, $validURIkeys) ) $data['error'][] = 'duplicate_uri_command';
		
		// is the next value a number?
		$dirtyNum = get_uri( $curURI++ );
		$pageNum = ( is_pos_int( $dirtyNum ) ) ?  $dirtyNum : false;
		if (! $pageNum) $data['error'][]  = 'invalid_page';
		
		// but if there are any data after this...oops
		if ( get_uri($curURI++) )  $data['error'][]  = 'bad_uri';

		// we are all good here:
		// construct the pagination portion of the search data
		$data['length'] = $_config['shopping_cart']['preferences']['prod_per_page'];
		$data['start'] = ($pageNum - 1) * $data['length'];
		
		// unset from validURIkeys to prevent duplicate uses
		$validURIkeys = array_merge( array_diff( $validURIkeys, array($command) ) );
		
		// we don't have any more commands to pursue!
		$command = false;
	} elseif ($command == 'featured') {

		// have we already filtered this out?
		if(! in_array($command, $validURIkeys) ) $data['error'][]  = 'duplicate_uri_command';

		// unset from validURIkeys to prevent duplicate uses
		$validURIkeys = array_merge( array_diff( $validURIkeys, array($command) ) );		

		// is this followed by a non-command word?
		$nextCommand = get_uri($curURI++);
		if ($nextCommand && ! in_array($nextCommand, $validURIkeys) ) $data['error'][]  = 'bad_uri';

		// set the search data variable
		if ( ! isset($data['error']) ) $data['featured'] = true;

		$command = $nextCommand; // false if there is no next command
		
	} elseif ($command == 'category') {
		
		// have we already filtered this out?
		if(! in_array($command, $validURIkeys) ) $data['error'][]  = 'duplicate_uri_command';

		// error if followed by a command word
		$parm = get_uri($curURI++);
		if(! $parm || in_array($parm, $allURIkeys) ) $data['error'][]  = 'missing_parm';
		
		// unset from validURIkeys to prevent duplicate uses
		$validURIkeys = array_merge( array_diff( $validURIkeys, array($command) ) );
		
		// error if this is followed by a non-command word
		$nextCommand = get_uri($curURI++);
		if ($nextCommand && ! in_array($nextCommand, $validURIkeys) ) $data['error'][]  = 'bad_uri';
		
		// set the search data
		if($parm && ! isset($data['error']) ) $data['category'] = trim($parm);
		
		$command = $nextCommand; // false if there is no next command
	} elseif ($command == 'collection') {
		// have we already filtered this out?
		if(! in_array($command, $validURIkeys) ) $data['error'][]  = 'duplicate_uri_command';

		// error if followed by a command word
		$parm = get_uri($curURI++);
		if(! $parm || in_array($parm, $allURIkeys) ) $data['error'][]  = 'missing_parm';
		
		// unset from validURIkeys to prevent duplicate uses
		$validURIkeys = array_merge( array_diff( $validURIkeys, array($command) ) );
		
		// error if this is followed by a non-command word
		$nextCommand = get_uri($curURI++);
		if ($nextCommand && ! in_array($nextCommand, $validURIkeys) ) $data['error'][]  = 'bad_uri';
		
		// set the search data
		if($parm && ! isset($data['error']) ) $data['collection'] = array(trim($parm));
		
		$command = $nextCommand; // false if there is no next command
	}
	

	// if the command is false; recursions have finished, we've reached the end.
	// the data variable now holds search parameters
	if (isset($data['error'])) return $data['error'];
	elseif (! $command ) return false;
	else doCommand($command, $curURI, $cartOptions, $validURIkeys, $data);
}

?>