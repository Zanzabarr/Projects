<?php

$base_url = $_config['site_path'];
global $base_url;
$news_items_url = $base_url."admin/modules/news_items/";
global $news_items_url;
?>

<!-- FOR EXPANDING DIV -->
	
<script type="text/javascript" src="<?php print $news_items_url;?>jquery/toggleElements/jquery-latest.pack.js"></script>
<script type="text/javascript" src="<?php print $news_items_url;?>jquery/toggleElements/pluginpage.js"></script>
<script type="text/javascript" src="<?php print $news_items_url;?>jquery/toggleElements/jquery.toggleElements.pack.js"></script>


<script type="text/javascript">
	$(document).ready(function(){
		$('div.toggler-1').toggleElements( );
	});
</script>
<!-- END OF EXPANDING DIV -->

<?php

// set seo data for use in the main head
$seot = "news_items and Stories from {$_config['company_name']}";
$seod = "news_items and Stories";
$seok = "news_items, Stories";

?>
<!-- news_items module includes -->
<?php $moduleLink = '<link rel="stylesheet" type="text/css" href="'.$_config['admin_url'].'modules/'.$module.'/frontend/style.css" />
<script type="text/javascript" src="'.$_config['admin_url'].'modules/'.$module.'/frontend/inc.js"></script>'; 
?>