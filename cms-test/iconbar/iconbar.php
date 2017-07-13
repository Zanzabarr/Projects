<link href="iconbar/iconbar.css" rel="stylesheet">
<div id="iconbar">
	<div class="wrap" style="position:relative;background:transparent;">
		
		<div id="sharethis-holder" style="display:none;">
			<!-- start sharethis -->
			<span class='st_sharethis_large' displayText='ShareThis'></span>
			<span class='st_facebook_large' displayText='Facebook'></span>
			<span class='st_googleplus_large' displayText='Google +'></span>
			<span class='st_twitter_large' displayText='Tweet'></span>
			<span class='st_email_large' displayText='Email'></span>
			<!-- end sharethis -->
		</div>
		
		<script>
		// script to show/hide generated sharethis icons
		function sharethistoggle(){
			el=document.getElementById('sharethis-holder');
			el.style.display=(el.style.display!='none'?'none':'');
		}
		</script>
		
		<!-- ############################################################# -->
		
		<?php
		// PREP ICONS
		$socialmedia = array 
		(
			// COMMENT OUT THOSE ICONS NOT NEEDED: //,array( name, icon, url ) 
			
			// LOCAL LINKS
			array( "ShareThis", "share", "javascript:sharethistoggle()" ) // no first comma
			,array( "Blog", "blog", "blog" ) 
			// ADD YOUR SM LINKS at last spot
			,array( "Facebook", "facebook", "https://www.facebook.com/" ) 
			,array( "Yelp", "yelp", "https://www.yelp.com/" ) 
			,array( "Google Plus", "googleplus", "https://plus.google.com/" )
			,array( "Twitter", "twitter", "https://twitter.com/" )
			,array( "Instagram", "instagram", "http://instagram.com/" )
			,array( "Linked In", "linkedin", "http://www.linkedin.com/" )
			,array( "Pinterest", "pinterest", "http://www.pinterest.com/" )
			,array( "YouTube", "youtube", "https://www.youtube.com/" )
		);
		
		// icons are white 32px height. need new icons? https://icomoon.io/app/#/select
		
		// CREATE ICONS
		foreach($socialmedia as $sm) {
			$SM_name = $sm[0];
			$SM_icon = $sm[1];
			$SM_iconpath = "iconbar/icons/".$SM_icon;
			$SM_url = $sm[2];
			
			// defaults
			$title = "{$_config['company_name']} @ {$SM_name}";
			$target = " target='_blank'";
			
			// overrides
			if($SM_name=="ShareThis") { $title = "click for ShareThis icons"; $target =  ""; }
			if($SM_name=="Blog") { $title = "Our Blog"; $target =  ""; }
			
			
			// PNG ONLY
			echo "<a href='{$SM_url}' title='{$title}'{$target}><img src='{$SM_iconpath}.png' alt='{$SM_name}' class='icon' /></a>";
			
			// SVG with PNG fallback for Android 2.3 and IE8
			//echo "<a href='{$SM_url}' title='{$title}'{$target}><image xlink:href='{$SM_iconpath}.svg' src='{$SM_iconpath}.png' alt='{$SM_name}' class='icon' /></a>";
			
		}
		
		//add search to social bar
		include_once($_config['rootpath']."iconbar/search.php");
		?>
		
	</div>
</div>
