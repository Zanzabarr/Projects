<?
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = trim($_POST['id']);

        foreach (glob("{$_config['admin_path']}modules/news_items/images/news_itemimages/{$id}_*") as $filename) {

            unlink($filename);
        }

		$query = "UPDATE `news_items` SET news_item_image = NULL WHERE id = '{$id}'";
		
		$result = logged_query($query,0,array());
		
		echo $result === false ? "There was an error." : 'success';
	}
	else echo 'error';
	
	die();
}	
?>
