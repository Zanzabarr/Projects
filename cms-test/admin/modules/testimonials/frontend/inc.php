<?php
/******************* FUNCTIONS *************************/
function getTestimonials() {
    $testimonials = logged_query_assoc_array("SELECT * FROM `testimonials` WHERE `id` > 0 AND `status` = 1 ORDER BY `date` DESC",null,0,array());
    
    return $testimonials;
}

?>
