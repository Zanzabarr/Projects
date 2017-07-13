$(document).ready(function() {

	$('.news_item_content').hide();
	
	if(window.location.hash) {
		var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character

		// find the matching news_item!
		scrollTonews_item(hash);

	} 
	
	// full story button handler (only for use on the news_items page(use it as a randomizer)
	$('.full_story_button').click(function(){
		var hash = $(this).attr('href').split('#');
		//hash = hash.split('#').elem(1)
		scrollTonews_item(hash[1]);
	});
	
	$('.news_itemToggler').click( function(){
		$(this).nextAll('.news_item_content:first').slideToggle('slow');
		toggleToggler( $(this) );
	
	} );
	
	function scrollTonews_item(strHash){
		$('.news_item_title').each(function(index, element){
			
			var strTitle = $(this).html();
			if(strHash == strTitle) {
				$(this).nextAll('.news_item_content:first').toggle();
				document.getElementById(strHash).scrollIntoView(true);
				$(this).nextAll('.news_itemToggler:first').each(function(){toggleToggler( $(this) )} );
				return false;
			}
			
		});
	}
	
	function toggleToggler($toggler)
	{
		// also toggle toggler values
		var tmpHtml = $toggler.html();
		$toggler.html($toggler.attr('rel'));
		$toggler.attr('rel', tmpHtml);
	}
	

});
