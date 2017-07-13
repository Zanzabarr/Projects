<?php
// ************* INITIALIZATION ********************* //

// including the config file starts initialization
include('includes/config.php');
// prepare tinyMce for inline editing
createTinySalt();
$hasInlineEditable = false;
if(isset($_POST['inline_editable']))
{
	$_SESSION['inline_editable'] = $_POST['inline_editable'] != 'close';
}


// ********************* Menuless PAGES  ********************* //

// pages that don't use the header/footer that is used by the rest of the site are included here.
// 'Coming Soon' and other landing pages are examples
// These pages are contained in includes to prevent direct navigation by users
if (array_key_exists($page_base, $_SESSION['menulessPages']) )
{
	// this site has a sample landing page as well as the home page in /includes/menuless_pages/
	include( $_config['rootpath'] . 'includes/menuless_pages/' . $page_base . '.' . $_SESSION['menulessPages'][$page_base]);
}

else // ********************* PAGES With Menus ********************* //

{
// Page/s Classes are used to generate menu bars as well as providing other page functions
$pages = new Pages($_config['db']);
if ($pages->error) do_site_down();

// ********************* START HEADER ********************* //
// create the head section
include_once( $_config['rootpath'] . 'includes/inc-head.php');




// determine what kind of menu system is in use and get their variables
$hasSidebar = false;
$hasTopMenu = false;
// if menu is top/side style
if ($_config['menu_type'] == 'top-side')
{
    // top level menu items
    $top_row = $pages->get_top_menu_heads();

    // gets the side menu data based on current page
    $chain = $pages->get_chain_by_slug($page_base, true);

    // if no chain passed, maybe this is a shopping page, get the shopping chain
    if($chain === false && isset($_config['cartName']) && $page_base == $_config['cartName']) {
        // objCategory passed from shopping_cart/frontend/head.php
        // get the category tree (limited to a depth of 2) based on the category determined by the url
        //$ecom_cat_data = $objCategory->get_category_chain(2);
        $chain = $objCategory->get_ecom_menu_data();
    }// echo display_array($chain);
}
if ($_config['menu_type'] == 'top')
{
	// get the top level headers to be passed to inc-top-menu
	$top_row = $pages->get_top_menu_heads();

}

// hasTopMenu/hasSidebar are css class names used to control display as well as
//  being used for php logic
if (isset($top_row) && count($top_row) > 0 )
{
	$hasTopMenu = 'hasTopMenu';
}
if (isset($chain['descendants']) && count ($chain['descendants'])  )
{
	$hasSidebar = 'hasSidebar';
}
if($module == "events") $hasSidebar = false;
if($module == "shopping_cart") $hasSidebar = 'hasSidebar';

// show side testimonial if not blog or product pages

if($module != 'blog' && $module != 'shopping_cart' && $module != "events") $hasSideTestimonial = 'hasSideTestimonial';
else $hasSideTestimonial = false;


// is this a valid page?
$valid_page = isset($info['id']) ? ($pages->valid_page($info['id']) ) : false;

// create the top section
?>
<!-- ********************* START TOP ********************* -->
<div id="top">
	
	<!-- ** ADD ICON BAR **  -->
	<?php include_once ( $_config['rootpath'] . 'iconbar/iconbar.php' ); ?>
	
	<!-- ** ADD HEADER **  -->
	<div id="header" class="wrap">
		<?php include_once( $_config['rootpath'] . 'includes/inc-header.php'); ?>
	</div><!-- header -->

</div> <!-- top -->
<!-- ********************* END OF TOP ********************* -->

<!-- ********************* START MIDDLE ********************* -->

<div id="middle">
<?php
// ********************* CUSTOM PAGES ********************* //
// pages that use the header/footer used by the rest of the site but also custom coding are accessed here
// These pages are contained in includes to prevent direct navigation by users

if (array_key_exists($page_base, $_SESSION['customPages']) ) :
	// this site has a sample landing page as well as the home page in /includes/menuless_pages/
	include($_config['rootpath'] . 'includes/custom_pages/' . $page_base.'.'.$_SESSION['customPages'][$page_base]);

// ********************* END CUSTOM PAGES ********************* //

else :
// ********************* START STANDARD PAGES ********************* //

?>

	<div id="content_area" class="wrap <?php echo $module . ' ' . $hasSidebar . ' ' . $hasSideTestimonial; ?>">
		<div id="content_area-inner">
		<?php
		// IF BLOG
		if($module =='blog'){
			
			// spits out its own version of <content> and <sidebar>
			
			// use module if there is a defined customName
			if ( isset($_config['customNames']) && is_array($_config['customNames']) && array_key_exists($page_base, $_config['customNames']) )
			{
				include( $_config['admin_modules'] . $_config['customNames'][$page_base] . '/frontend/' . $_config['customNames'][$page_base] .'.php' );
			}
			else echo "<h2>Sorry, an error occurred with the Blog.</h2><p><a href='{$_config['site_path']}'>Go to home page</a></p>";
		}
		
		// IF NOT BLOG
		else {
				
			// ADD SIDE BAR
			if ( $hasSidebar || $hasSideTestimonial )
			{
				if($module == "shopping_cart") {
					include_once($_config['rootpath'] . 'includes/inc-products-sidebar.php');
				} else {
					include_once($_config['rootpath'] . 'includes/inc-side-menu.php');
				}
				
				//if($hasSideTestimonial) {include_once('includes/inc-side-testimonial.php'); } no side testimonial for this site
			}
			
			?>
			
			<!-- CONTENT -->
			<div id="content">
				<div id="content-inner">
					
					<?php
					
					// use module if there is a defined customName
					if ( isset($_config['customNames']) && is_array($_config['customNames']) && array_key_exists($page_base, $_config['customNames']) )
					{
						include( $_config['admin_modules'] . $_config['customNames'][$page_base] . '/frontend/' . $_config['customNames'][$page_base] .'.php' );
					}
					
					else // otherwise, this is a regular page
					{ 

							// display content if valid page, otherwise output an error message
							if ( $valid_page ) 
							{
								$editable = array(
									'editable_class' => 'inlineUploadable',
									'attributes' => array(
										'data-jump' => 'admin/edit_page.php?page_id='.$info['id'].'#content',
										'data-jump-type' => 'back'
									),
									'secure_data' => array(
										'table' => 'pages',				// req for save
										'id-field' => 'id',				// req for save && upload
										'id-val' => $info['id'],	// req for save && upload
										'field' => 'content',			// req for save
										'upload-type' => 'dflt'			// req for upload
									)
								);

								display_content($info['content'],$editable);
							}
							else echo "<h2>Sorry, this page cannot be found.</h2><p>Please check your link or contact an administrator if there is an error on the website.</p><p><a href='{$_config['site_path']}'>Go to home page</a></p>";
					}

					?><div style="clear:both"></div>
				
				</div><!-- END content-inner -->
			</div> <!-- END content -->
			<div style="clear:both"></div>
			
			<?php
		
		} // end if not blog
		?>
		</div><!-- END content_area-inner -->
	</div><!-- END content_area -->

<?php 
endif;
// ********************* END STANDARD PAGES ********************* //
?>

</div>
<!-- ********************* END MIDDLE ********************* -->

<?php

// ********************* FOOTER ********************* //
include ($_config['rootpath'] . 'includes/inc-bottom.php');
// ********************* END FOOTER ********************* //
}
?>
