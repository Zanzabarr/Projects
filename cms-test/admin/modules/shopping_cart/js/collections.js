$(document).ready(function(){

// handle delete collection button
$('.deleteCollection').click(function(e) {
	e.preventDefault();
	var id = $(this).attr("rel"),
		that = this;
	var colurl = $(this).attr("data-url");
		
	confirm('Permanently remove this Collection? Related pages will be made draft.', function() { 
		$.ajax({
			type: "POST",
			url: "ajax/ajax.php",
			data:{ action: "delete_collection", id: id, url: colurl },
			cache: false,
			success: function(result){
				if(result == 'success') {
					$(that).parents('.menu_row').slideUp('slow');    
					// write success
					openBanner('success','Collection Deleted' , '')
				} else {
					openBanner('error','Error Deleting Collection' , 'collection not found')
				}

			}
		});
	});
});



});