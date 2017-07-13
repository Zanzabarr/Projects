<?php 
include('inc.php');

$testimonials = getTestimonials();

//----------------------------begin html----------------------------------------------

$testimonialTitle = isset($homePageData['title'])?$homePageData['title']:'';
echo "<h1 id='testimonial-title'>{$testimonialTitle}</h1>";
?>

<div id='testimonials-body'>
<?php
// Show the Testimonial's homepage description if it is enabled
if (isset($homePageData['desc']) && $homePageData['status'])
{
	echo htmlspecialchars_decode($homePageData['desc']);
}

    foreach($testimonials as $testimonial) { ?>
        <div style="margin-top:1.5em;">
            <div id="full-test"><?php echo htmlspecialchars_decode($testimonial['full_test']); ?></div>
            
            <p id="test-info">- <?php echo $testimonial['name']; ?><?php echo !empty($testimonial['business']) ? ', ' . $testimonial['business'] : ''; ?>
            </p> 
        </div><!-- testimonial -->     
    <?php  
    } 
    ?>    	    
</div><!-- end testimonials-body -->
