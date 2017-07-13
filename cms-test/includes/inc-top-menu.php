<?php // THIS TOP MENU PROVIDES TOUCHSCREEN SUPPORT WITHOUT SPRY AND MENUS FOR GRANDCHILDREN
//
// $children: contains menu info
// $children['head']										info about the heading
// $children['head']['slug']									slug for url
// $children['head']['id']     								id of page
// $children['head']['title']  								page title
// $children['descendants']								info about the children pages
// $children['descendants']['sorted_order'] 					position of descendant in order
// $children['descendants']['sorted_order'] ['slug']			slug for url
// $children['descendants']['sorted_order'] ['id']  		   	id of page
// $children['descendants']['sorted_order'] ['title']  		page title
// $children['selected']									info about the selected page
// $children['selected']['id']									selected page's id
// $children['selected']['top_id']								id of the page's top parent
// $children['selected']['top_slug'] 							slug of the page's top parent

$mobileMenu = $isMobile ? ' isMobile' : ' notMobile';
?>
<div id="topNav"><!-- start mainmenu -->
<div class="menu-toggle"></div>
<!-- start mainmenu -->
<!--[if IE 7]>
	<ul id="topMenu" class="MenuBarHorizontal ie7 navbar">
<![endif]-->
<!--[if !IE 7]> <!-->
	<ul id="topMenu" class="MenuBarHorizontal <?php echo $mobileMenu;?>">
<!--<![endif]-->
<?php
if (isset($top_row)){
	// find the slug of the top parent of the page we are currently on
	$selectedParent = $pages->get_chain_by_slug($page_base, true);
	//get current page title for mobile menu use
	$currentTitle = $pages->get_page_title_by_id($selectedParent['selected']['id']);
	//set mobile menu to default title if on the home page
	$selectedTitle = ($currentTitle != "" && $currentTitle != "Home") ? $currentTitle : "Website Navigation";
	//change $selectedParent from array to parent slug
	$selectedParent = $selectedParent['head']['slug'];

	if(is_array($top_row)): foreach ($top_row as $row)
	{
		// these are the top level menu-items
		// if this is a dropdown menu: get the children
		$children = false;
		$children = $pages->get_chain_by_slug($row['slug'], true);
		$hasChildren = isset($children['descendants']) && count($children['descendants']) > 0 ;
		$href = $_config['site_path'] . $row['slug'];
		if($row['slug']=='home') $href = $_config['site_path']; // NEW - change home link to root link only
		$selected = ( $row['slug'] == $selectedParent || ($selectedParent == '' && preg_match('/websitesbythemonth/', $row['slug'])) ) ? ' selectedMenu' : '' ;
		$arrow = ($hasChildren && $isMobile) ? "<a class='arrowOpen'>&#9658</a>" : "";
		//** PRINT THE TOP LEVEL HEADING ** //
		echo "<li class='topRow{$selected}'><a  class='MenuBarItemSubmenu {$selected}' href='{$href}'>{$row['title']}</a>{$arrow}";

		// if there are any children, deal with them
		if ( $hasChildren )
		{
		?>
			<ul id="<?php echo $row['slug'];?>-child" class="MenuBarSubmenu" data-parent="<?php echo $row['slug'];?>"></ul>
		<?php
		}
		echo "</li>";
	} endif;
}
?>
	</ul> <!-- end top menu -->
	<?php include($_config['admin_modules'] . 'members/frontend/login/member_panel/member_login.php'); ?>
</div>
<script>
	/* set mobile menu button text to page title */
	$('.menu-toggle').html('<?php echo $selectedTitle;?>');
</script>

 <!-- END mainmenu -->
<div style="clear:both;"></div>
