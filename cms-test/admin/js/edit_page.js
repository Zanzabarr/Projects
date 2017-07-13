$(document).ready(function(){

// trigger warning on unsaved page exits if inputs have been changed
bindNoDirtyPageExit('#form-page',['content'],['#submit-btn', '#cancel-btn']);

// handle delete page button
$('#delete-btn').click(function(e) {
	e.preventDefault();
	confirm('Permanently remove this page?', function() { $('#delForm').submit(); } );
});

// handle form submissions
$('#submit-btn').live('click',function(e){
	var noTitle = $("#page_title").val().trim()==''
	var noUrl =  $("#slug").val().trim()=='';
	
	if( noTitle || ((typeof $("#special_page") === "undefined" || $("#special_page").val() == 0 ) && (noUrl ||  $('#slug').hasClass('badUrl')) ))
	{
		openBanner('error', 'Minimum Requirements', 'at minimum, Title and Url fields must be completed and valid');
		e.preventDefault();
		if ( noTitle )$('#err_page_title').addClass('errorMsg').html('Required field');
		if ( noUrl ) $('#err_slug').attr('class','errorMsg').html('Required field');
		$('#err_url').show()
		$('#prop-toggle-wrap').slideDown('slow');
		$('#prop-toggle-wrap').scrollView();
		return false;
	}
	$('#form-page').submit();
});

// -----------------------------------  hide/show sections  ------------------------------------------------
if ( $('#version-toggle-wrap').find('.errorMsg, .successMsg, .warningMsg').length < 1)
{
	$('#version-toggle-wrap').hide();
}

// hide/show messages

// hide error messages on click
$('span.errorMsg, span.warningMsg, span.successMsg').live('click', function(){$(this).fadeOut();});

// display if message exists
$('.success, .error, .warning').parent().fadeIn(2000);
// close message
$('.close-message').click(function(){
	$(this).hide(810);
	$parent = $(this).parent();
	$parent.animate({opacity: .15 }, 300, function(){$parent.hide(500)});
});

// control display of menu name field
// if this is new page, hide menu name to start
if ($('#menuPosn')) { $('#menuNameWrap').hide();}

// if anything other than no menu is selected, show the menu name field
$('#menuPosn').change(function(e){
	if ( $(this).val() == -1 ) {
		$('#menuNameWrap').slideUp();
		$('#menu_name').val('No Menu');
	} else {
		if ( $('#menu_name').val() == 'No Menu') $('#menu_name').val('');
		$('#menuNameWrap').slideDown();		
	}
});



if ( $('#special_page').val() != 0 ) $('#slug').closest('.input_wrap').hide();

// hide content for special module pages
var tmpselected = $("#special_page option:selected").attr('data-type');
if(tmpselected == 'mod_special') $("#not_module_wrap").hide();
else if(tmpselected == "secondary") $('#seo_area_wrap').hide();

$('#special_page').change(function(e){
	var selected = $("option:selected", this).attr('data-type');
	
	if ( $(this).val() != 0 ) $('#slug').closest('.input_wrap').slideUp('slow');
	else $('#slug').closest('.input_wrap').slideDown('slow');

	// hide content for special module pages
	if(selected == 'mod_special')
	{
		// add hide wrap class
		$("#not_module_wrap").slideUp('slow');
	} else if (selected == "regular" || selected == "secondary")
	{
		$("#not_module_wrap").slideDown('slow');
		var newSlug = $('#slug').val() + '-';
		$('#slug').val(newSlug);
		curSlug = newSlug;
		
		if(selected == "secondary")
			$('#seo_area_wrap').slideUp('slow');
		
	} else 
	{
		$("#not_module_wrap").slideDown('slow');
		$('#seo_area_wrap').slideDown('slow');
	}
});

//validate the url, can't be in the array: usedSlugs (passed from php) unless it is the current page's slug name
$('#slug').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $(this) ) });
// write title to url (converting to good slug) and provide validation feedback
$('#page_title').keyup(function(e){changeSlug( curSlug, usedSlugs, $(this), $('#slug') ) });
});