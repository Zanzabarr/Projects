<script type="text/javascript">

$(document).ready(function() {
	// if start_open set: start open
	if($(".options-button:visible").attr("data-start_open")){
		openFrame($(".options-button:visible"));
	} 
	
    $(".options-button").live("click", function(e) {
        e.preventDefault();                
		openFrame($(this))


        return false;
    });
	
	function openFrame($this)
	{
		 		if($this.siblings('.iframe_area').find('.options_frame').length == 0) {
			// no options frame exists yet so create it
			var scriptSRC = $this.siblings('.scriptSRC').val();

			// but first, close any other option frames
			$('.options_frame').remove();
			
			$('<iframe />');  // Create an iframe element
			$('<iframe />', {
				name: 'options_frame',
				'class': 'options_frame',
				src: scriptSRC
			}).appendTo($this.siblings('div.iframe_area'));
		}
		else {
			$('.options_frame').remove();
		}
	}
	
	$(".member_panel .member-login").live('click', function(e){
		e.preventDefault();
		var that = this;
	//	$(this).siblings('.member-login-form').submit();
	// lets create a login for dynamically
		var formLocation = $(this).attr("href"),
			postData = $.parseJSON($(this).attr('data-post'));

//	$('div />');
	$(".member_panel").append($('<div />',{'class' : 'login_form'}));
	$(".member_panel .login_form").load(formLocation,postData)

	$(".member_panel .member-login").hide();
	$(".member_panel .member-login-cancel").show();
	

	});
	
	$(".member_panel .member-login-cancel").live('click', function(e){
		e.preventDefault();
		$(".member_panel .login_form").remove();
		$(".member_panel .member-login").show();
		$(".member_panel .member-login-cancel").hide();
	});	
	
});
    

	
function changeURL(url) {
    document.location = url;
}


</script>
