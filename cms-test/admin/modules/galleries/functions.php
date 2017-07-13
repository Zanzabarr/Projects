<?php

function getGalleries()
{
	$galleries = logged_query("SELECT gallery.id, title as gallery_title, sort_type, gallery.status, gallery.date FROM gallery",0,array());
	return $galleries;
}

function buildGalleryMenu($galleries)
{
	$menu = "<div class ='gallery_grp' >";
	if($galleries && count($galleries))
	{
		foreach($galleries as $gallery)
		{
			$menu .= "<div class ='gallery_row' >"."\n";
			$menu .= 		buildGalleryRow($gallery)."\n";
			$menu .= "</div>"."\n";// end of gallery row
		}
	} 
	else
	{
		$menu .= "<p style='margin-left:1em;'>No Galleries Found. Why not create a <a class='blue button' style='float:none;display:inline-block;font-size:12px;' href='gallery_edit.php?option=create'>New Gallery</a></p>";
	}
	$menu .= "</div>";// end of gallery grp
	return $menu;
}

function buildGalleryRow($gallery)
{	
	global $_config;
	$statusWord =$gallery['status'] ? 'Published' : 'Draft';

	
	$menu ="<span class='row_title'>".$gallery['gallery_title']."</span>"."\n";
	$menu .="<span class='row_page' ><input type='text' class='embed_code'  readonly=readonly style='width:186px;margin-left: -15px; text-align: center;' value='{{gallery/{$gallery['id']}}}' /></span>"."\n";
	$menu .= "<span class='row_status'>".$statusWord."</span>"."\n";


	$menu .="<span class='op_group'>";
	
	$menu .="
		<a href='gallery_edit.php?gallery_id=".$gallery['id']."'>
			<img class='tipTop' title='Edit this gallery' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>";
// temporarily disabled delete button until multi gallery is built	
	$menu .= "<a  href='gallery.php?delete=".$gallery['id']."' class=\"delete_gallery\" rel=\"".$gallery['id']."\">
			<img class='tipTop' title='Completely remove this gallery and all associated images.' src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";

	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
}


function removeGallery($galleryId)
{
	global $_config;
	
	if ( !is_numeric($galleryId) ) return false;

	// get all images belonging to this gallery and delete them
	$remImages = logged_query("SELECT * FROM `gallery_image` WHERE `gallery_id`=:galleryId",0,array(":galleryId" => $galleryId) );
	
	$uploadPath = $_config['upload_path'] . 'gallery/';
	
	if(is_array($remImages) ) : foreach($remImages as $remImage){
		foreach($_config['multi_uploader']['gallery'] as $tmp_folder => $dummy)
			@unlink( $uploadPath . $tmp_folder . '/'	. $remImage['name'] );
		@unlink( $uploadPath . '/' . $remImage['name'] );
	} endif;
	
	logged_query("DELETE FROM `gallery_image` WHERE `gallery_id`=:galleryId",0,array(":galleryId" => $galleryId) );
	
	// delete the gallery
	logged_query("DELETE FROM `gallery` WHERE `id`=:galleryId",0,array(":galleryId" => $galleryId) );
}
?>