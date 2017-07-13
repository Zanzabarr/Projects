<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'newsletter';
include('../../includes/headerClass.php');
include ($_config['admin_includes'].'html2text.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];
$pg = "main";

//get the data
$newsletters = logged_query("SELECT * FROM newsletter ORDER BY date_created DESC",0,array());
// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/newsletter/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/newsletter/js/newsletter.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>
<div class="page_container">
	<div id="h1"><h1>Newsletter</h1></div>
    <div id="info_container">

		<?php 
        
        echo '<div>'; 
        
		include("includes/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		 <?php 
        if (count($newsletters) > 0 ) :

		?>

		<div id='newsletter-list'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa; table-layout:fixed; width:600px;font-size:14px;">
				<tr>
					<th>SUBJECT</th>
					<th>STATUS</th>
					<th>DATE CREATED</th>
					<th>LAST UPDATED</th>
					<th>OPERATION</th>
				</tr>
            <?php echo buildNewsletters_Menu($newsletters); ?>
			</table>

		</div> <!--end news_items-list -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no Newsletters yet. Why not click "Create Newsletter" to make your first one?</p>
		<?php endif; ?>

	</div> <!--end infoContainer -->
</div>
<?php 
include($_config['admin_includes']."footer.php"); 
?>