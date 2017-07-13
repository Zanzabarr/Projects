<?php
/*
 	THIS IS THE NEW FULL SEARCH PLUGIN FRONTEND FILE - BEH 
	INCLUDING THIS FILE REQUIRES SITE TO HAVE THE FILE INCLUDES/CUSTOM_PAGES/SEARCH.PHP AS WELL AS A PAGE CREATED IN ADMIN WITH THE NAME AND URL OF "Search" AND "search" - IT CAN BE MENULESS BUT CAN ALSO BE ON THE MENU IF YOU WISH.
*/
?>

<div id="siteSearchContainer" class="equalheight">
	
	<form name="frmSiteSearch" id="frmSiteSearch" action="<?php echo $_config['site_path']; ?>search" method="get">
	
		<input class="equalheight" type="text" id="sitesearchTerms" name="q" placeholder="Search" 
		
		/><div id="frmSiteSearchButtons"
		
			><span class="formimg" title="Search"><img type="submit" src="<?php echo $_config['site_path']; ?>iconbar/icons/search.png" /></span
		
			><span class="formimg" title="Help"><img class="helpOpen" src="<?php echo $_config['site_path']; ?>iconbar/icons/question.png" /></span
			
		></div>
		
	</form>

	<div class="searchHelp">
		<strong>Search Help</strong><br />
		<dl id="helpList">
			<dt>Any Match</dt>
			<dd>Multiple words entered will return results for any matches of any of the words. ie: red car - will return matches for red car, red, and car.</dd>
			<dt>Exact Match</dt>
			<dd>Use double quotation marks (") around search terms and multiple words to search for an exact phrase match. ie: "red car" - Only "red car" matches will be returned (not red or car).</dd>
			<dt>Partial Word</dt>
			<dd>Use the asterisk (*) to create a wildcard at the end of a search term if your word is incomplete. ie: comp* - will return matches for complex, computer, and any word beginning with the letters "comp".</dd>
			<dt>
		</dl>
		
		<img class="helpClose" src="iconbar/icons/x-close-black.png">
	</div>
	
</div><!--form container-->

<div style="clear:both;"></div>

<script>
$(document).ready( function() {
	$(".helpOpen, .helpClose").click( function() {
		$(".searchHelp").toggle();
	});
});
</script>
