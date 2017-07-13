<?php
// asynchronous functions
// save function
if (array_key_exists('items', $_POST) ) {
	include_once("includes/config.php");
	// receives data as a comma separated list of numbers

	$rawPost = $_POST['items'];
	// foreach them
	foreach ( $rawPost as $rawData)
	{
		$arrRaw = explode(',',$rawData);

		// page title could have commas in it. take the rest of the items and sew them back together
		$page_title = $arrRaw[4];
		for ($i = 5; $i < count($arrRaw); $i++)
		{
			$page_title .= ',' . $arrRaw[$i];
		}
		
		$menuOrder = (int) $arrRaw[0];
		$id = substr($arrRaw[1], 2);
		$parent_id = substr($arrRaw[2], 2);
		$has_menu = (int) $arrRaw[3];
		
		if ( strlen($id) < 1 || strlen($parent_id) < 1 ) die ('Bad Data');
		$update = "UPDATE pages 
	SET parent_id=:parent_id,
		has_menu=:has_menu,
		page_title=:page_title,
		menu_order=:menuOrder
	WHERE id=:id";
		logged_query($update,0,array(
			":parent_id" => $parent_id,
			":has_menu" => $has_menu,
			":page_title" => $page_title,
			":menuOrder" => $menuOrder,
			":id" => $id
		));		
	}

	echo 'success';
	die();
}

// synchronous page actions
include('includes/headerClass.php');
$pageInit = new headerClass();
$pageResources ="
<script type=\"text/javascript\" src=\"".$baseUrl."js/new_page.js\"></script>
";
$pageInit->createPageTop($pageResources);

if (isset($_GET['remove'])) {
	if( is_numeric($_GET['remove']) )
	{
		$id = $_GET['remove'];
		// 	don't delete the page, instead set the id to a negative of itself so it can be restored later.
		// 		zero the has_menu and order data so it appears in menuless when restored 
		//		( don't interfere with order that may have changed since deletion )
		$update = "UPDATE pages 
	SET id = :neg_id,
		parent_id='0',
		has_menu='0',
		menu_order='0'
		WHERE id=:id";
		logged_query($update,0,array(":neg_id" => -$id, ":id" => $id));
				
	}
	//mysql_query("DELETE FROM pages WHERE id = '".$_GET['remove']."'") or die('Error Deleting Page:<br/>'.mysql_error());	
	// also need to delete all revisions;
	//mysql_query("DELETE FROM page_revisions WHERE page_id = '".$_GET['remove']."'") or die('Error Deleting Revisions:<br/>'.mysql_error());	
	echo "<script type=\"text/javascript\">window.location = \"pages.php\"</script>";
}

// build line items from $row array	
function build_li($row, $level = 0)
{
	global $_config;
	$spaces = '';
	if ($level == 0)
	{
		$type = 'parent';
	}
	else if ($level == 1)
	{ 
		$type = 'child';
	}
	else $type = 'grandchild';
	
	$p_status = $row['status'] == 0 ? 'Draft' : 'Published'; 
	
	$lrow ="<li class='".$type."' id='id".$row['id']."'>";
	$lrow .="<span class='drag-title'><span>".$row['page_title']."</span></span>";
    $lrow .="<span style='text-align:center'>".$p_status."</span>";
    $lrow .="<span class='page_navs' style='text-align:center'><a href='{$_config['site_path']}{$row['slug']}'><img alt='View Page' src='images/view_icon.png'></a> <a href='edit_page.php?page_id=".$row['id']."'><img src='images/edit.png' /></a> <a class='del_page' href='pages.php?remove=".$row['id']."'><img src='images/delete.png' /></a></span>";
    $lrow .= '<div class="exp-col show"></div>';
	$lrow .='</li>';
	return $lrow;
	}

// build the menu from query	
function buildMenu(){			 
	// get the pages data
	$pages = logged_query_assoc_array("SELECT pages.* FROM pages WHERE id > 0 ORDER BY menu_order",null,0,array());

	
	// take those data and format as topMenu array up to three levels deep
	// or load the noMenu array if page order is 0 (not linked to a menu) 
	$topMenu = array();
	$sideMenu = array();
	$noMenu = array();
	foreach($pages as $page)
	{
		if ($page['parent_id'] == 0 && $page['has_menu'] == 1)
		{	
			$curId = $page['id'];
			$topMenu[$curId] = $page;
			foreach($pages as $child)
			{
				if ( $topMenu[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$topMenu[$curId]['child'][$curChildId] = $child;
					foreach ($pages as $grandChild)
					{
						if ( $topMenu[$curId]['child'][$curChildId]['id'] == $grandChild['parent_id'] && $grandChild['has_menu'] != 0)
						{
							$topMenu[$curId]['child'][$curChildId]['grandChild'][$grandChild['id']] = $grandChild;
						}
					}
				}
			}
		}
		elseif ($page['parent_id'] == 0 && $page['has_menu'] == 2)
		{	
			$curId = $page['id'];
			$sideMenu[$curId] = $page;
			foreach($pages as $child)
			{
				if ( $sideMenu[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$sideMenu[$curId]['child'][$curChildId] = $child;
					foreach ($pages as $grandChild)
					{
						if ( $sideMenu[$curId]['child'][$curChildId]['id'] == $grandChild['parent_id'] && $grandChild['has_menu'] != 0)
						{
							$sideMenu[$curId]['child'][$curChildId]['grandChild'][$grandChild['id']] = $grandChild;
						}
					}
				}
			}
		}
		elseif ($page['has_menu'] <= 0 )
		{
			$noMenu[] = $page;	
		}
	}		 
					
	//Pull All Page Info From Database
	// build top menu
	$uList = "<li id='new-menu'><h2>Top Menu Pages</h2></li>";
	foreach ($topMenu as $top)
	{
		$uList .= build_li($top);
		if(isset($top['child']))
		{
			foreach ($top['child'] as $child)
			{
				$uList .= build_li($child, 1);
				if (isset ($child['grandChild']) ){
					foreach ($child['grandChild'] as $grandChild)
					{
						$uList .= build_li($grandChild, 2);
					}
				}
			}
		}
	}
	// build side menu	
	$uList .= "<li id='side-menu'><h2>Side Menu Pages</h2></li>\n";	
	foreach ($sideMenu as $top)
	{
		$uList .= build_li($top);
		if(isset($top['child'])) :foreach ($top['child'] as $child)
		{
			$uList .= build_li($child, 1);
			
			if ( isset($child['grandChild'] ) ) :
			foreach ($child['grandChild'] as $grandChild)
			{
				$uList .= build_li($grandChild, 2);
			}
			endif;
		} endif;
	}
	$uList .= '</ul>';
	$uList .= '<hr />';

	// build menuless section
	$uList .= '<ul class="menuless">';
	$uList .= '<li id="new-menuless"><h2>Pages Without Menus</h2></li>';
	if (count($noMenu) > 0)
	{
		foreach ($noMenu as $item)
		{
			$uList .= build_li($item);
		}
	}
	return $uList;
}
?>
	
<div class="page_container">
	<div id="h1"><h1>Pages</h1></div>
    <div id="addPage_container" class="not-dragging">
		
		<?php //---------------------------------------Error Banner----------------------------- 
		// create a banner and, if $message['banner'] exists: show banner info
		// banner can also be called dynamically with jquery function : openBanner(type, heading, message); type:'success'/'error'/'warning'
		if (! isset($message)) $message = array();
		createBanner($message); 
		?>
<!--		
		<div class="msg-wrap">
			<div class="close-message"></div>
			<div class="success">
				<h2>Successfully Saved</h2>
			</div>

		</div>
 -->       <div class="pages" id="table">
			<div id="drag_container">
				<table class="silly" cellspacing="0" cellpadding="0">
				<tr>
					<th width="330" style="text-align:left;padding-left:55px;">TITLE</th>
					<th width="100">STATUS</th>
					<th width="100">OPERATION</th>
				</tr>
				</table>
				
				<ul class='menu-item' id='top'>
				<?php //buildMenu builds two ULs: closes menu-item then opening menuless
					echo buildMenu($message);  
				?>
				</ul> <!-- end of menuless, -->
				<hr />
			</div>			
			<div class='clearFix' ></div>
			<a id="add_page" class="green button" href="edit_page.php?option=new_page">New Page</a>
			<a id="submit-btn" class="blue button" href="#">Save</a>
			<a class="grey button" id="cancel-btn" href="javascript:history.go(0)">Cancel</a>
			<div class='clearFix' ></div>
        </div> <?php //end pages ?>
    </div>
</div>
<?php include("includes/footer.php"); ?>