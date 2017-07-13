$(document).ready(function(){
	
	// ajax delete member
	$(".deletemember").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this member?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletemember.php",
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
