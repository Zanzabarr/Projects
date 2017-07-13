<?php	/*THIS FILE PROVIDES SILOED PAGE MENUS AND DOES NOT USE SPRY
			IT IS ACCESSED VIA AJAX POST FROM JS/NAVIGATION.JS */

//prerequisites
include('../includes/config.php');
$pages = new Pages($_config['db']);

//build menu html and send back to navigation.js
if(isset($_POST['parent'])) {
	$children = false;
	$children = $pages->get_chain_by_slug($_POST['parent'], true);

	//set select variable for selected menu
	if($isMobile) {
		$selvar = " mobileSelected";
	} else {
		$selvar = " selectedMenu";
	}

	$html = "";
	foreach($children['descendants'] as $descendants)
	{
		foreach($descendants as $sorted => $childdata)
		{
			$selected = ($childdata['slug'] == $_POST['slug'] ) ? $selvar : '';
			$arrow = (isset($childdata['grChild'])) ? "<a class='arrowOpen'>&#9658</a>" : "";
			//** OPEN THE CHILD HEADINGS  **//
			$html .= "<li class='{$selected}'><a class='MenuBarItemSubmenu{$selected}' href='{$_config['site_path']}{$childdata['slug']}'>{$childdata['page_title']}</a>{$arrow}";
			if (isset($childdata['grChild']) )
			{
				$html .= "<ul class='MenuBarSubSubmenu'>";
				foreach ($childdata['grChild'] as $sorted => $grChildData)
				{
					$selected = ($grChildData['slug'] == $_POST['slug'] ) ? $selvar : '';
					$html .= "<li class='{$selected}'><a class='grChildItem{$selected}' href='{$_config['site_path']}{$grChildData['slug']}'>{$grChildData['page_title']}</a></li>";
				}
				$html .= "</ul>";
			}
			//** CLOSE THE CHILD HEADINGS **//
			$html .= "</li>";
		}
	}

	echo $html;
}
?>
