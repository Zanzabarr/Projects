<?php
$testy = get_random_testimonial();
if ($testy)
{
    $testimony = htmlspecialchars_decode($testy['full_test']);
    if ($testy['name']) {$name = htmlspecialchars_decode($testy['name']);}
    if ($testy['business']) {$business = htmlspecialchars_decode($testy['business']);}
    
    echo "<div id='testimonial' class='testimonial'>
    {$testimony}<div class=\"testimonial-name\">{$name}<br />{$business}</div>
	
    </div>";

}
?>
