<title><?php echo $meta['metatitle']; ?></title>
<meta name="description" content="<?php echo $meta['metadesc']; ?>" />
<meta name="keywords" content="<?php echo $meta['metakeys']; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo $_config['site_path']; ?>modules/<?php echo $module; ?>/frontend/style.css" />
<script type="text/javascript" src="<?php echo $_config['site_path']; ?>modules/<?php echo $module; ?>/frontend/inc.js"></script>

<!--  not using this as head info, moved that elsewhere. This is where frontend module includes will go instead. (Most should no need)
