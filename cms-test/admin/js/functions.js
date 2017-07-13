if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  };
}

/*	bindNodDirtyPageExit
*  sets bindings and vars to prevent accidental navigation from page with unsaved changes.
*  @parm formId (string) 	id of the form to be tested. 		eg) 'page_form'
*  @parm tinyIds (array)	id/s of tinymce fields to be tested	eg) ['content'] or ['content', 'small_desc']
*  @parm legalExits			array of links to allow page navigation to work (save and cancel buttons)
*								eg) ['#submit-btn', '#cancel-btn']
*/
window.tinyMCElegalExit = false;
window.onBeforeIsBound = false;
window.editPageDirty = false;
function bindNoDirtyPageExit(formId,tinyIds,legalExits)
{
	var $form_id = $(formId);
	for(var tmpExit in legalExits)
	{	
		$(document).on('click', legalExits[tmpExit], function(){window.tinyMCElegalExit = true;});
	}
		
	// do checkbox and input on click (works cross browser. ie8 doesn't work on change)
	$("input[type='checkbox'], input[type='radio']",$form_id).click(function(){window.editPageDirty = true});
	// textareas and selects (use onchange so we don't trigger on clicks that change nothing)
	$("input,textarea,select",$form_id).change(function(){window.editPageDirty = true});
	
	// ie <9 input changes ( works even if change is made and input isn't blurred )
	var propertyChangeUnbound = false;
	$("input",$form_id).on("propertychange", function(e) {
		if (e.originalEvent.propertyName == "value") {
			window.editPageDirty = true;
		}
	});
	// html5 input changes ( works even if change is made and input isn't blurred )
	$("input",$form_id).on("input", function() {
		if (!propertyChangeUnbound) {
			$("input").unbind("propertychange");
			propertyChangeUnbound = true;
		}
		window.editPageDirty = true;
	});
	if(!window.onBeforeIsBound)
	//if(true)
	{
		window.onBeforeIsBound = true;
		// unload binding, checks dirty state and checks all tinyMCEs passed
		// empty the binding if it already exists so we don't get a bunch of them
		$(window).on('beforeunload',function(){
			// only do these checks if not a legal exit
			if(window.tinyMCElegalExit) 
			{
				window.tinyMCElegalExit = false;
				return;
			}
			
			// only do these checks if not already dirty
			if(!window.editPageDirty)
			{
				for(tmpid in tinyIds)
					if(tinymce.get(tmpid).isDirty()) window.editPageDirty = true;
			}

			if(window.editPageDirty) return 'Unsaved changes will be lost. Do you want to leave?'
		} );
	}
}

//-----------------------modal----------------------------------------
// overload confirm with modal version
function confirm(msg,callback) {
	
	// warn about misuse
	if ( typeof(callback) == 'undefined' ) {
		alert('System Error', 'javascript confirm has been overloaded: <br /> Do: <br /> confirm( msg, callback ); <br />Instead of:<br /> if ( confirm(msg) ) callback();');
		return false;
	}
	
	$('#confirm')
		.jqmShow()
		.find('#confirm_form fieldset p')
		.html(msg)
		.end()
		.find('a.button:visible')
		.unbind('click')
		.click(function(e){
			e.preventDefault();
			if( $(this).attr('id') == "confirm_btn") (typeof callback == 'string') ? window.location.href = callback : callback();
			$('#confirm').jqmHide();
		});
	return false;
}

// overload alert, taking a title and a message.
// if only one parm is passed, title becomes 'Alert'
function alert(title, message)
{
	// if only one item is passed, treat it as a traditional alert
	if ( typeof(message) == 'undefined' ) {
		message = title;
		title = 'Alert'
	}
	$('#alert').jqm();
	$('#alert').jqmAddClose('.close');
	$('#alert h2').html(title);
	$('#alert_form fieldset p').html(message);
	$('#alert').jqmShow();      
}

// ---------------dynamic banner-----------------
// parms: 	(str)	type	-	accepted values: success, error, warning
//			(str)	heading	-	What you want to appear in the heading
//			(str)	message	-	What you want to appear as the banner's message
function openBanner(type, heading, message)
{
	if (typeof message == 'undefined'){
		message = '';
	}
		
	$('.msg-wrap h2').html(heading);
	$('.msg-wrap span').html(message);
	$('.msg-wrap #bannerType').attr('class', type);
	$('.msg-wrap').find('.close-message').fadeIn(800);
	$('.msg-wrap').css('opacity',1).fadeIn(1000);
}

// scrolling function, slide to id
$.fn.scrollView = function () {
    return this.each(function () {
        $('html, body').animate({
            scrollTop: $(this).offset().top
        }, 500);
    });
}

function validSlug(slug)
{
	slug = slug.toLowerCase();
	slug = slug.replace(/[^a-z0-9-]/g, '-');
	slug = slug.replace(/(-)+/g, '-');
	return slug.replace(/^-/, "");
}


function changeSlug(curSlug, usedSlugs, $source, $target){

	// maintain a valid slug
	var testSlug = validSlug($source.val());
	
	$target.attr("value", testSlug);

	uniqueInput( curSlug, usedSlugs, $source, $target, 'Url exists or is reserved.' );


}


// uniqueInput
//		values entered in the source input are changed to a properly formatted slug in the slug input field
//		if the slug generated isn't the current page's pre-existing slug and is in the usedSlugs array (both php generated on the calling page)
//			then the slug input is given the class: badUrl and an error is displayed in the url input's message area
//
// 	variables:
//  	curSlug		the current page's slug
// 		usedSlugs	array containing all inellegible slugs
// 	
// 		$source		jquery object: this is the input that is generating the slug (title or slug inputs)
//  	$slug		jquery object: the slug input that is being written to.
function uniqueInput( curVal, arInvalid, $source, $target, message )
{
		// error message id
		var $errMsg = $target.prev().children();
		var testVal = ($source != $target) ? $target.val() : $source.val();
		
		if( testVal != curVal && $.inArray(testVal, arInvalid) > -1 ) 
		{
			$target.addClass('badUrl');
			$errMsg.addClass('errorMsg').html(message).show('slow');
		}
		else 
		{
			$target.removeClass('badUrl');
			$errMsg.removeClass('errorMsg').html('');
		}
}

function getFileExtension(filename)
{
	var a = filename.split(".");
	if( a.length === 1 || ( a[0] === "" && a.length === 2 ) ) {
		return "";
	}
	return a.pop().toLowerCase(); 
}

$.fn.setModalTop = function(){
	// set the modal's height from the top of the screen
	// get the height of screen top
	var $window = $(window),
		scrollTop = $window.scrollTop(),
	// get the height of the modal
		modalHeight = $(this).height(),
	// get the height of the window
		windowHeight = $window.height(),
	// subtract the modal height from the screen height: treat negative as 0
		newMargin = (windowHeight - modalHeight) / 2,
		truMargin = newMargin < 0 ? 0 : newMargin,
		newTop = scrollTop + truMargin;
		$(this).css({top:newTop + 'px'});
		
	return this;
};

$(document).ready(function() {
	
/*	// during dev
	$('.toggle').each(function(){var thisId = $(this).attr('id');
		var $multiselect = $('#'+thisId+"-wrap select[multiple]");
		var posBack = function() {};
		if ($multiselect.css('position') == 'absolute') $multiselect.css('position', 'relative');
		else posBack = function(){$multiselect.css('position', 'absolute');};
		$('#'+thisId+"-wrap").slideToggle('slow', posBack);});
*/
	// --------------------------------------modal------------------------------------------------
	// establish the confirm modal
	$('#confirm').jqm({overlay: 60, modal: true, trigger: false, target:false});

	// --------------------------------------tipTips------------------------------------------------
	// setup default tiptips
	$('.tipTop').tipTip({defaultPosition:"top"});
	$('.tipRight').tipTip({defaultPosition:"right",edgeOffset:18});
	$('.tipLeft').tipTip({defaultPosition:"left"});
	$('.tiptip').tipTip({defaultPosition:"right"});
	$('.tipSelect').tipTip({defaultPostition:"right"});
	
	// --------------------------------------Toggle items------------------------------------------------

	
	// heading needs to have class: toggle, and an id (eg. toggle-heading) 
	// div to open/close needs to have the same id + -wrap (eg. toggle-heading-wrap)
	$('.toggle').live('click', function(e){
		
		var thisId = $(this).attr('id');
		var $multiselect = $('#'+thisId+"-wrap select[multiple]");
		var posBack = function() {};
		if ($multiselect.css('position') == 'absolute') $multiselect.css('position', 'relative');
		else posBack = function(){$multiselect.css('position', 'absolute');};
		$('#'+thisId+"-wrap").slideToggle('slow', posBack);
	});

	
	// ----------------------error/message banner and inline messages----------------------------------
	// hide inline messages on click
	$('span.errorMsg, span.warningMsg, span.successMsg').live('click', function(){$(this).fadeOut();});
		
	// close message
	$('.close-message').click(function(){
		$(this).hide(810);
		$parent = $(this).parent();
		$parent.animate({opacity: .15 }, 300, function(){$parent.hide(500)});
	});
	
	// --------------on page load------------
	// display banner if message exists
	$('.success, .error, .warning').parent().fadeIn(2000);
	


});	