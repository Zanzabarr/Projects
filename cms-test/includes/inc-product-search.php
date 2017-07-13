
<div id="formSearchContainer">
<?php
	if(isset($_SERVER['HTTP_REFERER'])) {
		echo "<span class='backbutton'>&lt;&lt;&nbsp;<a href='{$_SERVER['HTTP_REFERER']}'>Back to previous page</a></span>";
	}
?>
<form name="frmSearch" id="frmSearch" action="shopping" method="post">
	<input type="hidden" name="prevpage" value="#" />
	<input type="text" name="search" placeholder="SEARCH" /><input type="submit" id="searchsubmit" name="searchsubmit" value="" />
</form>
</div><!--form container-->