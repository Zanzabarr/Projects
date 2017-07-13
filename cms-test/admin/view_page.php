<?php
include('includes/headerClass.php');
$pageInit = new headerClass();
$pageInit->createPageTop();

if (isset($_GET['option']) && $_GET['option'] == 'revision' )
{
	$pageViewQuery = logged_query("SELECT * 
FROM page_revisions 
WHERE `id` = :pageId",0,array(":pageId" => $_GET['page']));
}
else $pageViewQuery = logged_query("SELECT * 
FROM pages 
WHERE slug = :page 
  AND id > 0
ORDER BY `id` DESC
LIMIT 1",0,array(":page" => $_GET['page']) );

if (isset($pageViewQuery[0]['page_title']) ) $ptitle = $pageViewQuery[0]['page_title'] . " - ";
else $ptitle = '';
?>
<div class="page_container">
	<div id="h1"><h1><?php echo $ptitle; ?>PAGE VIEW CONTENT SUMMARY</h1></div>
    
    <div id="info_container">
        <div class="page_view">
		
	<?php
	
	if (count($pageViewQuery)) :
		$pageQuery = $pageViewQuery[0];
	?>
			<p>Title: <?php echo $pageQuery['page_title']; ?></p>
			<p>Content: <?php echo htmlspecialchars_decode($pageQuery['content']) ?></p>
			<p>Current Status: <b><i>
			<?php 
			if (isset($_GET['option']) && $_GET['option'] == 'revision') echo 'deleted'; 
			else
			{
				if ($pageQuery['status'] == "1") echo "live"; 
				else echo "draft"; ; 
			}	
			?>
			</i></b></p>
		
	<?php else : ?>
			<p>Page Not Available For Viewing</p>
	<?php endif; ?>
		</div>
	</div>


</div>
<?php include("includes/footer.php"); ?>