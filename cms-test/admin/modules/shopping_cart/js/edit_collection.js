$(document).ready(function() {

$('#submit-btn').click(function(e){
	var noTitle = $("#title").val().trim()==''
	var noUrl =  $("#url").val().trim()=='';
	
	if( noTitle || noUrl || $('#url').hasClass('badUrl') )
	{
		openBanner('error', 'Minimum Requirements', 'at minimum, Title and Url fields must be completed and valid');
		e.preventDefault();
		if ( noTitle )$('#err_title').addClass('errorMsg').html('Required field');
		if ( noUrl ) $('#err_url').attr('class','errorMsg').html('Required field');
		$('#err_url').show()
		$('#prop-toggle-wrap').slideDown('slow');
		$('#prop-toggle-wrap').scrollView();
		return false;
	}
	$('#form_data').submit();
});

	//validate the url, can't be in the array: usedSlugs (passed from php) unless it is the current page's slug name
	$('#url').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $(this) ) });
	// write title to url (converting to good slug) and provide validation feedback
	$('#title').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $('#url') ) });	

});