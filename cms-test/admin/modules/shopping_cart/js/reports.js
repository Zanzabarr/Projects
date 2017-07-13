$(document).ready( function() {
	$('#submit-btn').click(function(){
		$('#form_data').submit();
	});
	
	/* create select option strings */
	var moptions = "<select id='month' name='month' class='timedisplay'><option value='1'>January</option><option value='2'>February</option><option value='3'>March</option><option value='4'>April</option><option value='5'>May</option><option value='6'>June</option><option value='7'>July</option><option value='8'>August</option><option value='9'>September</option><option value='10'>October</option><option value='11'>November</option><option value='12'>December</option></select>";
	
	var curYear = (new Date).getFullYear();
	years = new Array(5);
	var yoptions = "<select name='year' id='year' class='timedisplay'>";
	for(i=0;i<5;i++) {
		years[i] = curYear - i;
		yoptions += "<option value='"+years[i]+"'>"+years[i]+"</option>"
	}
	yoptions += "</select>";
	/***/
	
	$('#timeframe').on('change', function() {
		var timeframe = $(this).val();

		if($('.timedisplay').length) {
			$('.timedisplay').remove();
		}
		
		if(timeframe == "month") {
			$(this).parent('div').append(moptions);
		} else if(timeframe == "year") {
			$(this).parent('div').append(yoptions);
		}
		
		//select proper month/year
		var d = new Date(),
		n = d.getMonth(),
		y = d.getFullYear();
		$('#month option:eq('+n+')').prop('selected', true);
		$('#year option[value="'+y+'"]').prop('selected', true);
	});
});