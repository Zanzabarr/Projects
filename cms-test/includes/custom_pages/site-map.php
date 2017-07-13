<?php
	/* ATOMATED HTML SITE MAP */
?>
<style>
#sitemap, #sitemap ul { list-style:none; }
#sitemap { text-transform:uppercase; }
#sitemap ul { text-transform: capitalize; margin:1em 0; }
#sitemap li { line-height:1.45;margin-bottom:.7em; }
</style>
<div id="content_area" class="wrap default hasSideTestimonial">
<div id="content">
<?php
	$pagequery = logged_query_assoc_array("SELECT pages.* FROM pages WHERE id > 0 && status = 1 && no_follow < 1 && has_menu = 0",null,0,array());
	$donotlist = array("home","site-map","thankyou","thank-you","home_part_2","search");
	$noMenu = array();
	foreach($pagequery as $q) {
		if(!in_array($q['slug'], $donotlist)) {
			$noMenu[] = $q;
		}
	}
	echo "<h1>{$_config['company_name']} SITE MAP</h1><div style='clear:both;'></div>";
	echo "<h2><a href='{$_config['site_path']}'>{$_config['company_name']} Home</a></h2><div style='clear:both;'></div>";
	echo "<ul id='sitemap'>";

	foreach ($top_row as $row)
	{
		$children = false;
		$children = $pages->get_chain_by_slug($row['slug'], true);
		$hasChildren = isset($children['descendants']) && count($children['descendants']) > 0 ;
		$href = $_config['site_path'] . $row['slug'];
		if($row['slug']=='home') $href = $_config['site_path']; // NEW - change home link to root link only

		//** PRINT THE TOP LEVEL HEADING ** //
		echo "<li class='topRow{$selected}'><a  class='{$selected}' href='{$href}'>{$row['title']}</a>{$arrow}";

		// if there are any children, deal with them
		if ( $hasChildren )
		{
		?>
			<ul>
		<?php
			foreach($children['descendants'] as $descendants)
			{
				foreach($descendants as $sorted => $childdata)
				{
					//** OPEN THE CHILD HEADINGS  **//
					echo "<li><a href='{$_config['site_path']}{$childdata['slug']}'>- {$childdata['page_title']}</a>";
					if (isset($childdata['grChild']) )
					{
						echo "<ul>";
						foreach ($childdata['grChild'] as $sorted => $grChildData)
						{
							echo "<li><a href='{$_config['site_path']}{$grChildData['slug']}'>{$grChildData['page_title']}</a></li>";
						}
						echo "</ul>";
					}
				}
			}
			//** CLOSE THE CHILD HEADINGS **//
		?>
			</li></ul>
		<?php
		}
		/* CLOSE TOP LEVEL HEADING */
		echo "</li>";
	}
	foreach($noMenu as $nm) {
		$href = $_config['site_path'] . $nm['slug'];
		echo "<li><a href='{$href}'>{$nm['page_title']}</a></li>";
	}
	/* CLOSE SITEMAP */
	echo "</ul>";
?>
	<div style="clear:both;"></div>
</div><!-- end content -->
	<div style="clear:both;"></div>
</div><!-- end content_area -->
<div style="clear:both;"></div>
