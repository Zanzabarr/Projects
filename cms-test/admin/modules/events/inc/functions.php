<?php

// get options for displaying members data from members_options table
function getEventsOptions()
{
	$options = logged_query("
		SELECT * 
		FROM events_options
	",0,array());
	return isset($options[0]) ? $options[0] : array();
}


function getCategoriesData()
{
    global $_config;

	//create categories array
	$categories = array();
    $tmpcategories=logged_query("SELECT * FROM `events_cat` WHERE `id` >= 0 ORDER BY `title` ASC",0,array());
	if($tmpcategories):foreach ($tmpcategories as $category){
		// convert category data to useable format
		$category['status_word'] = $category['status'] == 0 ? 'Draft' : 'Published';
		
		// update the categories array
		$categories[$category['id']] = $category;
	}endif;
	//get category colors
	$colors = logged_query("SELECT cat_id, color FROM events_colors",0,array());
	if($colors): foreach($colors as $color) {
		foreach($categories as $category) {
			if($category['id']==$color['cat_id']) $categories[$category['id']]['color'] = $color['color'];
		}
	} endif;

	return $categories;
}

function getEventsData()
{
    global $_config;

	//create events array
	$events = array();
    $tmpevents=logged_query("SELECT * FROM `events` WHERE `id` > 0 ORDER BY `start_date` DESC",0,array());
	if($tmpevents) : foreach ($tmpevents as $event){
		// convert events data to useable format
		$event['status_word'] = $event['status'] == 0 ? 'Draft' : 'Published';
        $event['arCat'] = categoryArray($event['cate']);
        
		// set default data
		
		// update the events array
		$events[$event['id']] = $event;
	}	endif;

	return $events;
}


function buildCatRows($categories, $colors = array())
{
    global $_config;

    foreach($categories as $category)
    {
?>
        <div class='menu_row'> 
			<span class='cat_color' style='background:<?php echo isset($category['color']) ? $category['color'] :'';?>;'>&nbsp;</span>
            <span class='cat_title'><?php echo $category['title']; ?></span>
            <span class="cat_status"><?php echo $category['status_word']; ?></span>
            <span class="op_group">
                <a target='_blank' href='<?php echo $_config['site_path']; ?>events/category/<?php echo $category['url'];?>' >
                    <img class='tipTop' title='See it as it will appear live.' src="../../images/view_icon.png" alt="View">
                </a>
                <a href="<?php echo $_config['admin_url']; ?>modules/events/events_category_new.php?catid=<?php echo $category['id'];?>">
                    <img class='tipTop' title='Edit this Category' src="../../images/edit.png" alt="Edit">
                </a>
                <a href="<?php echo $_config['admin_url'].'modules/events/events_category.php?delete='.$category['id']; ?>" class="deletecategory" rel="<?php echo $category['id']; ?>">
                    <img class='tipTop' title='Permanently remove this category. <br /> note: related events will not be lost.' src="../../images/delete.png" alt="Delete" />
                </a>
                <div class='clearFix'></div>
            </span>

        </div>
        <div class='clearFix'></div>
<?php
    }
}


function buildEventsMenu($events, $categories)
{
	$menu = "<div class ='events_grp' >";
	foreach($events as $row)
	{
		$menu .= "<div class ='events_row' >"."\n";
		$menu .=	"<div class ='menu_row'>"."\n";
		$menu .= 		buildMenuRow($row, $categories)."\n";
		$menu .=	"</div>"."\n";
		$menu .= "</div>"."\n";// end of events_row
	}	
	$menu .= "</div>";// end of events_grp
	return $menu;
}


function buildMenuRow($event, $categories)
{	
	global $_config;
	$menu ="<span class='row_title'>".$event['title']."</span>"."\n";
	$desc = writeCategory($event['arCat'], $categories);
	$menu .="<span class='row_cat' >".$desc."</span>"."\n";
	$menu .="<span class='row_date' >";

    // determine whether or not event uses time of day
    if($event['enable_time']) $menu .= date('g:i A, M j, Y', strtotime($event['start_date']));
    else $menu .= date('M j, Y', strtotime($event['start_date']));

    $menu .= "</span>"."\n";

	$menu .= "<span class='row_status'>".$event['status_word']."</span>"."\n";
		
	$menu .="<span class='op_group'>
		<a target='_blank' href='{$_config['site_path']}events/".$event['url']."'>
		<img class='tipTop' title='See event as it will appear live.' src=\"../../images/view_icon.png\" alt=\"View\">
		</a>
		<a href='events_new.php?eventid=".$event['id']."'>
			<img class='tipTop' title='Edit' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>
		<a  href='events.php?delete=".$event['id']."' class=\"deleteevent\" rel=\"".$event['id']."\">
			<img class='tipTop' title='Delete' src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";
	
	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
}


function writeCategory($catVal, $categories)
{
	$return = '';

	foreach ($catVal as $cat)
	{	
		if (array_key_exists($cat, $categories) )
		$return .= "{$categories[$cat]['title']} <br />";
	}
	return $return;
}


function categoryArray($catWord)
{
	$cleancate = explode(".", $catWord);
	$return = array();
	if ($catWord != '')
	{
		foreach ($cleancate as $cat)
		{	
			$return[] = $cat;
		}
	}
	return $return;
}


function urlfriendly($mess)
{
	$x = ereg_replace("[^A-Za-z0-9_-]", "_", $mess);
	while(strpos($x, "__")){
		$clean = ereg_replace("__", "_", $x);
	}
	return $clean;

}


function errors()
{
	global $error;
	if(isset($error)){
		echo "<div class=\"error\">".$error."</div>";
	}
	if(isset($_GET['wrongfiletype'])){
		echo "<div class=\"error\">That file type is not supported</div>";
	}
	
	if(isset($_SESSION['SESS_MSG'])){
		echo "<div class=\"error\">".$_SESSION['SESS_MSG']."</div>";
		unset($_SESSION['SESS_MSG']);
	}
	
	if(isset($_SESSION['SESS_MSG_green'])){
		echo "<div class=\"success\">".$_SESSION['SESS_MSG_green']."</div>";
		unset($_SESSION['SESS_MSG_green']);
	}
}

?>
