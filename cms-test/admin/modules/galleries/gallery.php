<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'galleries';
include('../../includes/headerClass.php');
include('functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

if (isset($_GET['delete'])) removeGallery( $_GET['delete'] );


// get all categories, indexed by id
$galleries = getGalleries();

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Galleries</h1></div>
    <div id="info_container" class='galleryTable'>

		<div id='gallerynavbtn'>
			<a class="blue button tipTop" title='Create a new gallery.' href="gallery_edit.php?option=create">New Gallery</a>
			<div class='clearFix'></div>
		</div>	

		<br />
		<?php


		if (count($galleries) >0 ) :
		?>
			
		<div id='galleryPosts'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="200">TITLE</th>
					<th width="230" class='tipTop' title="Copy and paste this code into your page on a line by itself where you want the gallery to be placed.">Link Code</th>
					<th >STATUS</th>
					<th>Operation</th>
				</tr>
			</table>

			<?php // echo display_array($pages);
			echo buildGalleryMenu($galleries) 
		?>
		</div> <!--end blogPosts -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no galleries yet. Why not click "New Gallery" to create your first one?</p>
		<?php endif; ?>

</div> <!--end infoContainer -->
<script type="text/javascript">
	/* allows one-click selection of embed code for gallery BEH */
	$('.embed_code').click(function() {
		$(this).focus();
		$(this).select();
	});
</script> 
<?php // echo display_array($pages);
include($_config['admin_includes']."footer.php"); ?>