<?php 
include('includes/headerClass.php');

$pageInit = new headerClass();
$pageInit->createPageTop();

$infoquery=logged_query_assoc_array("SELECT * FROM auth_users WHERE user_id='{$_SESSION['uid']}'");
$info=$infoquery[0];
?>
<div class="page_container">
	<div id="h1"><h1>Administrative Summary</h1></div>
    
    <div id="info_container">
    	<div class="info">
        	<h4>Information :: Admin</h4>
            <ul>
            	<?php if ($info['first_name']) { ?>
            	<li>First Name: <b><?php echo $info['first_name']; ?></b></li>
                <?php } if ($info['last_name']) { ?>
                <li>Last Name: <b><?php echo $info['last_name']; ?></b></li>
                <?php } if ($info['company_name']) { ?>
                <li>Company Name: <b><?php echo $info['company_name']; ?></b></li>
                <?php } if ($info['office_address']) { ?>
                <li>Office Address: <b><?php echo $info['office_address']; ?></b></li>
                <?php } if ($info['office_city']) { ?>
                <li>Office City: <b><?php echo $info['office_city']; ?></b></li>
                <?php } if ($info['office_postal']) { ?>
                <li>Office Postal Code: <b><?php echo $info['office_postal']; ?></b></li>
                <?php } if ($info['office_number']) { ?>
                <li>Office Number: <b><?php echo $info['office_number']; ?></b></li>
                <?php } if ($info['fax_number']) { ?>
                <li>Fax Number: <b><?php echo $info['fax_number']; ?></b></li>
                <?php } if ($info['cell_number']) { ?>
                <li>Cell Phone: <b><?php echo $info['cell_number']; ?></b></li>
                <?php } if ($info['username']) { ?>
                <li>Username: <b><?php echo $info['username']; ?></b></li>
                <?php } if ($info['email']) { ?>
                <li>Email: <b><?php echo $info['email']; ?></b></li>
                <?php } ?>
            </ul>
        </div>
        <div class="info" style="float:right">
        	<h4>Information :: Version</h4>
            <ul>
            	<li>Version: <strong>2.1.2</strong></li>
            </ul>
        </div>
		
		<?php

/*
$imr = new imageresizer();
$imr->load($_config['admin_path'] . 'testfiles/Arviat27m.png');
$imr->resizeToWidth(100);
$imr->save($_config['admin_path'] . 'testfiles/resized.png')
*/
phpinfo();
?>
	<img src="testfiles/resized.png">
    </div>
</div>

<?php include("includes/footer.php"); ?>
