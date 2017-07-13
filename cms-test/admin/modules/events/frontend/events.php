<?php //session_start();

/* variables passed along from index.php (this is an include from index)
**	$_config	: contains all config data from admin/includes/config.php
**	$uri		: array of uri components: $uri[0] is the base (events in this case)
**	$module = 'events';
**	$pages		: pages object defined in admin/includes/functions.php - used to build menus and keep track of page info
*/

/* variables passed along from modules/events/frontend/head.php (via index.php)
**	$options		: (global) evebts page options (data includes events home page data)
**	$base_url		: (global) the base site path
** 	$arOutput		: display data
*/

if($eventsview == 'list') {
	include('list-events.php');
} else {
	include('cal-events.php');
}

 /* SCRIPT BELOW MUST ACCOMPANY FRONTEND CONTENT */ ?>
<script>
$(document).ready( function() {
	
	/* size calendar-day divs */
	var dayHeight = 0;
	$('.calendar-day').each( function() {
		if(dayHeight < $(this).height()) dayHeight = $(this).height();
	});
	$('.calendar-day').each( function() {
		$(this).css('height', dayHeight);
	});

	$('div.cat-toggle').live('click', function() {
		$('div.exp-toggle').removeClass('toggled-on');
		$('.expmenu').removeClass('toggled-on');
		$(this).toggleClass('toggled-on');
		$('.catmenu').toggleClass('toggled-on');
		var margin = $('.catmenu > li').length + 3;
		$('.catmenu').css('margin-bottom','-'+margin+'em');
	});
	
	$('div.exp-toggle').live('click', function() {
		$('div.cat-toggle').removeClass('toggled-on');
		$('.catmenu').removeClass('toggled-on');
		$(this).toggleClass('toggled-on');
		$('.expmenu').toggleClass('toggled-on');
		if($('.expmenu > li').length > 1) {
			var margin = $('.expmenu > li').length + 2;
		} else {
			var margin = 2;
		}
		$('.expmenu').css('margin-bottom','-'+margin+'em');
	});
	
	//popupSwitch();
	$('.event').live('click', function() {
		/* if popup too far to the right, switch it to the left */
		var offset = $(this).parent('div').offset();
		var popright = ($(window).width() - offset.left);
		if(popright < $(this).children('span.pop').width()) {
			$(this).children('span.pop').addClass('popSwitch');
		}
		/**/
		$(this).children('.pop').toggle();
	});

	$('#print-btn').live('click', function() {
		if($('.eventcontent').length) {
			var title = $('title').html();
			$(".eventcontent").printThis({
				debug: false,              //show the iframe for debugging
				importCSS: true,           //import page CSS
				printContainer: false,      //grab outer container as well as the contents of the selector
				loadCSS: "<?php echo $_config['admin_url'];?>modules/<?php echo $module;?>/frontend/print.css", 				//path to additional css file
				pageTitle: title,             //add title to print page
				addTitle: false,			//prepend title to printed section ** added by BEH **
				removeInline: false,       //remove all inline styles from print elements
				printDelay: 333,           //variable print delay S. Vance
				header: null               //prefix to html
			});
		} else {
			$(".calendar").printThis({
				debug: false,              //show the iframe for debugging
				importCSS: true,           //import page CSS
				printContainer: false,      //grab outer container as well as the contents of the selector
				loadCSS: "<?php echo $_config['admin_url'];?>modules/<?php echo $module;?>/frontend/print.css", 				//path to additional css file
				pageTitle: "<?php echo date("F_Y",strtotime($month.'/01/'.$year));?>",      //add title to print page
				addTitle: true,				//prepend title to printed section ** added by BEH **
				removeInline: false,       //remove all inline styles from print elements
				printDelay: 333,           //variable print delay S. Vance
				header: null               //prefix to html
			});
		}
	});
	
	/*$('#viewlink').live('click', function() {
		var currentview = $(this).html();
		if(currentview == 'calendar') {
			currentview = 'list';
		} else {
			currentview = 'calendar';
		}
		$('#setview').load('external/setview.php',{eventsview:currentview});
	});*/
});
</script>