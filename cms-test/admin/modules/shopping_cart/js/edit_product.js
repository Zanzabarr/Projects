$(document).ready(function() {

// some sites have more than one product type. If there is a Product type field, ensure that only the right ones are showing
var $productType = $('#product_type');

if(typeof $productType.val() !== 'undefined')
{
	// start closed
	$('.product_exclude_' + $productType.val()).prop('disabled', true).closest('.input_wrap').hide();
	
	// if a site has check/radio groups and multiple product types, different products may have different options in the group
	// if so, ensure the right ones are showing
	
	// start hidden
	$('.check_group_exclude_' + $productType.val()).prop('disabled', true).hide();	
	
	// hide all the hideable select inputs
	$('.select_exclude_' + $productType.val() ).prop('disabled', true).hide();
	
	// change visible inputs when product type changes
	$productType.change(function() {
		// show all the closable inputs
		$('.product_include').prop('disabled', false).closest('.input_wrap').show();

		// hide all the product specific excludes
		$('.product_exclude_' + $productType.val() ).prop('disabled', true).closest('.input_wrap').hide();
	
		// show all the hideable check_group inputs
		$('.check_group_include').prop('disabled', false).closest('li').show();

		// hide all the hideable check_group inputs
		$('.check_group_exclude_' + $productType.val() ).prop('disabled', true).closest('li').hide();
		
		// show all the hideable select inputs
		$('.select_include').prop('disabled', false).show();

		// hide all the hideable select inputs
		$('.select_exclude_' + $productType.val() ).prop('disabled', true).prop('selected', false).hide();
	})
}

//some sites limit collection availability for specific categories
// if so, make sure only the collections available to that category are available
var $category = $('#category_id');
if(typeof $category.val() !== 'undefined')
{
	// hide all the hideable check_group inputs
	$('.cat_check_group_exclude_' + $category.val() ).prop('disabled', true).closest('li').hide();

	$category.change(function() {
		// show all the hideable check_group inputs
		$('.cat_check_group_include').prop('disabled', false).closest('li').show();

		// hide all the hideable check_group inputs
		$('.cat_check_group_exclude_' + $category.val() ).prop('disabled', true).closest('li').hide();
		
		// show all the hideable select inputs
		$('.cat_select_include').prop('disabled', false).show();

		// hide all the hideable select inputs
		$('.cat_select_exclude_' + $category.val() ).prop('disabled', true).prop('selected', false).hide();
	});
}


$('#submit-btn').click(function(e){
	e.preventDefault();
	var noTitle = $.trim( $("#title").val() )==''
	var noUrl =  $.trim( $("#url").val() )=='';
	
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

	
	// quantity functions
	
	// make sure they are open if needed
	if ($('#q3').val() > 0) {
		$("#quan2").css("display","inline");
		$("#open2").css("display","none");
		$("#close2").css("display","inline");
		$("#open3").css("display","inline");
		$("#q2").removeAttr('disabled');
	}
	if ($('#q5').val() > 0) {
		$("#quan3").css("display","inline");
		$("#open3").css("display","none");
		$("#close2").css("display","none");
		$("#q4").removeAttr('disabled');
		$("#q6").prop('disabled', false).prop('readonly', true);
	}
	
	$("#q2").focusout(function() { 
		if(isNaN($("#q2").val()) && $("#q2").val() != '+')
		{ 
			alert("Must be a number");
			$('#q2').focus();
			$('#q2').val();
			return false;
		}
		var val = $("#q2").val();
		if (val < 1) val = 1;
		val++;
		$("#q3").attr("value", val);
		if ( $("#q4").attr('value') < val) 
		{
			$("#q4").attr('value', ++val);
			$("#q5").attr('value', ++val);
			
		}
	});	
	
	
	$("#q4").focusout(function() { 
		if(isNaN($("#q4").val()) && $("#q4").val() != '+')
		{ 
			alert("Must be a number");
			$('#q4').focus();
			return false;
		}
		var val = $("#q4").val(),
			val3 = $("#q3").val(),
			val5 = $("#q5").val();
		if (val <= val3) val = val3++;
		val++;
		$("#q5").attr("value", val);
	});

	
	$("#open2").live("click", function(e) {
		e.preventDefault();
		$("#quan2").css("display","inline");
		$("#open2").css("display","none");
		$("#close2").css("display","inline");
		$("#open3").css("display","inline");
		$("#q2").attr("value","1");
		$("#q3").attr("value","2");
		$("#q4").prop("readonly",true);
		$("#q4").attr("value","+");
		$("#q2").prop('readonly', false);
		$("#q2").focus();
	});
	
	$("#close2").live("click", function(e) {
		e.preventDefault();
		$("#quan2").css("display","none");
		$("#open2").css("display","inline");
		$("#close2").css("display","none");
		$("#q2").prop("readonly", true);
		$("#q2").attr("value","+");
		$("#q3, #q4, #price2").val(0);
	});
	
		
	$("#open3").live("click", function(e) {
		e.preventDefault();
		$("#quan3").css("display","inline");
		$("#open3").css("display","none");
		$("#close2").css("display","none");
		$("#q4").prop('readonly', false);
		var x = $("#q3").val();
		x++;
		$("#q4").attr("value",x);
		x++;
		$("#q5").attr("value",x);
		$("#q4").focus();
		$("#q6").prop('readonly', true).val('+');
	});
	
	$("#close3").live("click", function(e) {
		e.preventDefault();
		$("#quan3").css("display","none");
		$("#open3").css("display","inline");
		$("#close2").css("display","inline");
		$("#q4").prop("readonly",true);
		$("#q4").attr("value","+");
		$("#q5, #q6, #price3").val(0);
	});
	
	// --------------------------------------  options  ------------------------------
		var count = 0;
		var setcount = $('#maincount').val();;
		
		
		
		count0 = 90;
		count1 = 90;
		count2 = 90;
		count3 = 90;
		count4 = 90;
		count5 = 90;
		count6 = 90;
		count7 = 90;
		
		//var a = 'blah';
		//window[a+'x'] = 'sdfhsdf';
		//alert(blahx);
		
		
		
		//-- add option
		$('.addop').live('click', function (){
			var x = $(this).attr('id');
			var y = x.split('_');
			var id = y[0];
			//alert(id);
			window['count'+id] += 1;
			//alert(count0);
			if(window['count'+id] == 91) {
				$(this).next('br').after('<label for="option'+ id +'_'+ window['count'+id] +'" class="set_'+ id +'" style="margin-left:182px;"></label><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Option:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][option]" type="text" class="medinput focus'+ window['count'+id] +'" /><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Price Difference:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][price]" type="text" class="smallinput" /><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Weight Difference:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][weight]" type="text" class="smallinput" /><img src="img/delete.png" alt="delete" class="deleteoption" id="del_option'+ id +'_'+ window['count'+id] +'"/><br>');
			} else {
				$('label[class^=set_'+ id +']').first().before('<label for="option'+ id +'_'+ window['count'+id] +'" class="set_'+ id +'" style="margin-left:182px;"></label><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Option:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][option]" type="text" class="medinput focus'+ window['count'+id] +'" /><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Price Difference:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][price]" type="text" class="smallinput" /><p id="option'+ id +'_'+ window['count'+id] +'" class="option'+ id +'">&nbsp;Weight Difference:</p><input id="option'+ id +'_'+ window['count'+id] +'" name="opt[options_'+ id +']['+ window['count'+id] +'][weight]" type="text" class="smallinput" /><img src="img/delete.png" alt="delete" class="deleteoption" id="del_option'+ id +'_'+ window['count'+id] +'"/><br>');
			}
			$('input.focus'+ window['count'+id]).focus();
			//$('input[id^=option'+ id +'_'+ window['count'+id] +']').focus();
		})
		
		
		$('.deleteoption').live('click', function (){
			var $that = $(this);
			confirm('Are you sure?', function () {				
				var x = $that.attr('id');
				var y = x.split('_');
				var id = y[0];
				var numm = y[2];
				$('label[for^='+ y[1] +'_'+ numm +']').remove();
				$('input[id^='+ y[1] +'_'+ numm +']').remove();
				$('p[id^='+ y[1] +'_'+ numm +']').remove();
				$that.next('br').remove();
				$that.remove();} );
		});
		
		$('.deleteoptionset').live('click', function (){
			var $that = $(this);
			confirm('Are you sure?', function () {
				var x = $that.attr('id');
				var y = x.split('_');
				$('label[for^='+ y[1] +'_set]').remove();
				$('input[id^='+ y[1] +'_set]').remove();
				$('input[id^='+ y[1] +'_addop]').remove();
				
				$('label[for^=option'+ y[1] +'_]').remove();
				$('input[id^=option'+ y[1] +'_]').remove();
				$('img[id^=del_option'+ y[1] +'_]').next('br').remove();
				$('img[id^=del_option'+ y[1] +'_]').remove();
				$('p[class^=option'+ y[1] +']').remove();
				
				$that.next('br').remove();
				$that.remove();
			} );
		});
		
		
		
		$('.similar').change(function() {
		  $(this).closest('label').toggleClass("sel", this.checked);
		});

		
		//--- add set
		$('#addset').live('click', function (){
			
			$(this).before('<br><label for="'+ setcount +'_set">Option:</label><input id="'+ setcount +'_set" style="margin-left:110px;" name="opt[options_'+ setcount +'][name]" type="text" /><img src="img/delete.png" alt="delete" class="deleteoptionset" id="deloption_'+ setcount +'"/><input name="add" type="button" value="Add Option" class="addop" id="'+ setcount +'_addop" /><br />');
			
			setcount += 1;
		})
		

	
	
	


	
});
