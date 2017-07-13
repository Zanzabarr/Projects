<?php 
include("includes/header.php"); 
include("includes/sidebar.php"); 

$pagequery=mysql_query("SELECT * FROM revisions WHERE id =".$_GET['page_id']."");
$page=mysql_fetch_assoc($pagequery);


if($_GET['option'] == "edit") {
	$update = "UPDATE pages SET slug='".$page['slug']."', page_title='".$page['page_title']."',content='".$page['content']."',  seo_keywords='".$page['seo_keywords']."', seo_description='".$page['seo_description']."', date='".date("M,d Y h:i:s A")."' WHERE id='".$page['page_id']."'";
	mysql_query($update) or die(mysql_error());
	
	echo "<script>window.location='edit_page.php?page_id={$page['page_id']}';</script>";
	
}

if($_GET['action']=='del')
{
	$query = "DELETE FROM revisions WHERE id = ".$_GET['page_id'];
	mysql_query($query);
	echo "<script>window.location='pages.php';</script>";
	
}
    

?>
<div class="page_container">
	<div id="h1"><h1>REVISION - <?php echo $page['page_title']." - "; echo $page['date']; ?></h1></div>
    <div class="revise_container" style="margin-bottom: 15px">
        <h2>SEO Information</h2>
                    
        <p>
        <b>Title:</b><br />
        <?php echo htmlspecialchars_decode($page['page_title']); ?>
        </p>
        
        <p>
        <b>Description:</b><br />
        <?php echo htmlspecialchars_decode($page['seo_description']); ?>
        </p>
        
        <p>
        <b>Keywords:</b><br />
        <?php echo htmlspecialchars_decode($page['seo_keywords']); ?>
        </p>
    </div>
    <div class="revise_container"  style="margin-bottom: 15px">
        <strong>Page Content:</strong><br />
        <?php echo htmlspecialchars_decode($page['content']); ?>
    </div>
    <div class="revise_container">
        <form action="revise_page.php?option=edit&page_id=<?php echo $_GET['page_id']; ?>" method="post" enctype="application/x-www-form-urlencoded" name="edititem">
		<input name="Submit" type="submit" id="submit" value="Restore Page" /><a id="delete_rev" href="revise_page.php?page_id=<?php echo $page['id']; ?>&action=del">DELETE REVISION</a>
        </form>
    </div>
</div>
<?php include("includes/footer.php"); ?>