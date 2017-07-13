<?php
if($hasInlineEditable) :


if(!isset($_SESSION['inline_editable'])) $inline_closed_class = ' ';
elseif($_SESSION['inline_editable']) $inline_closed_class = ' ' ;
else $inline_closed_class = 'inline_closed';
 
$title_text = $inline_closed_class == 'inline_closed' ? "Enable Editing" : "Disable Editing" ;
?>
<style type="text/css">

.tinyNoEditTagged br[data-mce-bogus] {
	display: none;
}

.mce-edit-focus .tinyNoEditTagged{
	position:relative;
}

.mce-content-body .tinyNoEditTagged:after{
    bottom: 0;
    left: 0;
    opacity: 0.9;
    padding: 1em;
    position: absolute;
    right: 0;
    text-align: center;
    top: 0;
    z-index: 10000;
	cursor:not-allowed;
}
.mce-edit-focus .tinyNoEditTagged:after{
	background: none repeat scroll 0 0 #808080;
	content: "Not Editable";
	font-size: 34pt;
    font-weight: bold;
}

#inline_edit {
	position:fixed;
	bottom:0;
	right:0;
    max-width: 23em;
    min-width: 5em;
    height: 1.4em;
    padding: 0.2em 0.8em;
	z-index:10000;
	color: #3B5925;
	font-size: 14px;
	font-family: Tahoma;
	border: 1px solid #FFFFFF;
	border-bottom:none;
	border-right:none;
	background: #F8941D;
	background : -webkit-linear-gradient(top, #F8941D, #FA6723);
	background : -moz-linear-gradient(top, #F8941D, #FA6723);
	background : -o-linear-gradient(top, #F8941D, #FA6723);
	box-shadow: 1px -4px 13px 0px #7D7D7D;
	-webkit-box-shadow: 1px -4px 13px 0px #7D7D7D;
	-moz-box-shadow: 1px -4px 13px 0px #7D7D7D;
	-webkit-transition-property: top, bottom;
	-webkit-transition-duration: .5s;
	transition: top .2s ease-out .2s, left .5s ease-out .2s, right .5s ease-out .2s, bottom .5s ease-out .2s;
}
#inline_edit.inline_closed {
    bottom: -3.2em;
    right: 0;
    width: 5em;
}
#inline_edit.inline_closed:hover {
	bottom:-1.9em;
} 
#inline_edit_tab{
	color: #3B5925;
	border: 1px solid #FFFFFF;
	border-bottom:none;
	padding:4px 10px 2px;
	background: #F8941D;
	background : -webkit-linear-gradient(top, #F8941D, #FA6723);
	background : -moz-linear-gradient(top, #F8941D, #FA6723);
	background : -o-linear-gradient(top, #F8941D, #FA6723);
	border-radius: 5px 5px 0px 0px;
	box-shadow: 1px -4px 13px 0px #7D7D7D;
	-webkit-box-shadow: 1px -4px 13px 0px #7D7D7D;
	-moz-box-shadow: 1px -4px 13px 0px #7D7D7D;
	font-size: 8pt;
    position: absolute;
    left: -1px;
    text-align: center;
    top: -2.7em;
	cursor:pointer;

} 
#enable_inline, #disable_inline {
	display:none;
	position:absolute;
	left:-4px;
	top:-4px;
}
#disable_inline {
	display:block;
}
#inline_edit.inline_closed #enable_inline {
	display:block;
}
#inline_edit.inline_closed #disable_inline {
	display:none;
}
</style>
<div id='inline_edit' class="<?php echo $inline_closed_class; ?>">
	<div id='inline_edit_tab' title="<?php echo $title_text;?>">
		<span>ADMIN EDIT</span>
		<img id="enable_inline" src="admin/images/button-maximize.png">
		<img id="disable_inline" src="admin/images/button-minimize.png">
		<form id="inline_edit_form"  name="inline_edit_form" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
			<input type="hidden" name="inline_editable" value="<?php echo  $inline_closed_class === ' '  ? 'close' : 'open'; ?>">
		</form>
	</div>
	<span id='inline_edit_message'>Inline Editing Enabled</span>
</div>
<script type="text/javascript">
$(document).ready(function () {

	$(window).bind('beforeunload', function(){
		var tinyIsDirty = false;
		for (var i=0; i<tinymce.editors.length && !tinyIsDirty; i++) {
			if(tinymce.editors[i].isDirty()) tinyIsDirty = true;
		}
		if (tinyIsDirty)
			return 'You have unsaved changes to the page. Are you sure you want to leave? (All changes will be lost)';
		//return 'You have unsaved changes to the page. Are you sure you want to leave? (All changes will be lost)';
	});
	
	$('#inline_edit_tab').click(function(e){
		$("#inline_edit_form").submit();
	});
});	
</script>
<?php
endif;