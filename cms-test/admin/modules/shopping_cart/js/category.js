$(document).ready(function(){

// handle delete category button
$('.deleteCategory').click(function(e) {
	e.preventDefault();
	var newHref = $(this).attr("href");
		
	confirm('<p>Permanently remove this Category?</p><p>Any descendant categories will be moved up a level.</p><p>Any related page will be made draft.<br>No product data will be lost.</p>', function() { 
		window.location.href = newHref;
	});
	
});

});
  


