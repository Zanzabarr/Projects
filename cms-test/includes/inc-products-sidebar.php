<?php // THIS FILE CREATES SIDEBAR MENU FOR COLLECTIONS AND INCLUDES CATEGORIES MENU
//dynamic creation of a sidebar

// $chain passed from index.php: contains sidebar info
// $chain['head']										info about the heading
// $chain['head']['slug']									slug for url
// $chain['head']['id']     								id of page
// $chain['head']['title']  								page title
// $chain['descendants']								info about the children pages
// $chain['descendants']['sorted_order'] 					position of descendant in order
// $chain['descendants']['sorted_order'] ['slug']			slug for url
// $chain['descendants']['sorted_order'] ['id']  		   	id of page
// $chain['descendants']['sorted_order'] ['title']  		page title
// $chain['selected']									info about the selected page
// $chain['selected']['id']									selected page's id

$mobileMenu = $isMobile ? ' isMobile' : ' notMobile';
?>
<div id="sidebar" style="margin-top:1em;">

       
<?php if($hasSidebar) 
{ 
$collections = logged_query_assoc_array("SELECT * FROM ecom_collection WHERE status > 0 && id > 0 ORDER BY title ASC",null,0,array());
if($collections !== false && !empty($collections)) {
?>
<!--[if IE 7]> 
	<ul id="sideMenu" class="MenuBarVertical ie7 navbar sidenav<?php if($isMobile)echo ' isMobile' ;?>">
<![endif]-->
<!--[if !IE 7]> <!-->
	<ul id="sideMenu" class="MenuBarVertical MenuBarActive <?php echo $mobileMenu;?>">
<!--<![endif]--> 
<?php // get products (collections) for given collection product belongs to
		echo"<li class='menu-top'><a class='side-menu-title' href='{$chain['head']['slug']}'><h2>Products</h2></a></li>";
		
	$currentSlug = strtolower(implode('/',$uri));
	
	foreach($collections as $cat) {
		$selectedParent = ($currentSlug == $cat['url']) ? 'selectedParent' : '';
		$openMenu = ($selectedParent != "" || $currentSlug == $cat['url']) ? 'open' : '';
		echo"<li class='menu-bottom'><a class='$selectedParent' href='" . $_config['site_path']."shopping/collection/".$cat['url'] . "'>{$cat['title']}</a></li>";
	}
?>
</ul> <!-- END sideMenu2 -->
<?php } ?>
<?php include('includes/inc-categories-menu.php'); ?>
<div class="clear" style="height:1em;"></div>
<script>
$(document).ready( function() {
	$('#sideMenu.notMobile li').hover( function() {
		$(this).children('ul').addClass('hoveropen');
	}, function() {
		$(this).children('ul').removeClass('hoveropen');
	});
	
	/* Mobile full-size menu (tablets) */
	$('ul#sideMenu.isMobile span.mobileOpen').live('click', function() {
		$(this).removeClass('mobileOpen').addClass('mobileClose');
		var menu = $(this).parent('span').siblings('ul');
		$('#sideMenu ul.MenuBarSubmenuVisible').each( function() {
			$(this).removeClass('MenuBarSubmenuVisible');
		});
		
		$(menu).addClass('MenuBarSubmenuVisible');
	});
	$('ul#sideMenu.isMobile span.mobileClose').live('click', function() {
		$(this).removeClass('mobileClose').addClass('mobileOpen');
		var menu = $(this).parent('span').siblings('ul');
		$(menu).removeClass('MenuBarSubmenuVisible');
	});
	$('ul#sideMenu2.isMobile span.mobileOpen').live('click', function() {
		$(this).removeClass('mobileOpen').addClass('mobileClose');
		var menu = $(this).parent('span').siblings('ul');
		$('#sideMenu2 ul.MenuBarSubmenuVisible').each( function() {
			$(this).removeClass('MenuBarSubmenuVisible');
		});
		
		$(menu).addClass('MenuBarSubmenuVisible');
	});
	$('ul#sideMenu2.isMobile span.mobileClose').live('click', function() {
		$(this).removeClass('mobileClose').addClass('mobileOpen');
		var menu = $(this).parent('span').siblings('ul');
		$(menu).removeClass('MenuBarSubmenuVisible');
	});
	/**/
	/* Mobile Menu */
	$('.isMobile a.side-menu-title').on('click', function(e) {
		e.preventDefault();
		$(this).addClass('clicked');
		$('li.toggled-on').removeClass('toggled-on');
		if($(this).hasClass('toggled-on')) {
			$(this).removeClass('toggled-on');
			$(this).parents('li').siblings('li').removeClass('toggled-on');
		} else {
			$(this).addClass('toggled-on');
			$(this).parents('li').siblings('li').addClass('toggled-on');
		}
		$('a.side-menu-title.toggled-on').each( function() {
			if($(this).hasClass('clicked')) {
				$(this).removeClass('clicked');
			} else {
				$(this).removeClass('toggled-on');
			}
		});
	});

	/**/
});
</script>
<?php
} // if sidebar

?>
</div> <!-- left_col -->
