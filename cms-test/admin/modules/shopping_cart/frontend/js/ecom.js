$(window).load(function(){	

	// calculate initial prices
	calculate_price();
	
	// recalculate prices if quantity or options change:
	$('.product_options, #quantity').change(calculate_price);
	
	// submit form
	$('.addbutton').click( function(e) {
		e.preventDefault();
		$('#addtocart').submit();
		
	});
	
	// products page sort submit
	$('#filterbox').change( function() {
		$('#sortform').submit();
	});
	
	function calculate_price()
	{
		// find the current quantity
		var qty = $('#quantity').val(),
		// find the quantity base prices
			$price1 = $('#curPrice1'),
			base1 = $price1.data('base-price'),
			$price2 = $('#curPrice2'),
			base2 = $price2.data('base-price'),
			$price3 = $('#curPrice3'),
			base3 = $price3.data('base-price'),
			qtyMax1 = $price1.data('high'),
			qtyMax2 = $price2.data('high'),
			qtyMax3 = $price3.data('high'),
			qtyMin1 = $price1.data('low'),
			qtyMin2 = $price2.data('low'),
			qtyMin3 = $price3.data('low'),
			optionTotal = 0;
			
		
		// sum all option price deltas
		$('.product_options').each(function () {
			optionTotal += $(this).find(':selected').data('price');
		});
		

		// highlight the right price range
		$price1.removeClass('qty_price');
		$price2.removeClass('qty_price');
		$price3.removeClass('qty_price');
		if (typeof qtyMax1 !== 'undefined' && qtyMax1 >= qty ) $price1.addClass('qty_price');
		else if (typeof qtyMax2 !== 'undefined' && qtyMax2 >= qty && typeof qtyMin2 !== 'undefined' && qtyMin2 <= qty) $price2.addClass('qty_price');
		else if (typeof qtyMax3 !== 'undefined' && qtyMax3 >= qty && typeof qtyMin3 !== 'undefined' && qtyMin3 <= qty) $price3.addClass('qty_price');
		
		// show the total price in all the ranges
		$price1.html('$' + parseFloat( base1 + optionTotal).toFixed(2));
		$price2.html('$' + parseFloat( base2 + optionTotal).toFixed(2));
		$price3.html('$' + parseFloat( base3 + optionTotal).toFixed(2));		
	}
	
	//--------------CART FUNCTIONS------------------------------//
	$('#checkout_cart').click(function(e){
		e.preventDefault();
		$('#cartupdate').submit();
	});

	$('#update_cart').click(function(e){
		e.preventDefault();
		$('#cartupdate').attr('action', $(this).data('link'));
		$('#cartupdate').submit();
	});
		
	$('.ship_radio').click(function() {
		$('.ship_radio').each(function() {
			$(this).parents('tr').removeClass('selected');
		});
		$(this).parents('tr').addClass('selected');
		var price = $(this).attr('data-price');
		$('#frmFedex #ship_price').remove();
		$('#frmFedex').append("<input type='hidden' id='ship_price' name='ship_price' value='"+price+"' />");
	});
});		