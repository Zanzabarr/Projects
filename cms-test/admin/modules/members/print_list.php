<?php 
include_once("../../includes/config.php");
include_once("includes/functions.php");

// validate user: kick to login if not logged in, otherwise set curUser data
$curUser = check_login();

// get members data
$members =  getMembersData();

// get unpaid new signups members data
$members_unpaid_signups = array();
$members_bulletin_only = array();

foreach ($members as $member_key => $member)
{
	if($member['eBulletin'] == 2)
	{
		$members_bulletin_only[] = $member;
		unset($members[$member_key]);
	}
	elseif($member['unpaid_signup'] == 1)
	{
		$members_unpaid_signups[] = $member;
		unset($members[$member_key]);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<style>
body {font-family: Arial,Helvetica,sans-serif;padding:20px;	}
table {
	
}
td {
    font-size: 12px;
    line-height: 16px;
	padding: 10px;
}
.centered {
	text-align:center;
}

</style>
</head>
<body>

<div>
	<h3>Members</h3>
<?php 
if (count($members) > 0 ) echo build_member_table($members);
else echo "<p>There are no members yet.</p>";
?>
</div>
<div>
	<h3>Unpaid New Signups</h3>
<?php 
if (count($members_unpaid_signups) > 0 ) echo build_member_table($members_unpaid_signups);
else echo "<p>There are no newly signed-up members who have yet to pay.</p>";
?>
</div>
<div>
	<h3>Non-Member eBulletin Subsribers</h3>
<?php 
if (count($members_bulletin_only) > 0 ) echo build_member_table($members_bulletin_only);
else echo "<p>There are no non-member eBulletin subscribers.</p>";
?>
</div>



</body>
</html>
<?php  

function build_member_table($members)
{
	ob_start();
	?>
	<table border="1">	
		<tr>
			<th>Surname</th>
			<th>First Name</th>
			<th>Mailing Address</th>
			<th>City</th>
			<th>Province</th>
			<th>Country</th>
			<th>Postal Code</th>
			<th>Email</th>
			<th>Phone Number</th>
			<th>Status</th>
			<th>Member Level</th>
			<th>Payment Status</th>
			<th>Expiry Date</th>
			<th>e-Bulletin</th>
		</tr>
	
	<?php	
	foreach($members as $row)
	{ 
		$status = $row['status'];
		if($status == 1) $pretty_status = 'New';
		elseif($status == 2) $pretty_status = 'Regular';
		elseif($status == 3) $pretty_status = 'Complimentary';
		elseif($status == 4) $pretty_status = 'Honorary';
		else $pretty_status = 'Sponsored';
		
	?>
		<tr>
			<td><?php echo $row['last_name']; ?></td>
			<td><?php echo $row['first_name']; ?></td>
			<td><?php echo $row['mailing_address']; ?></td>
			<td><?php echo $row['city']; ?></td>
			<td><?php echo $row['province_state_region']; ?></td>
			<td><?php echo $row['country']; ?></td>
			<td><?php echo $row['postal_code']; ?></td>
			<td><?php echo $row['email']; ?></td>
			<td><?php echo $row['phone_number']; ?></td>
			<td class="centered"><?php echo $pretty_status; ?></td>
			<td class="centered"><?php echo $row['level'] ? 'Seed Donor' : 'Standard'; ?></td>
			<td class="centered"><?php echo $row['payment_status'] ? "Paid" : "Unpaid"; ?></td>
			<td class="centered"><?php echo date('F d, Y', strtotime($row['membership_expiry'])); ?></td>
			<td class="centered"><?php echo $row['eBulletin'] ? 'Yes' : 'No'; ?></td>
			
		</tr>
	<?php	}	?>
	</table>
	<?php
	
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
	
}