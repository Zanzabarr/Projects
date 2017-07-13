<?php	/* SITE SEARCH ENGINE - THIS FILE REQUIRED FOR SITE SEARCH WITH INCLUDES/INC-SEARCH.PHP */

//array of searchable modules
$searchable = array("blog","stories","devotionals","biography","events","news","shopping_cart");

function siteSearch($terms,$mod = "") {
	global $_config;
	if($mod == "") {
		$query = "SELECT page_title, slug, content, MATCH(content) AGAINST ('{$terms}' IN BOOLEAN MODE) AS m FROM pages WHERE status > 0 && id > 0 && visibility = 0 && no_follow = 0 && MATCH(content) AGAINST ('{$terms}' IN BOOLEAN MODE) ORDER BY m desc";
		
		$result = logged_query($query,0,array());
		if($result !== false && !empty($result)) {
			$result['q'] = $terms;
		}
		return $result;
	} else {
		include_once($_config['admin_modules']."{$mod}/system/search.php");
	}
}

function buildFrontend($frontArr, $mod) {
	global $_config;
	if(is_array($frontArr) && !empty($frontArr) && $frontArr['q']!="") {
		//get the search phrase and remove from array
		$term = $frontArr['q'];
		unset($frontArr['q']);
		
		echo "<h2 data-mod='{$mod}' class='resultToggle'>Results from {$mod}";
		
		if(count($frontArr) > 3) {
			echo " <span class='toggleImg'>show more &#9660;</span>";
		}
		
		echo "</h2>";
		
		
		//loop thru $frontArr
		$l = 0;
		foreach($frontArr as $f) {
			$l++;
			if($l == 4) {
				echo "<section id='{$mod}-toggle-wrap' class='toggleWrap'>";
			}
			echo "<span class='resultListing'><a href='{$_config['site_path']}{$f['url']}'>{$f['title']}</a><br /><p>{$f['intro']}</p></span>";
		}
		if($l > 3) {
			echo "</section>";
		}
		
		
	} else {
		echo "<h3 style='margin-left:1em;'>NO VALID SEARCH - YOU ARE BEING REDIRECTED</h3>";
		echo "<script>location.href = '{$_config['site_path']}';</script>";
	}
}

if(isset($_GET['q']) && $_GET['q']!="") {
	$q = trim(strip_tags($_GET['q']));
	//echo $q."<div class='clear' style='height:3em;'></div>"; //dev & troubleshooting purposes
	
	//create content_area and content containers
	echo "<div id='content_area' class='wrap'><div id='content'><h1>Search Results for: ".$q."</h1>";
	
	//get pages search first
	$return = siteSearch($q);
	if($return !== false && !empty($return)) {
		$q = $return['q'];
		unset($return['q']);
	
		//build array for frontend
		$frontArr = array();
	
		for($i = 0; $i < count($return); $i++) {
			$frontArr[$i]['title'] = $return[$i]['page_title'];
			$frontArr[$i]['url'] = $return[$i]['slug'];
			$frontArr[$i]['intro'] = substr(strip_tags(htmlspecialchars_decode($return[$i]['content'])),0,120);
		}
		$frontArr['q'] = $q;
		
		buildFrontend($frontArr,"pages");
	}
	
	//get modules search
	foreach($_SESSION['activeModules'] as $amod) {
		if(in_array($amod, $searchable)) {
			$q = trim(strip_tags($_GET['q']));
			
			siteSearch($q, $amod);
			//buildFrontend($return, $amod);
		}
	}
	
	//close content and content_area containers
	echo "</div><div style='clear:both'></div></div>";
	
} else {
	echo "<h3 style='margin-left:1em;'>NO VALID SEARCH - YOU ARE BEING REDIRECTED</h3>";
	echo "<script>location.href = '{$_config['site_path']}';</script>";
}
?>
<style>
	.toggleWrap {
		min-height: 3em;
	}
	.resultToggle {
		background:#eee;
		padding:2px;
	}
	.toggleImg {
		font-size:.65rem;
		font-weight:800;
		margin-left:2em;
	}
</style>

<script>
$(document).ready( function() {
	$(".toggleWrap").each( function() {
		$(this).slideUp();
	});
	$(".resultToggle").on('click', function() {
		var mod = $(this).attr('data-mod');
		
		$(".resultToggle").each( function() {
			if($(this).attr('data-mod') == mod) {
				if($(this).hasClass('open')) {
					$(this).removeClass('open');
					$(this).children('.toggleImg').html("show more &#9660;");
					$("#"+mod+"-toggle-wrap").slideUp();
				} else {
					$(this).addClass('open');
					$(this).children('.toggleImg').html("show less &#9650;");
					$("#"+mod+"-toggle-wrap").slideDown();
				}
			} else {
				$(this).removeClass('open');
				$(this).children('.toggleImg').html("show more &#9660;");
			}
		});
		
		$(".toggleWrap").each( function() {
			if($(this).attr('id') != mod+"-toggle-wrap") {
				$(this).slideUp();
			}
		});
	});
});
</script>


<?php
	/* EXAMPLE $return:
		Array ( [0] => Array ( [content] => <h1>Another Ministry Page</h1> <p>Bacon Booger ipsum markup dolor sit amet leberkas jowl beef, ham pork chop bresaola andouille ribeye short ribs pastrami frankfurter tenderloin corned beef. Corned beef jerky brisket frankfurter boudin short loin ribeye tail turkey cow kevin meatloaf. Chicken biltong t-bone bresaola sirloin pork chop andouille tenderloin ham hock pork bacon beef ribs fatback cow tail. Short loin ham hock cow pancetta. Andouille kevin tail short loin beef kielbasa, pork loin prosciutto pig boudin porchetta swine ham strip steak. Rump ball tip pork ham hock pork loin.</p> <p>Turkey biltong venison beef, cow leberkas pork chop tongue turducken landjaeger strip steak pork beef ribs capicola fatback. Capicola boudin beef ribs spare ribs. Andouille beef ribs frankfurter, capicola venison tenderloin bresaola chicken sirloin salami cow t-bone shank filet mignon doner. Capicola ground round porchetta short ribs bacon pastrami prosciutto strip steak doner. Landjaeger pork jerky t-bone bresaola boudin. Corned beef pork belly turkey chicken tongue fatback leberkas rump sirloin chuck ground round tri-tip jerky biltong. Biltong frankfurter hamburger brisket.</p> [m] => 1 ) [q] => "ipsum markup" )
	*/
?>