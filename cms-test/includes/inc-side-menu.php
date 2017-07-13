<?php // THIS SIDE MENU PROVIDES TOUCHSCREEN SUPPORT WITHOUT SPRY AND PROVIDES MENUS FOR GRANDCHILDREN
//dynamic creation of a sidebar

// $chain passed from index.php: contains sidebar info
// $chain['head']										info about the heading
// $chain['head']['slug']									slug for url
// $chain['head']['id']     								id of page
// $chain['head']['title']  								page title
// $chain['descendants']								info about the children pages
// $chain['descendants']['sorted_order'] 					position of descendant in order
// $chain['descendants']['sorted_order'] ['slug']			slug for url
// $chain['descendants']['sorted_order'] ['id']  		   	id of page
// $chain['descendants']['sorted_order'] ['title']  		page title
// $chain['selected']									info about the selected page
// $chain['selected']['id']									selected page's id

$mobileMenu = $isMobile ? ' isMobile' : ' notMobile';
?>
<div id="sidebar">

<?php if($hasSidebar)
{
    if(isset($chain['descendants']))
	{
		?>
		<ul id="sideMenu" class="MenuBarVertical MenuBarActive <?php echo $mobileMenu;?>">

		<?php
		echo"<li class='menu-top'><a class='side-menu-title' href='{$chain['head']['slug']}'><p>{$chain['head']['title']}</p></a></li>";
    	$count = 0;
		foreach($chain['descendants'] as $descendants)
		{
			foreach($descendants as $sorted => $childdata)
			{
				$count++;
            	$first = ($count == 1) ? ' first' : '';
				$selected = $childdata['id'] == $chain['selected']['id'] ? ' selectedMenu' : '';
				$arrow = (isset($childdata['grChild'])) ? "<a class='sideArrow'>&#9658</a>" : "";
				//** OPEN THE CHILD HEADINGS  **//
				echo "<li class='{$selected}'><a class='MenuBarItemSubmenu{$first}' href='{$_config['site_path']}{$childdata['slug']}'>{$childdata['page_title']}</a>{$arrow}";
				if (isset($childdata['grChild']) )
				{
					echo "<ul>";
					foreach ($childdata['grChild'] as $sorted => $grChildData)
					{
						$selectedGr = $grChildData['id'] == $chain['selected']['id'] ? ' selectedMenu' : '';
						echo "<li class='{$selectedGr}'><a href='{$_config['site_path']}{$grChildData['slug']}'>{$grChildData['page_title']}</a></li>";
					} // foreach childdata
					echo "</ul>";
				} // isset grChild
			} // foreach descendant as sorted
			echo "</li>";
		}  // foreach cahin(descendant)
?>
		</ul> <!-- END sideMenu -->
<?php
	} // if has descendants
} // if sidebar
?>
</div> <!-- left_col -->
