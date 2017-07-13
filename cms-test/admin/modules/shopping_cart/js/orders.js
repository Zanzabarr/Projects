$(document).ready( function() {
	$(".orderlink").fancybox({
		'autoDimensions': false,
		'width': '80%',
		'height': '100%',
		'overlayColor': '#000',
		'overlayOpacity': 0.6,
		'transitionIn': 'elastic',
		'transitionOut': 'elastic',
		'centerOnScroll': true,
		'titlePosition': 'outside',
		'easingIn': 'easeOutBack',
		'easingOut': 'easeInBack'
	});

	// handle archive order button
	$('.archiveOrder').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
			
		confirm('Archive this Order?', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "archive_order", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') { 
						$('#tblOrderPage .op_group a').each( function() {
							if($(this).attr('rel')==id) $(this).parent().parent().parent().remove();
						});
						// write success
						openBanner('success','Order Archived' , '')
						//setTimeout('location.reload()',3000);
					} else {
						openBanner('error','Error Archiving Order' , 'order not found')
					}
				}
			});
		});
	});
	
	// handle change Confirm button
	$('.changeConfirm').click(function(e) {
		e.preventDefault();

		var id = $(this).attr("rel"),
			$imgNode = $(this).children()
			changeTo = $imgNode.attr('rel'),
			changeFrom = changeTo == 1 ? 0 : 1,
			newIconSrc = changeTo == 1 ? 'img/icon-star.png' : 'img/icon-darkstar.png',
			newTipTitle = changeTo == 1 ? 'Unconfirm Order / Not Paid' : 'Confirm Order / Paid',
			newAlt = changeTo == 1 ?  'Confirmed' : 'Not Confirmed';

			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "change_confirm", id: id, change_to : changeTo },
				cache: false,
				success: function(result){
					if(result == 'success') {
						$imgNode.attr('rel', changeFrom).attr('src', newIconSrc).attr('alt', newAlt).attr('title', newTipTitle);
						$('.tipTop').tipTip({defaultPosition:"top"});
					} else {
						openBanner('error','Error changing Confirmation' , result);
					}
				}
			});
	});

	// handle Shipped button
	$('.changeShipped').click(function(e) {
		e.preventDefault();

		var id = $(this).attr("rel"),
			$imgNode = $(this).children()
			changeTo = $imgNode.attr('rel'),
			changeFrom = changeTo == 1 ? 0 : 1,
			newIconSrc = changeTo == 1 ? 'img/shipped.png' : 'img/unshipped.png',
			newTipTitle = changeTo == 1 ? 'Mark Order Not Shipped' : 'Mark Order Shipped',
			newAlt = changeTo == 1? 'Shipped' : 'Not Shipped';
			
			confirm('Mark this order '+newAlt+'?', function() { 
				$.ajax({
					type: "POST",
					url: "ajax/ajax.php",
					data:{ action: "change_shipped", id: id, change_to : changeTo },
					cache: false,
					success: function(result){
						if(result == 'success') {
							$imgNode.attr('rel', changeFrom).attr('src', newIconSrc).attr('alt', newAlt).attr('title', newTipTitle);
							$('.tipTop').tipTip({defaultPosition:"top"});
						} else {
							openBanner('error','Error changing Order Shipped' , result);
						}
					}
				});
			});
	});
	
	// handle delete order button
	$('.deleteOrder').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
		confirm('Permanently Remove this Order?&nbsp;&nbsp;<em>This <u>cannot</u> be undone!</em>', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "delete_order", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') { 
						$('#tblOrderPage .op_group a').each( function() {
							if($(this).attr('rel')==id) $(this).parent().parent().parent().remove();
						});
						// write success
						openBanner('success','Order Deleted' , '')
						//setTimeout('location.reload()',3000);
					} else {
						openBanner('error','Error Removing Order' , 'order not found')
					}
				}
			});
		});
	});
    // datepicker calendar anchoring
    $("#start_date").datepicker({ yearRange: "2013:+1" });
    $("#end_date").datepicker({ yearRange: "2013:+1" });
});