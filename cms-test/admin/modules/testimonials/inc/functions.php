<?php

function getTestimonialsData()
{
    global $_config;

	//create testimonials array
	$testimonials = array();
    $tmptestimonials = logged_query("SELECT * FROM `testimonials` WHERE `id` > 0 ORDER BY `date` DESC",0,array());
	foreach ($tmptestimonials as $testimonial){
		// convert testimonials data to useable format
		$testimonial['status_word'] = $testimonial['status'] == 0 ? 'Inactive' : 'Active';
		
		// update the testimonials array
		$testimonials[$testimonial['id']] = $testimonial;
	}	

	return $testimonials;
}


function buildTestimonialsMenu($testimonials)
{
	$menu = "<div class ='testimonials_grp' >";
	foreach($testimonials as $row)
	{
		$menu .= "<div class ='testimonials_row' >"."\n";
		$menu .=	"<div class ='menu_row'>"."\n";
		$menu .= 		buildMenuRow($row)."\n";
		$menu .=	"</div>"."\n";
		$menu .= "</div>"."\n";// end of testimonials_row
	}	
	$menu .= "</div>";// end of testimonials_grp
	return $menu;
}


function buildMenuRow($testimonial)
{	
	global $_config;
	$menu ="<span class='row_name'>".$testimonial['name']."</span>"."\n";
	$menu .= "<span class='row_business'>".$testimonial['business']."</span>"."\n";
	$menu .= "<span class='row_full_test'>".htmlspecialchars_decode($testimonial['full_test'])."</span>"."\n";
		
	$menu .="<span class='op_group'>
        <a>";
    if($testimonial['status'] == 0) { 
        $menu .= "<img id=".$testimonial['id']." class='tipTop active' title='Set the status for the testimonial.' ref='off' src='images/dislike.png' alt='Status'>";
    }
    else {
        // testimonial['status'] == 1
        $menu .= "<img id=".$testimonial['id']." class='tipTop active' title='Set the status for the testimonial.' ref='on' src='images/check.png' alt='Status'>";
    }

    $menu .= "</a>
		<a href='testimonials_new.php?testimonialid=".$testimonial['id']."'>
			<img class='tipTop' title='Edit' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>
		<a  href='testimonials.php?delete=".$testimonial['id']."' class=\"deletetestimonial\" rel=\"".$testimonial['id']."\">
			<img class='tipTop' title='Delete' src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";
	
	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
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
