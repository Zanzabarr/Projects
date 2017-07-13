<?php 

function getGalleries()
{
	$galleries = logged_query_assoc_array("
		SELECT gallery.id, page_id, slug as url, page_title, title as gallery_title, sort_type, posn, gallery.status, gallery.date
		FROM gallery
		left JOIN pages
		ON gallery.page_id = pages.id
	",0,array());
	return $galleries;
}
/*
// build page dropdown
function buildPagesDropdown($selected, $message) 
{
	// Menu Position  -- create version
	$input_menu_posn = new inputField( 'Menu Position', 'page_id', 'page_id' );
	$input_menu_posn->toolTip("Attach the Image Gallery to the desired page." );
	$input_menu_posn->type('select');
	$input_menu_posn->selected($selected);
	$input_menu_posn->arErr($message);
	
	// build the option set
		// get all menu items, order them parentally
	// get the menus data
	$pagequery = logged_query("SELECT id, parent_id, has_menu, menu_order, page_title FROM pages ORDER BY menu_order");
	if ( ! $pagequery ) {
		die ('Invalid query: ' . mysql_error());
	}
	$menus = array();
	while ($menu = mysql_fetch_assoc($pagequery) )
	{
		$menus[] = $menu;
	}
	
	// take those data and format as tree array up to three levels deep
	// or load the noMenu array if menu order is 0 (not linked to a menu) 
	$arTop = array();
	$arSide = array();
	$arNoMenu = array();
	//first, construct the top menu structure
	foreach($menus as $menu)
	{
		if ($menu['parent_id'] == 0 && $menu['has_menu'] == 1)
		{	
			$curId = $menu['id'];
			$arTop[$curId] = $menu;
			foreach($menus as $child)
			{
				if ( $arTop[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$arTop[$curId]['child'][$curChildId] = $child;
					foreach($menus as $grChild)
					{
						if ($arTop[$curId]['child'][$curChildId]['id'] == $grChild['parent_id'] && $grChild['has_menu'] != 0)
						{
							$curGrChildId = $grChild['id'];
							$arTop[$curId]['child'][$curChildId]['grChild'][$curGrChildId] = $grChild;
						}
					}
				}
			}
		}
		elseif ($menu['parent_id'] == 0 && $menu['has_menu'] == 2)
		{	
			$curId = $menu['id'];
			$arSide[$curId] = $menu;
			foreach($menus as $child)
			{
				if ( $arSide[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$arSide[$curId]['child'][$curChildId] = $child;
					foreach($menus as $grChild)
					{
						if ($arSide[$curId]['child'][$curChildId]['id'] == $grChild['parent_id'] && $grChild['has_menu'] != 0)
						{
							$curGrChildId = $grChild['id'];
							$arSide[$curId]['child'][$curChildId]['grChild'][$curGrChildId] = $grChild;
						}
					}
				}
			}
		}
		elseif ( $menu['has_menu'] == 0 )
		{
			$arNoMenu[] = $menu;
		}
	}
	//create  options: value represents the parent's id with parent 0 indicating top level
//	$input_menu_posn->option(  0, 'New Top Menu Page', array('class' => 'tipRight', 'title' => 'Make this page one of the<br \> main menu items.', 'style' => 'text-decoration: underline;' ) );
	
	foreach ($arTop as $parent)
	{
		$input_menu_posn->option(  $parent['id'], $parent['page_title'], array('class' => 'tipRight', 'title' => "Add gallery to this Top Menu page. "));
		if ( isset($parent['child']) ){
			foreach ( $parent['child'] as $child ) 
			{
				$input_menu_posn->option(  $child['id'], '&nbsp;&nbsp;&nbsp;'.$child['page_title'], array('class' => 'tipRight', 'title' =>  "Add gallery to this Top Menu page. "));
				if (isset($child['grChild'])  )
				{
					foreach ( $child['grChild'] as $grChild ) 
					{
						$input_menu_posn->option(  $grChild['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$grChild['page_title'], array('class' => 'tipRight', 'title' =>  "Add gallery to this Top Menu page." ));
					}	
				}
			}
		}
	}

	foreach ($arSide as $parent)
	{
		$input_menu_posn->option(  $parent['id'], $parent['page_title'], array('class' => 'tipRight', 'title' => "Add gallery to this Side Menu page."));
		if ( isset($parent['child']) ){
			foreach ( $parent['child'] as $child ) 
			{
				$input_menu_posn->option(  $child['id'], '&nbsp;&nbsp;&nbsp;'.$child['page_title'], array('class' => 'tipRight', 'title' => "Add gallery to this Side Menu page."));
				if ( isset($child['grChild']) )
				{
					foreach ( $child['grChild'] as $grChild ) 
					{
						$input_menu_posn->option(  $grChild['id'], '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$grChild['page_title'], array('class' => 'tipRight', 'title' => "Add gallery to this Side Menu page."));
					}	
				}
			}
		}
	}
	foreach ($arNoMenu as $menu)
	{
		$input_menu_posn->option(  $menu['id'], $menu['page_title'], array('class' => 'tipRight', 'title' => "Add gallery to this Menuless page."));
	}
	
	return $input_menu_posn;
} 
*/
function buildGalleryMenu($galleries)
{
	$menu = "<div class ='gallery_grp' >";
	foreach($galleries as $gallery)
	{
		$menu .= "<div class ='gallery_row' >"."\n";
		$menu .= 		buildGalleryRow($gallery)."\n";
		$menu .= "</div>"."\n";// end of gallery row
	}	
	$menu .= "</div>";// end of gallery grp
	return $menu;
}

function buildGalleryRow($gallery)
{	
	global $_config;
	$statusWord =$gallery['status'] ? 'Published' : 'Draft';
	$pageTitle = $gallery['page_title'] ? $gallery['page_title'] : '&nbsp';
	
	$menu ="<span class='row_title'>".$gallery['gallery_title']."</span>"."\n";
	$menu .="<span class='row_page' >".$pageTitle."</span>"."\n";
	$menu .= "<span class='row_status'>".$statusWord."</span>"."\n";
	
	// setup different values if a page is associated
	$disabled = '';
	$viewImage = 'view_icon.png';
	$tipMsg = 'See it as it will appear live.';
	if ( is_null($gallery['page_id']) )
	{
		$disabled = 'disabled="disabled"';
		$viewImage = 'view_icon_shade.png';
		$tipMsg = 'Gallery must be associated with a page to be previewed.';
	}
	
	$menu .="<span class='op_group'>
		<a target='_blank' {$disabled} href='{$_config['site_path']}".$gallery['url']."'>
		<img class='tipTop' title='{$tipMsg}' src=\"../../images/{$viewImage}\" alt=\"Edit\">
		</a>
		<a href='gallery_edit.php?gallery_id=".$gallery['id']."'>
			<img class='tipTop' title='Edit this gallery' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>";
// temporarily disabled delete button until multi gallery is built	
//	$menu .= "<a  href='gallery.php?delete=".$gallery['id']."' class=\"delete_gallery\" rel=\"".$gallery['id']."\">
//			<img class='tipTop' title='Completely remove this gallery and all associated images.' src=\"../../images/delete.png\" alt=\"Delete\">
//		</a>
//	"."\n";

	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
}

// build image divs from imgData( db data passed in)
function buildImgHtml($imgData)
{
	global $_config;
	
	foreach($imgData as $id => $data)
	{
	$desc = htmlspecialchars($data['desc']);
	$name = htmlspecialchars($data['name']);
	$alt = htmlspecialchars($data['alt']);
	$posn = htmlspecialchars($data['posn']);
	$url = $_config['upload_url'];
?>
	<li>
		<div id='imageData<?php echo $id; ?>' class='imageData'>
			<input type='hidden' class='imgDesc' value='<?php echo $desc; ?>' />
			<input type='hidden' class='imgAlt' value='<?php echo $name; ?>' />
			<input type='hidden' class='imgPosn' value='<?php echo $posn; ?>' />
			<div class='image_wrap'>
				<span class='option_navs' >
					<a class='showImg' href='<?php echo $url; ?>galleries/fullsize/<?php echo $name; ?>' ><img alt='View Page' src='../../images/view_icon.png'></a>
					<a class='edit_image' href='#' rel='<?php echo $id; ?>'><img src='../../images/edit.png' /></a>
					<a class='gallery-del' href='#' rel='<?php echo $id; ?>'><img src='../../images/delete.png' /></a>
				</span>
				<img src='<?php echo $url; ?>galleries/thumb/<?php echo $name; ?>' alt='<?php echo $alt; ?>'/>
			</div>
		</div>	
	</li>
<?php
	}
}
