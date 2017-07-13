<div id="nl-nav">
    <ul>
        <li <?php if($pg == 'main'){echo " class=\"active\"";} ?>><a href="newsletter.php">Main</a></li>
		<li <?php if($pg == 'settings'){echo " class='active'";} ?>><a href="settings.php">Settings</a></li>
        <li <?php if($pg == 'subs'){echo " class=\"active\"";} ?>><a href="subs.php">Subscribers</a></li>
    </ul>
	<div id='navbtn'>
	<?php 
	switch($pg)
	{
		case "main" : 
			echo "<a class='blue button tipTop' title='Create a new Newsletter.' href='newsletter_edit.php?option=create'>Create Newsletter</a>";
			break;
	}
	?>
		<div class='clearFix'></div>
	</div>
</div>
