<div id="inside-banner" class="wrap">
	<?php 
		if ($chain['head']['id']==3){print "<img src=\"{$_config['site_path']}images/banners/banner-about.jpg\" />";} // About Section
		elseif ($chain['head']['id']==4){print "<img src=\"{$_config['site_path']}images/banners/banner-web-design.jpg\" />";} // Web Design Section
		elseif ($chain['head']['id']==17){print "<img src=\"{$_config['site_path']}images/banners/banner-web-features.jpg\" />";} // Web Features Section
		elseif ($chain['head']['id']==11){print "<img src=\"{$_config['site_path']}images/banners/banner-internet-marketing.jpg\" />";} // Internet Marketing Section
		elseif ($chain['head']['id']==12){print "<img src=\"{$_config['site_path']}images/banners/banner-contact.jpg\" />";} // Contact Section
		elseif ($chain['head']['id']==36){print "<img src=\"{$_config['site_path']}images/banners/banner-blog.jpg\" />";} // Blog
		else {$num=rand(1,2); print "<img src=\"{$_config['site_path']}images/banners/banner-websites{$num}.jpg\" />";} // Other Pages
	?>
	
	
</div><!-- inside-banner -->
<div style="clear:both"></div>
