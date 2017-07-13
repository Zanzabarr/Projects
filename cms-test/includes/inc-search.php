<?php /* THIS IS THE NEW FULL SEARCH PLUGIN FRONTEND FILE - BEH 
	INCLUDING THIS FILE REQUIRES SITE TO HAVE THE FILE INCLUDES/CUSTOM_PAGES/SEARCH.PHP AS WELL AS A PAGE CREATED IN ADMIN WITH THE NAME AND URL OF "Search" AND "search" - IT CAN BE MENULESS BUT CAN ALSO BE ON THE MENU IF YOU WISH.
*/
	
?>
<style>
.translateContainer {
	width:10%;
	margin-right:1em;
}
#siteSearchContainer {
	width:auto;
	padding:1em 1em 0 1em;
	background:none;
	float:right;
	left:0;
	position:relative;
}

span.helpClose {
	position:absolute;
	top:0;
	right:.5em;
	cursor:pointer;
	font-weight:600;
}
#frmSiteSearch {
    display:inline-block;
    margin-left:1em;
}
#frmSiteSearch button img {
	width:75%;
}

img.searchInfo {
	display:inline-block;
	vertical-align:middle;
	margin-left:5px;
}
section.searchHelp {
	display:none;
	position:absolute;
	left:-30%;
	width:300px;
	background:#fff;
	padding:10px;
	border:2px solid #000;
	border-radius:5px;
	-webkit-border-radius:5px;
	z-index:1500;
	text-align:left;
}
.searchHelp dt {
	margin-top:.5em;
	font-weight:400;
}
.clear {
	clear:both;
	height:1em;
}
@media screen and (min-width:550px) {
	section.searchHelp {
		left:-50%;
	}

	#siteSearchContainer {
		width:auto;
		padding:1em 1em 0 1em;
		background:none;
		float:right;
		left:0;
	}
}
</style>
<div id="siteSearchContainer">
<form name="frmSiteSearch" id="frmSiteSearch" action="<?php echo $_config['site_path']; ?>search" method="get">
	<input type="text" id="sitesearchTerms" name="q" placeholder="SEARCH" /><button type="submit" value=""><img src="<?php echo $_config['site_path']; ?>images/search.png" /></button><img class="searchInfo" src="<?php echo $_config['site_path']; ?>images/help.png" />
</form>

<section class="searchHelp">
	<strong>Search Help</strong><span class="helpClose"> x </span><br />
	<dl id="helpList">
		<dt>Any Match</dt>
		<dd>Multiple words entered will return results for any matches of any of the words. ie: red car - will return matches for red car, red, and car.</dd>
		<dt>Exact Match</dt>
		<dd>Use double quotation marks (") around search terms and multiple words to search for an exact phrase match. ie: "red car" - Only "red car" matches will be returned (not red or car).</dd>
		<dt>Partial Word</dt>
		<dd>Use the asterisk (*) to create a wildcard at the end of a search term if your word is incomplete. ie: comp* - will return matches for complex, computer, and any word beginning with the letters "comp".</dd>
		<dt>
	</dl>
</section>
</div><!--form container-->
<div class="clear"></div>
<script>
$(document).ready( function() {
	$("img.searchInfo").click( function() {
		$("section.searchHelp").toggle();
	});
	$("span.helpClose").click( function() {
		$("section.searchHelp").toggle();
	});
});
</script>