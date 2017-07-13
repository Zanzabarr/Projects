$(document).ready(function(){

	// handle delete page button
	$('.deleteProduct').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
			
		confirm('Permanently remove this Product?', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "delete_product", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') {
						$(that).parents('.menu_row').slideUp('slow');    
						// write success
						openBanner('success','Product Deleted' , '')
					} else {
						openBanner('error','Error Deleting Product' , 'product not found')
					}
				}
			});
		});
	});


	// handle change Featured button
	$('.changeFeatured').click(function(e) {
		e.preventDefault();

		var id = $(this).attr("rel"),
			$imgNode = $(this).children()
			changeTo = $imgNode.attr('rel'),
			changeFrom = changeTo == 1 ? 0 : 1,
			newIconSrc = changeTo == 1 ? 'img/icon-star.png' : 'img/icon-darkstar.png',
			newTipTitle = changeTo == 1 ? 'Make Product No Longer Featured' : 'Make Featured Product',
			newAlt = changeTo == 1 ?  'Not Featured' : 'Feature';

			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "change_featured", id: id, change_to : changeTo },
				cache: false,
				success: function(result){
					if(result == 'success') {
						//$(that).parents('.menu_row').slideUp('slow');    
						// write success
						//openBanner('success','Product Changed' , '');
						$imgNode.attr('rel', changeFrom).attr('src', newIconSrc).attr('alt', newAlt).attr('title', newTipTitle);
						$('.tipTop').tipTip({defaultPosition:"top"});
					} else {
						openBanner('error','Error changing Featured Product' , result);
					}
				}
			});
	});

	// handle Activate button
	$('.changeActive').click(function(e) {
		e.preventDefault();

		var id = $(this).attr("rel"),
			$imgNode = $(this).children()
			changeTo = $imgNode.attr('rel'),
			changeFrom = changeTo == 1 ? 0 : 1,
			newIconSrc = changeTo == 1 ? 'img/check.png' : 'img/dislike.png',
			newTipTitle = changeTo == 1 ? 'Make Product Inactive' : 'Make Product Active',
			newAlt = changeTo == 1? 'Not Active' : 'Active';

			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "change_active", id: id, change_to : changeTo },
				cache: false,
				success: function(result){
					if(result == 'success') {
						$imgNode.attr('rel', changeFrom).attr('src', newIconSrc).attr('alt', newAlt).attr('title', newTipTitle);
						$('.tipTop').tipTip({defaultPosition:"top"});
					} else {
						openBanner('error','Error changing Active Product' , result);
					}
				}
			});
	});
	
	$('#view_category').change( function() {
		$('#view_collection').val('-1');
		$("#viewform").submit();
	});
	$('#view_collection').change( function() {
		$('#view_category').val('-1');
		$("#viewform").submit();
	});

});