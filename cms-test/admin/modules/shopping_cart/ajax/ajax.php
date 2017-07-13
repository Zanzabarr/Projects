<?php
include("../../../includes/config.php");

ajax_check_login();

if (array_key_exists('action', $_POST) && $_POST['action'] == "delete_collection") {
	if (isset($_POST['id']) && is_pos_int($_POST['id']) )
	{
		$id = $_POST['id'];
		$colurl = $_POST['url'];

		// 'delete' the collection data
        $result = logged_query("DELETE FROM `ecom_collection` WHERE id = {$id}",0,array());

		if ($result===false) 
		{
			echo "Error removing Collection.";
			die();
		}
		
		// now delete all links to it
		$result = logged_query("DELETE FROM `ecom_prod_in_col` WHERE `collection_id` = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Error unlinking Products from Collection.';
			die();
		}
        // now deal with pages
        // get related page and make draft, change slug to '--slug'
        $relatedPage = logged_query("select id, slug from pages where id > 0 && status = 1 && slug = :slug LIMIT 1",0,array(":slug" => "shopping/collection/{$colurl}"));
        
        if(!empty($relatedPage)) {
            if(substr($relatedPage[0]['slug'],0,1) != "--") {
                $updatePage = logged_query("UPDATE pages SET status = 0, slug = '--{$relatedPage[0]['slug']}' WHERE id = {$relatedPage[0]['id']}",0,array());
            }
        }
        
		die('success');
	}
	else echo 'Could not find Collection to delete.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "delete_product") 
{
	if (isset($_POST['id']) && is_pos_int($_POST['id']) )
	{
		$id = $_POST['id'];
		
		// 'delete' the product data
		
		$result = logged_query("UPDATE `ecom_product` SET `id`=-{$id} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo "Error removing Product.";
			die();
		}
		
		// now delete all links to it
		$result = logged_query("DELETE FROM `ecom_prod_in_col` WHERE `product_id` = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Error unlinking Products from Collection.';
			die();
		}
		
		die('success');
	}
	else echo 'Could not find Product to delete.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "change_featured") 
{
	
	if (isset($_POST['id']) && is_pos_int($_POST['id']) && isset($_POST['change_to']) && ($_POST['change_to'] == '0' || $_POST['change_to'] == '1' ) )
	{
		$id = $_POST['id'];
		$change_to = $_POST['change_to'];
		
		// 'update' the featured value
		$result = logged_query("UPDATE `ecom_product` SET `featured`={$change_to} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Could not find Product to Change.';
			die();
		}
		echo 'success';
	}
	else echo 'Could not find Product to Change.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "change_active") 
{
	//include("../includes/functions.php");
	if (isset($_POST['id']) && is_pos_int($_POST['id']) && isset($_POST['change_to']) && ( $_POST['change_to'] == '0' || $_POST['change_to'] == '1' ) )
	{
		$id = $_POST['id'];
		$change_to = $_POST['change_to'];
		
		// first make sure this is valid
		$productData = ecom_functions::get_product_overview('', $id);
	
		if ($productData===false || !is_array($productData) || ( $change_to && ! ecom_functions::canActivateProduct($productData) ))
		{
			echo 'Could not find Product to Change.';
			die();
		}
		
		// 'update' the featured value
		$result = logged_query("UPDATE `ecom_product` SET `status`={$change_to} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Could not find Product to Change.';
			die();
		}
		echo 'success';
	}
	else echo 'Could not find Product to Change.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "change_confirm") 
{
	if (isset($_POST['id']) && is_pos_int($_POST['id']) && isset($_POST['change_to']) && ($_POST['change_to'] == '0' || $_POST['change_to'] == '1' ) )
	{
		$id = $_POST['id'];
		$change_to = $_POST['change_to'];
		
		// 'update' the featured value
		$result = logged_query("UPDATE `ecom_orders` SET `confirm`={$change_to} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Could not find Order to Confirm.';
			die();
		}
		echo 'success';
	}
	else echo 'Could not find Order to Confirm.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "change_shipped") 
{
	if (isset($_POST['id']) && is_pos_int($_POST['id']) && isset($_POST['change_to']) && ($_POST['change_to'] == '0' || $_POST['change_to'] == '1' ) )
	{
		$id = $_POST['id'];
		$change_to = $_POST['change_to'];
		
		// 'update' the featured value
		$result = logged_query("UPDATE `ecom_orders` SET `shipped`={$change_to} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Could not find Order to Ship.';
			die();
		}
		echo 'success';
	}
	else echo 'Could not find Order to Ship.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "archive_order") 
{
	if (isset($_POST['id']) && is_pos_int($_POST['id']) )
	{
		$id = $_POST['id'];
		
		// 'archive' the product data
		$result = logged_query("UPDATE `ecom_orders` SET `id`=-{$id} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo "Error Archiving Order.";
			die();
		}
		die('success');
	}
	else echo 'Could not find Order to archive.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "delete_order") 
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		
		// completely remove the product data
		$result = logged_query("DELETE FROM `ecom_orders` WHERE `id`={$id}",0,array());
		if ($result===false) 
		{
			echo "Error Removing Order.";
			die();
		}
		die('success');
	}
	else echo 'Could not find Order to delete.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "delete_coupon") 
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		
		// completely remove the product data
		$result = logged_query("DELETE FROM `ecom_coupons` WHERE `id`={$id}",0,array());
		if ($result===false) 
		{
			echo "Error Removing Coupon.";
			die();
		}
		die('success');
	}
	else echo 'Could not find Coupon to delete.';
	die();
}

elseif (array_key_exists('action', $_POST) && $_POST['action'] == "delete_tax") 
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		
		// completely remove the product data
		$result = logged_query("DELETE FROM `ecom_taxes` WHERE `id`={$id}",0,array());
		if ($result===false) 
		{
			echo "Error Removing Tax Location.";
			die();
		}
		die('success');
	}
	else echo 'Could not find Tax Location to delete.';
	die();
}