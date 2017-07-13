<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'testimonials';
include('../../includes/headerClass.php');
include('inc/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get surveys data
$testimonials = getTestimonialsData();

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/testimonials/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/testimonials/js/testimonials.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Testimonials</h1></div>
    <div id="info_container" class='testimonialsTable'>

		<?php 
		$selectedTestimonials = 'tabSel';
        
        echo '<div id="testimonialsnavhome">'; 
        
		include("inc/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		 <?php 
		errors(); 
        if (count($testimonials) > 0 ) :
		?>
			
		<div id='testimonials-list'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="175">NAME</th>
					<th width="200">BUSINESS</th>
					<th width="250">FULL TESTIMONIAL</th>
					<th width="125">OPERATION</th>
				</tr>
			</table>

			<?php 
			echo buildTestimonialsMenu($testimonials); 
		?>
		</div> <!--END testimonials-list -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no testimonials yet. Why not click "Add Testimonial" to add your first one?</p>
		<?php endif; ?>

	</div> <!-- end info_container -->
</div>
<?php 
include($_config['admin_includes']."footer.php"); 
?>
