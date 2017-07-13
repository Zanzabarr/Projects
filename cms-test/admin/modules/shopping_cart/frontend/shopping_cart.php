<?php //session_start();


/* variables passed along from index.php (this is an include from index)
**	$_config	: contains all config data from admin/includes/config.php
**	$uri		: array of uri components: $uri[0] is the base (blog in this case)
**	$module = 'shopping_cart';
*/

/* variables passed along from modules/shopping_cart/frontend/head.php (via index.php)
**	$scData['seo']['metatitle']   	// seo data (not used here)
**	$scData['seo']['metadesc']
**	$scData['seo']['metakeys']
**	$scData['page']					// name of the include page
**	// conditional variables: only set if required by the related page
**	$_GET['url']					// url used in item.php
** 	$_GET['cate']					// category name, used in products.php
}
*/

//----------------------------begin html----------------------------------------------

// this section has been entered from /index.php and this is the beginning of:
// <div id="content-area">

// make the module path available to js
?>
<input type="hidden" id="module_path" value="<?php echo $_config['admin_url'] . 'modules/shopping_cart/'; ?>" />

<?php  
	// go to the appropriate cart page
	if(isset($scData))
	{
		include("{$scData['page']}");
	}
?>