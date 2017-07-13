$(document).ready(function(){
	
	// ajax delete category
	$(".deletecategory").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this category?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletecategory.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	$(that).parents('.menu_row').slideUp('slow'); 
					
				}
			});
		})
	});
		
	$(".active").hover(function() {
	  $(this).css('cursor','pointer');
	});

});
