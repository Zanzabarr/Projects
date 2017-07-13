<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'members';
include('../../includes/headerClass.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

$active = array('switch'=>'folder','dir'=>'ASC');
if(isset($_POST['changed']))
{
	$last_active = isset($_SESSION['folder_sort']) ? $_SESSION['folder_sort'] : array('switch'=>'folder','dir'=>'ASC');
	
	// if this is already active, arrow direction changes
	if($_POST['changed'] == $last_active['switch'])
	{
		$tmpDir = isset($_POST[$_POST['changed']]) ? 'ASC' :'DESC';
		$active = array(
			'switch' => $_POST['changed'],
			'dir' => $tmpDir
		);
	}
	else // this is now the highlighted element and the arrow is down by default
	{
		$active = array('switch'=> $_POST['changed'], 'dir'=>'ASC');
	}
	$_SESSION['folder_sort'] = $active;
}

// get users data
$folders= get_valid_folders(false, $active['switch'], $active['dir'] );

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/folder_style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/ftp.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>FILE SHARING</h1></div>
    <div id="info_container" class='ftpTable'>

		<?php 
		$selectedOpts = '';
		$selectedUsers = '';
		$selectedFolders = 'tabSel';
        
        echo '<div id="ftpnavhome">'; 
        
		include("includes/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		<?php 
		createBanner(array()); 
		?>
			
		<div id='users-list'>


			<?php 
			
	$ascend = 'checked';
	?>
			<div class="folderWrap">
				<form action="folders.php" id="sort-form" method="post" >
					<div class="titlebar">
						<span style="width:20em;display:inline-block;">folder
							<div id="nameSwitch" class="upDown <?php echo $active['switch'] == 'folder' ? '' : 'inactive'; ?>">
								<input type="checkbox" name="folder" <?php echo $active['switch'] == 'folder' && $active['dir'] =='DESC' ? '' : 'checked'; ?> ><label></label>
							</div>
						</span>
						<span style="width:13em;display:inline-block;">Last Updated
							<div id="nameSwitch" class="upDown <?php echo $active['switch'] == 'date_updated' ? '' : 'inactive'; ?>">
								<input type="checkbox" name="date_updated" <?php echo $active['switch'] == 'date_updated' && $active['dir'] =='DESC' ? '' : 'checked'; ?> ><label></label>
							</div>
						</span>
						<span style="width:5em;display:inline-block;">Status
							<div id="nameSwitch" class="upDown <?php echo $active['switch'] == 'status' ? '' : 'inactive'; ?>">
								<input type="checkbox" name="status" <?php echo $active['switch'] == 'status' && $active['dir'] =='DESC' ? '' : 'checked'; ?> ><label></label>
							</div>
						</span>
						<span style="float:right;margin-right:1em;">operation</span>
					</div>
				</form>	
				<div class ='folder_grp' >
					<?php
						if (isset($_GET['new_folder']))
						{
							echo	"<div class ='new_folder_row'>"."\n";
							echo		buildFolderRow(0, '')."\n";
							echo	"</div>"."\n";
						}
						
						foreach($folders as $id => $folder)
						{

							echo	"<div class ='folder_row'>"."\n";
							echo 		buildFolderRow($id, $folder)."\n";
							echo	"</div>"."\n";
						}		
					?>
				</div>
			</div>
		</div>
		<?php         
		$hide_no_folder_msg = count($folders) > 0 || isset($_GET['new_folder']) ? "display:none;" : '';
		echo '<p id="no_folders" style="text-align:center;width:100%;'.$hide_no_folder_msg.'">There are no folders yet. Why not click "Create Folder" to add your first one?</p>';
		?>	
</div> <!--end infoContainer -->
 
<?php 
include($_config['admin_includes']."footer.php"); 
?>
