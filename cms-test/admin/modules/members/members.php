<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'members';
include('../../includes/headerClass.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get members data
$members =  getMembersData();

// get unpaid new signups members data
$members_unpaid_signups = array();
$members_bulletin_only = array();

foreach ($members as $member_key => $member)
{
	if(isset($member['eBulletin']) && $member['eBulletin'] == 2)
	{
		$members_bulletin_only[] = $member;
		unset($members[$member_key]);
	}
	elseif(isset($member['unpaid_signup']) && $member['unpaid_signup'] == 1)
	{
		$members_unpaid_signups[] = $member;
		unset($members[$member_key]);
	}
}

reset($members);

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/members.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Members</h1></div>
    <div id="info_container" class='membersTable'>

		<?php 
		$selectedOpts = '';
		$selectedMembers = 'tabSel';
		$selectedHome = '';
        
        echo '<div id="membersnavhome">'; 
        
		include("includes/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		 <?php 
		errors(); 
        if (count($members) > 0 ) :
		?>
			
		<div id='members-list'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
                    <th width="45">IMAGE</th>
					<th width="185">NAME</th>
					<th width="75">MEMBERSHIP STATUS</th>
					<th width="105">MEMBERSHIP EXPIRY</th>
					<th width="115">OPERATION</th>
				</tr>
			</table>

			<?php 
			echo buildMembersMenu($members); 
		?>
		</div> <!--end members-list -->
		
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no members yet. Why not click "Add Member" to add your first one?</p>
		<?php endif; ?>

	</div> <!--end infoContainer -->
</div>
<?php /*(<div id="info_container_unpaid_signups">        
    <h2 id="unpaid-signups-header">Unpaid New Signups</h2>
    
    <?php    
    if (count($members_unpaid_signups) > 0 ) :
	?>
    
    <div id='members-list-unpaid-signups'>
		<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
			<tr>
                <th width="45">IMAGE</th>
				<th width="185">EMAIL</th>
				<th width="75">MEMBERSHIP STATUS</th>
				<th width="105">MEMBERSHIP EXPIRY</th>
				<th width="115">OPERATION</th>
			</tr>
		</table>
        <?php 
		echo buildMembersMenu($members_unpaid_signups); 
		?>
	</div> <!--end members-list-unpaid-signups -->
		
    <?php else : ?>
	<p style='text-align:center;width:100%;padding-top:5px;'>There are no newly signed-up members who have yet to pay.</p>
	<?php endif; ?>
</div>

<div id="info_container_unpaid_signups">        
    <h2 id="unpaid-signups-header">Non-Member eBulletin Subsribers</h2>
    
    <?php    
    if (count($members_bulletin_only) > 0 ) :
	?>
    
    <div id='members-list-unpaid-signups'>
		<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
			<tr>
                <th width="45">IMAGE</th>
				<th width="185">EMAIL</th>
				<th width="75">MEMBERSHIP STATUS</th>
				<th width="105">MEMBERSHIP EXPIRY</th>
				<th width="115">OPERATION</th>
			</tr>
		</table>
        <?php 
		echo buildMembersMenu($members_bulletin_only,false); 
		?>
	</div> <!--end members-list-unpaid-signups -->
		
    <?php else : ?>
	<p style='text-align:center;width:100%;padding-top:5px;'>There are no non-member eBulletin subscribers.</p>
	<?php endif; ?>
</div>*/?>
<?php 
include($_config['admin_includes']."footer.php"); 
?>
