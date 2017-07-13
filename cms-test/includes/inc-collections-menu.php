<?php /* THIS FILE IS A SECOND MENU FOR CATEGORIES IN THE PRODUCTS MODULE SIDEBAR */ ?>
<!--[if IE 7]> 
	<ul id="sideMenu2" class="MenuBarVertical ie7 navbar sidenav<?php if($isMobile)echo ' isMobile' ;?>">
<![endif]-->
<!--[if !IE 7]> <!-->
	<ul id="sideMenu2" class="MenuBarVertical MenuBarActive <?php echo $mobileMenu;?>">
<!--<![endif]--> 
<?php // get products (collections) for given category product belongs to
		echo"<li class='menu-top'><a class='side-menu-title' href='{$chain['head']['slug']}'><h2>Collections</h2></a></li>";
		
	$currentSlug = strtolower(implode('/',$uri));
	$categories = logged_query_assoc_array("SELECT * FROM ecom_collection WHERE status > 0 && id > 0 ORDER BY title ASC",null,0,array());
	foreach($categories as $cat) {
		$selectedParent = ($currentSlug == $cat['url']) ? 'selectedParent' : '';
		$openMenu = ($selectedParent != "" || $currentSlug == $cat['url']) ? 'open' : '';
		echo"<li class='menu-bottom'><a class='$selectedParent' href='" . $_config['site_path']."shopping/collection/".$cat['url'] . "'>{$cat['title']}</a></li>";
	}
?>
</ul> <!-- END sideMenu2 -->	