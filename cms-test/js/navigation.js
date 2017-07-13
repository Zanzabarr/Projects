/* NAVIGATION SUPPORT */

function basename(path) {
   return path.split('/').reverse()[0];
}

$(document).ready( function() {
	var maxTabletWidth = 899;	/* MAX TABLET WIDTH FOR DESIGN IN PX */

	/* SILO NAVIGATION */
	/* populate siloed sections via ajax - child/grandchild menus */
	$('.MenuBarSubmenu').each( function() {
		var parent = $(this).attr('data-parent');
		var slug = basename(window.location.pathname);
		$('#'+parent+'-child').load('external/silo.php',{parent:parent, slug:slug});
	});
	/**/
	
	/* FULL SIZE */
	/* full-size menu */
	$('.MenuBarHorizontal.notMobile li').hover( function() {
		var menu = $(this).children('ul:first');
		$(this).children('a').addClass('MenuBarItemHover');
		
		/* if menu too far to the right, switch it to the left */
		var offset = $(this).offset();
		var menuright = ($(window).width() - offset.left);
		if(menuright < $(menu).width()) {
			$(menu).addClass('horizontalSwitchClass');
		}
		if(menuright < ($(menu).width()*2)) {
			$(menu).children().addClass('horizontalSwitchClass');
			$('.horizontalSwitchClass').children().addClass('horizontalSwitchClass');
		}
		/**/
		
		$(menu).addClass('MenuBarSubmenuVisible');
	}, function() {
		var menu = $(this).children('ul:first');
		$(this).children('a').removeClass('MenuBarItemHover');
		$(menu).children('.horizontalSwitchClass').children().removeClass('horizontalSwitchClass');
		$(menu).children().removeClass('horizontalSwitchClass');
		$(menu).removeClass('horizontalSwitchClass');
		$(menu).removeClass('MenuBarSubmenuVisible');
	});
	/**/
	
	/* SIDE MENU */
	$('#sideMenu.notMobile li').hover( function() {
		$(this).children('ul').addClass('MenuBarSubmenuVisible');
	}, function() {
		$(this).children('ul').removeClass('MenuBarSubmenuVisible');
	});
	/**/
	
	/* TABLETS */
	/* Mobile full-size menu (tablets) */
	$('ul.isMobile span.mobileOpen').live('click', function() {
		$(this).removeClass('mobileOpen').addClass('mobileClose');
		var menu = $(this).parent('span').siblings('ul');
		if($(menu).parent('li').hasClass('topRow')) {
			$('.topRow ul.MenuBarSubmenuVisible').each( function() {
				$(this).siblings('span').children('span').removeClass('mobileClose').addClass('mobileOpen');
				$(this).removeClass('MenuBarSubmenuVisible');
			});
		}
		
		/* if menu too far to the right, switch it to the left */
		var offset = $(menu).parent('li').offset();
		var menuright = ($(window).width() - offset.left);
		if(menuright < $(menu).width()) {
			$(menu).addClass('horizontalSwitchClass');
		}
		if(menuright < ($(menu).width()*2)) {
			$(menu).children().addClass('horizontalSwitchClass');
			$('.horizontalSwitchClass').children().addClass('horizontalSwitchClass');
		}
		/**/
		
		$(menu).addClass('MenuBarSubmenuVisible');
	});
	$('ul.isMobile span.mobileClose').live('click', function() {
		$(this).removeClass('mobileClose').addClass('mobileOpen');
		var menu = $(this).parent('span').siblings('ul');
		$(menu).children('.horizontalSwitchClass').children().removeClass('horizontalSwitchClass');
		$(menu).children().removeClass('horizontalSwitchClass');
		$(menu).removeClass('horizontalSwitchClass');
		$(menu).removeClass('MenuBarSubmenuVisible');
	});
	/**/
	
	/* MOBILE */
	/* Mobile Button */
	$('div.menu-toggle').click( function() {
		$(this).toggleClass('toggled-on');
		$('#topMenu').toggleClass('toggled-on');
	});
	/**/
	
	/* Mobile Menu */

	/* Remove class toggle from menu if screen is rotated or resized above mobile width */
	$(window).resize( function() {
		if($(window).width() > maxTabletWidth) {
			if($('#topMenu').hasClass('toggled-on')) {
				$('#topMenu').removeClass('toggled-on');
				$('div.menu-toggle').removeClass('toggled-on');
			}
		}
	});
	/**/
});
