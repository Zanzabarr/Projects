<div id="header-logo">
  <a href="<?php echo $_config['site_path']; ?>" title="<?php echo $_config['company_name']; ?> - Home">
    <img src="images/logo.png"  alt="<?php echo $_config['company_name']; ?>">
  </a><!-- header_logo -->
</div>

<div id="header-contact">
<?php include($_config['admin_modules'] . 'members/frontend/login/member_panel/member_login.php'); ?>
	<a href="contact"><div id="signup-bar"></div></a>
	<a href="tel:999.999.9999"><div id="call-bar"></div></a>
    <div id="girl"><img src="images/website-girl.png" alt="website-girl" /></div> <!-- girl -->
</div> <!-- header contact -->


<div style="clear:both"></div>

<div class="mobile-phone">
CLICK TO CALL: <a href="tel:999.999.9999">999.999.9999</a>
</div> <!-- mobile-tablet -->


<!-- ** ADD TOP MENU ** //  -->
<?php if ( $hasTopMenu )	include_once( $_config['rootpath'] . 'includes/inc-top-menu.php'); ?>


<?php 

if ($page_base == $_config['home_slug']) {include_once('includes/inc-homebanner.php');}
// elseif (isset($_config['cartName']) && $page_base == $_config['cartName'] && uri::get('products') ) {}
else include_once('includes/inc-banner.php');
?>

<div style="clear:both;"></div>

