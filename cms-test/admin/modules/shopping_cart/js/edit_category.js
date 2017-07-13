

$(document).ready(function(){

//console.log(usedSlugs);
// handle form submissions
$('#submit-btn').click(function(e){
	var noTitle = $("#name").val().trim()==''
	var noUrl =  $("#url").val().trim()=='';
	
	if( noTitle || noUrl || $('#url').hasClass('badUrl') )
	{
		openBanner('error', 'Minimum Requirements', 'at minimum, Title and Url fields must be completed and valid');
		e.preventDefault();
		if ( noTitle )$('#err_name').addClass('errorMsg').html('Required field');
		if ( noUrl ) $('#err_url').attr('class','errorMsg').html('Required field');
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


//validate the url, can't be in the array: usedSlugs (passed from php) unless it is the current page's slug name
$('#url').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $(this) ) });
// write title to url (converting to good slug) and provide validation feedback
$('#name').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $('#url') ) });


});