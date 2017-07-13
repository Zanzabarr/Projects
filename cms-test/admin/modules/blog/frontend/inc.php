<!-- inside jobs/inc.php
<?php 
/*
$post = rtrim($_GET['post'],"/");

if(isset($_GET['cat'])){
	$meta = good_query_assoc("SELECT * FROM `blog_cat` WHERE `url` = '{$_GET['cat']}' LIMIT 1");
}elseif(isset($_GET['post'])){
	$meta = good_query_assoc("SELECT * FROM `blog_post` WHERE `url` = '{$post}' LIMIT 1");
}else{
	$meta = good_query_assoc("SELECT * FROM `blog_options` WHERE `id` = '1' LIMIT 1");
}
	//print_r($meta);
	
*/
?>
			
<title><?php echo $meta['metatitle']; ?></title>
<meta name="description" content="<?php echo $meta['metadesc']; ?>" />
<meta name="keywords" content="<?php echo $meta['metakeys']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $_config['site_path']; ?>modules/<?php echo $module; ?>/frontend/style.css" />
<script type="text/javascript" src="<?php echo $_config['site_path']; ?>modules/<?php echo $module; ?>/frontend/inc.js"></script>
<!-- inside jobs/inc.php -->


<!--  not using this as head info, moved that elsewhere. This is where frontend module includes will go instead. (Most should no need)