<?php
// if this isn't a module, use the navigated page's SEO data

if ($module == 'default')
{
	$info = $_config['db']->select('pages', "`slug`=:page_base AND `id`>0", array(':page_base' => uri::get(0)), $fields="*");
	if (isset($info[0])) $info = $info[0];
	else $info = array();
	if(count($info))
	{
		$seot = $info['seo_title'];
		$seod = $info['seo_description'];
		$seok = $info['seo_keywords'];
	} else {
		$seot = 'Page Not Found';
		$seod = 'Page Not Found';
		$seok = '';
		$info['no_follow'] = 1;
	}
} else {
	// module's seo data (and other special head items is contained in modules/frontend/head
	include_once ($_config['admin_modules'] . '/' . $module . '/frontend/head.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<title><?php echo htmlspecialchars_decode($seot); ?></title>

<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="<?php echo htmlspecialchars_decode($seod); ?>" />
<meta name="Keywords" content="<?php echo htmlspecialchars_decode($seok); ?>" />
<?php echo (isset($info['no_follow']) && $info['no_follow'] == 1) ? '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">'."\n" : ''; ?>
<meta name="Author" content="Test Cms">
<!-- responsive -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<base href="<?php echo $_config['site_path']; ?>" />

<!-- Standard CSS files -->
<link rel="stylesheet" type="text/css" href="css/basic.css?<?php echo filectime($_config['rootpath'] . "css/basic.css"); ?>" media="all, handheld" />
<link rel="stylesheet" type="text/css" href="css/navigation.css?<?php echo  filectime($_config['rootpath'] . "css/navigation.css");?>" media="all, handheld" />
<link href="admin/modules/testimonials/frontend/style.css" rel="stylesheet" type="text/css" />
<link href="admin/modules/members/frontend/login/member_panel/member_login.css" rel="stylesheet" type="text/css" />
<!-- End of Standard CSS files -->

<link href='//fonts.googleapis.com/css?family=Open+Sans:400,800,300,700' rel='stylesheet' type='text/css'>
<!-- jQuery Files -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>

<!-- js config files -->
<script type='text/javascript'>
var config = new Array();
config.site_path = "<?php echo $_config['site_path'];?>";
config.admin_url = "<?php echo $_config['admin_url'];?>";
</script>


<!-- Active Menu Files -->
<!-- TOUCHSCREEN SUPPORT WITHOUT SPRY-->
<script type="text/javascript" src="js/navigation.js"></script>

<?php if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) : ?>
<!-- Front-End Editing -->
<script type="text/javascript" src="admin/js/tiny_mce/tinymce.min.js"></script>
<script type="text/javascript" src="admin/js/tiny_custom_plugins.tinymce.plugin.js"></script>
<script type="text/javascript" src="admin/js/tiny_mce_settings.js"></script>
<?php endif; ?>

<!-- JS and CSS utilities-->
<script type="text/javascript" src="js/Placeholders.min.js"></script>
<link rel="stylesheet" href="js/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="js/functions.js"></script>

<!-- galleries -->
<script type="text/javascript" src="admin/js/cycle.js"></script>
<script type="text/javascript" src="admin/modules/galleries/frontend/displaygallery.js"></script>
<link rel="stylesheet" href="admin/modules/galleries/frontend/displaygallery.css?<?php echo filectime($_config['rootpath'] . "admin/modules/galleries/frontend/displaygallery.css"); ?>" type="text/css" media="screen" />

<!-- banner links -->
<link rel="stylesheet" type="text/css" href="css/homebanner.css" />
<?php if($not_phone) : ?>
	<script type="text/javascript" src="js/banner_functions.js"></script>
<?php endif; ?>

<!-- Share This links -->
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "b0ce38d6-364c-4e16-a5a1-66e4fcd9feb4", doNotHash: true, doNotCopy: false, hashAddressBar: false});</script>

<?php if(uri::get(0) == 'home') :?>
<!-- homepage links -->
<script type="text/javascript" src="js/homepage_functions.js"></script>
<?php
endif;

// if the module's head has set links that have to occur after jquery and other precedences: write them
if (isset($moduleLink) ) echo $moduleLink;

// add the googlecode for live sites
// and prevent image contextmenu
if (! $_config['debug']) :
	include ($_config['rootpath'] . "includes/googlecode.php");
?>
<script type="text/javascript">
    $(document).ready(function(){
		$("img").bind("contextmenu",function(e){
			return false;
		});
    });
</script>
<?php endif; ?>

<script type="text/javascript" src="js/respond.min.js"></script>

<!-- sharethis -->
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="//w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "3e5b23a3-0c6d-4d14-94d3-58261499cf0a", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

</head>
<body>
