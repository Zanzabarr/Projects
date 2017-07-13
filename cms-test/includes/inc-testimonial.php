<?php
$testy = get_random_testimonial();
if ($testy)
{
    $testimony = "<em>".htmlspecialchars_decode($testy['full_test'])."</em>";
    if ($testy['name']) {$name = htmlspecialchars_decode($testy['name']);}
    if ($testy['business']) {$business = htmlspecialchars_decode($testy['business']);}
    
    echo "<div id='testimonial'>
    {$testimony}<div class=\"testimonial-name\">{$name}, {$business}<br />";
	
	echo '<p style="margin-top:1em;"><a href="testimonials">more testimonials >></a></p></div></div>';

}
?>
