<div class="sidebar">
	<ul>
    	<li id="none">&nbsp;</li>
        <?php if ($PageName == "index.php" && $adminmodule != 'shopping_cart') {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}index.php'\"><i id=\"admin_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}index.php'\"><i id=\"admin\"></i></li>";
		}?>
        
        <?php if ($PageName == "dashboard.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}dashboard.php'\"><i id=\"dashboard_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}dashboard.php'\"><i id=\"dashboard\"></i></li>";
		}?>
        
        <?php if ($PageName == "pages.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}pages.php'\"><i id=\"pages_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}pages.php'\"><i id=\"pages\"></i></li>";
		} 
		
		
		
		
		
		
		
		
		
		

		
		if ($PageName == "blogs.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/blog/blog.php'\"><i id=\"blogs_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}modules/blog/blog.php'\"><i id=\"blogs\"></i></li>";
		}
/*
		if ($PageName == "galleries.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}galleries.php'\"><i id=\"galleries_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}galleries.php'\"><i id=\"galleries\"></i></li>";
		}
		
		?>
*/        
//		 if ($adminmodule == "store_locator") {
		if ($PageName == "list_locations.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/store_locator/list_locations.php'\"><i id=\"locator_active\"></i></li>";
		} else if (is_dir('modules/store_locator') || is_dir('../../modules/store_locator')){ 
		echo "<li onclick=\"window.location='{$baseUrl}modules/store_locator/list_locations.php'\"><i id=\"locator\"></i></li>";
		}
/*        
        <?php if ($PageName == "maillist.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/maillist/maillist.php'\"><i id=\"maillist_active\"></i></li>";
		} else if (is_dir('modules/maillist') || is_dir('../../modules/maillist')){ 
		echo "<li onclick=\"window.location='{$baseUrl}modules/maillist/maillist.php'\"><i id=\"maillist\"></i></li>";
		}?>
        
        <?php if ($PageName == "newsletter.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/newsletter/newsletter.php'\"><i id=\"newsletter_active\"></i></li>";
		} else if (is_dir('modules/newsletter') || is_dir('../../modules/newsletter')){ 
		echo "<li onclick=\"window.location='{$baseUrl}modules/newsletter/newsletter.php'\"><i id=\"newsletter\"></i></li>";
		}
		
		 if ($PageName == "listings.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/realtor/listings.php'\"><i id=\"realtor_active\"></i></li>";
		} else if (is_dir('modules/realtor') || is_dir('../../modules/realtor')){ 
		echo "<li onclick=\"window.location='{$baseUrl}modules/realtor/listings.php'\"><i id=\"realtor\"></i></li>";
		} 
*/		
		 if ($adminmodule == 'shopping_cart') {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/shopping_cart/index.php'\"><i id=\"ecommerce_active\"></i></li>";
		} else { 
		echo "<li onclick=\"window.location='{$baseUrl}modules/shopping_cart/index.php'\"><i id=\"ecommerce\"></i></li>";
		}		 
/*		
		if ($PageName == "jobs.php") {
			echo "<li style=\"background:url({$baseUrl}images/sidebar_dividers_hover.jpg)\" onclick=\"window.location='{$baseUrl}modules/jobs/jobs.php'\"><i id=\"jobs_active\"></i></li>";
		} else if (is_dir('modules/jobs') || is_dir('../../modules/jobs')){ 
		echo "<li onclick=\"window.location='{$baseUrl}modules/jobs/jobs.php'\"><i id=\"jobs\"></i></li>";
		}
*/		
		?>

    </ul>
</div>