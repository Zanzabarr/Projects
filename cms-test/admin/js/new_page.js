

$(document).ready(function() {
	

	//----delete page
	$('.del_page').live('click', function(e){
		
		if ( $('#addPage_container').hasClass('unsavedChanges') ) return false;
		$line = $(this).parents('li');
		if (childDepth($line) > 0 ) {
			alert('Has Sub-Menus', 'Move Sub-Menus before deleting this Page.');
			return false;
		}
		confirm("This page will be lost forever! Are you sure?", this.href);
		return false;
		
	});
	
	
	//-------------------------------------saving------------------------------------------------------
	$('#submit-btn').click( function(e){
		e.preventDefault();
	//	$("body").css("cursor", "progress");
		// ---create postdata---
		
		var curParent = 'id0';
		var curChild = 'id0';
		// fill an array with each line's data:	
		var arLines = new Array();
		var parentPosn = 0;
		var childPosn = 0;
		var grChildPosn = 0;
		var menuLevel =  1 ;
		// get id, get parent_id and is_menu (is that all I need to update this???)
		$('#top li').not('#new-menu').each(function(index, line) {
			$line = $(line);
			
			if ($line.attr('id') == 'side-menu' ) 
			{
				menuLevel = 2;
				return true;
			}
			
			// get it's id
			var lineId = $line.attr('id');
			
			// obviously has menu is true
			
			var parentId = 'id0';
			// figure out its parent
			if ( $line.hasClass('parent') ) {
				parentId = 'id0';
				curParent = lineId;
				curPosn = parentPosn++;
			} else if ( $line.hasClass('child') ) {
				parentId = curParent;
				curChild = lineId;
				curPosn = childPosn++;
				// reset the child position counter if the next element is a parent (this is the last child of the current parent)
				if ( $line.next().hasClass('parent') ) childPosn = 0;
			} else {// is a grand child
				parentId = curChild;
				curPosn = grChildPosn++;
				// reset the grandchild position counter if the next element is a child (last grchild of current child)
				if ( $line.next().hasClass('child') ) grChildPosn = 0;
				// reset the grandchild and child position counters if the next element is a parent
				else if ( $line.next().hasClass('parent') ) 
				{
					grChildPosn = 0;
					childPosn = 0;
				}	
			}
			var menuName = $line.children().children().html();

			arLines.push(new Array(curPosn, lineId, parentId, menuLevel, menuName));
		});

		// now do menuless
		$('ul.menuless li').not('#new-menuless').each(function(index, line) {
			$line = $(line);

			// get it's id
			var lineId = $line.attr('id');
			
			// menu level and menu position both = 0
			var menuLevel = 0;
			var curPosn = 0;
			var parentId = 'id0';
			var menuName = $line.children().children().html();

			arLines.push(new Array(curPosn, lineId, parentId, menuLevel, menuName));
		});
		// post the data, php receives an array of comma separated numbers
		$.post('pages.php', {'items[]': arLines }, function(data){
			$("body").css("cursor", "auto");
			if (data == 'success') {
				openBanner('success', 'Successfully Saved');

				
			}
			else openBanner('error', 'Error Saving', 'Could not save changes');
			
			$('#addPage_container').removeClass('unsavedChanges');
		});
	});
	// close message
	$('.close-message').click(function(){
		$(this).hide(810);
		$parent = $(this).parent();
		$parent.animate({opacity: .15 }, 300, function(){$parent.hide(500)});
		
	});
	
	// prevent accidental navigation away with unsaved changes
	$('a').live('click', function(e) {
		if ( ! $('#addPage_container').hasClass('unsavedChanges') ) return true;
		$clicked = $(this);
		if ( $clicked.attr('id') == 'submit-btn' || 
			$clicked.attr('id') == 'cancel-btn' || 
			$clicked.attr('id') == 'change_menu_btn'  || 
			$clicked.attr('id') == 'confirm_btn' || 
			$clicked.hasClass('dialog_cancel_btn')  
		) return true;
		//alert('Unsaved Changes', 'Changes to menu order have not been saved.<br/>Either Save or Cancel before proceeding.');
		alert('Unsaved Changes', 'Changes to menu order have not been saved.<br/>Either Save or Cancel before proceeding.');
		return false;
	});


	//-------------------------------expand/collapse ---------------------------------------------------

	// give them expand/collapse handles
	$('ul.menu-item li').not('#new-menu, #side-menu').each( function(){
		var $item = $(this);
		var itemLevel = getLevel($item);
		if ( getLevel($item.next('li') ) > itemLevel ){
			$item.addClass('collapsable');
		}
	});
	
	// start collapsed
	$('li.parent.collapsable div.exp-col').each(function() {
		expandCollapse ( $(this).parent(), false );
	});
	
	// expand/collapse
	$('div.exp-col').click(function(e){
		var $line = $(this).parent();
		expandCollapse ($line, true);
	});

		// --------------------------------helpers------------------------------------------------
	
	// expand or collapse a branch from starting line item, second parm is animatited: true/false
	function expandCollapse ($line, animated)
	{
		var collapse = $line.hasClass('collapsable');
		var lineLevel = getLevel($line);
		var nextLevel = lineLevel +1;
		var $nextLine = $line.next('li');
		
		while (lineLevel < nextLevel && $nextLine.length > 0)
		{
			var curLevel = getLevel($nextLine);
			var x =$nextLine.children('span').html()
			if (curLevel >= nextLevel){
				$expCol = $nextLine.children('.exp-col');
				if(collapse) {
					if (animated){
						$expCol.removeClass('show');
						$nextLine.slideUp('slow','easeInOutQuint');
						
					} else {
						$nextLine.hide();
					}
					$line.removeClass('collapsable').addClass('expandable');
				}else { 
					//  make sure the button is always a -
					if ($nextLine.hasClass('expandable')){
						$nextLine.removeClass('expandable').addClass('collapsable');
					}
					if (animated){
					// show the element, showing it's button last (prevents clipped view)
						$nextLine.slideDown('slow', 'easeInOutExpo', function () {$(this).children('.exp-col').addClass('show');});
						
					} else {
						$nextLine.show();
					}
					$line.removeClass('expandable').addClass('collapsable');
				}
				nextLevel = curLevel > nextLevel ? nextLevel + 1: nextLevel;
				$nextLine = $nextLine.next('li');
				nextLevel = getLevel($nextLine);
			}
		}
	}
	// returns true if more than one immediate child is found, false if less than two immediate children
	function getNumOfDirectChildren ($line)
	{	
		var lineLevel = getLevel($line);
		var $nextLine = $line.next('li');
		var nextLineLevel = getLevel($nextLine);
		var childCount = 0;
		while ( $nextLine.length > 0 && $nextLine.html() != '<hr>' && lineLevel < nextLineLevel )
		{ 
			if ( getLevel($nextLine) == lineLevel + 1 ) childCount++;
			$nextLine = $nextLine.next('li');
			nextLineLevel = getLevel($nextLine);
		}
		return childCount;
	}
	
	// returns the numerical value for the item's ancestry depth
	function getLevel ($item) 
	{
		var itemLevel = 0;
		if ( $item.hasClass('parent') ){
			itemLevel = 1;
		}
		else if ( $item.hasClass('child') ) {
			itemLevel = 2;
		}
		else if ( $item.hasClass('grandchild') ) {
			itemLevel = 3;
		}

		return itemLevel;
	}
	
	
	function getLineage ($item)
	{
		if ($item.hasClass('parent') ) return 'parent';
		if ($item.hasClass('child') ) return 'child';
		if ($item.hasClass('grandchild') ) return 'grandchild';
	}
	
	function lineageFromLevel (itemLvl)
	{
		if (itemLvl == 1) return 'parent';
		if (itemLvl == 2) return 'child';
		if (itemLvl == 3) return 'grandchild';
	}
	
	// returns number of levels of children item has
	
	// returns 	0 for no children
	//			1 if it only has children
	//			2 if it has grandchildren
	function childDepth ($item)
	{
		// menuless items always have 0 children
		if ( $item.parent().hasClass('menuless')) return 0;
		
		var parentLevel = getLevel($item);
		var $nextItem = $item.next('li');
		
		// items that have no nextitem or the next item is lower or same level have no children
		if ($nextItem.length < 1 || getLevel($nextItem) <= parentLevel) return 0;
		
		// if this isn't a parent level, there can only be one level of children
		if ( ! $item.hasClass('parent') ) return 1;
		
		// at this point, it is a parent with at least one child,
		//	is there another level? Cycle until either there are no more nextLines or 
		while ($nextItem.length > 0 && $nextItem.hasClass('child') ) $nextItem = $nextItem.next('li');
		// if there are no more or the nextItem is another parent, return 1
		if ( $nextItem.length < 1 || $nextItem.hasClass('parent') ) return 1;
		// otherwise, there is at least 1 grandchild
		return 2;
	}
	
	// put the line item in its proper place after updating all of it's classes/relationships
	function dropLineItem($line, $target)
	{	
		var lineLevel = getLevel($line);
		var $nextLine = $line.next('li');
		var $lastChild = false;

		while ( $nextLine.length > 0 && $nextLine.html() != '<hr>' && lineLevel < getLevel($nextLine) )
		{
			// basically curchild
			$lastChild = $nextLine;
			$nextLine = $lastChild.next('li');
		}
		
		// if this isn't the last child, start at the last child and move them into position in reverse order
		if ( $lastChild !== false )
		{
			var $prevLine = $lastChild;
			// now work backwards writing to position
			while($prevLine.length > 0 && $prevLine.html() != $line.html()  )
			{
				// figure out the new lineage
				var newLevel = getLevel($prevLine) + getLevel($target) - lineLevel + 1;
				if ($target.hasClass('blank')) newLevel -=  1;
				$prevLine.removeClass('parent child grandchild');
				
				// if target is menuless, do append function. (always parent)
				if ( $target.hasClass('menuless')){
					$prevLine.addClass('parent');
					moveAppend($prevLine, $target);
					
				}
				else // otherwise, move it into its place in the tree with its new lineage
				{
				 
					if (newLevel == 1 ){
						$prevLine.addClass('parent');
					} else if (newLevel == 2){
						$prevLine.addClass('child');
					} else $prevLine.addClass('grandchild');
					
					moveAfter($prevLine, $target);
				}
				$prevLine = $prevLine.prev('li');
			}
			
		}
		// having dealt with all children, now deal with the initial element
		
		

			// if line isn't a parent itself
			// find the number of children of line's parent...if it is the only one, get rid of its expand-collapse classes
			if (lineLevel != 1) // not a parent level element
			{
				// if prev isn't the parent, there are other siblings, fall out
				var $prevLine = $line.prev();
				if (getLevel($prevLine) + 1 == lineLevel){
					// if line doesn't have siblings, parent no longer needs expand or collapse
					var numberOfChildren = getNumOfDirectChildren ( $prevLine );
					if ( numberOfChildren < 2 )
					{
						$prevLine .removeClass('expandable');
						$prevLine .removeClass('collapsable');
					}
				}
			}
			
			
			// straight forward if it is moving to menuless or a blank target
			if ( $target.hasClass('menuless') )	moveAppend($line, $target);
			else if ( $target.hasClass('blank') ) {
				var newLevel = getLevel($target);
				$line.removeClass('parent child grandchild');
				if ( newLevel == 1 ) {
					$line.addClass('parent');
				} else if (newLevel == 2){
					$line.addClass('child');
				} else $line.addClass('grandchild');
				moveBlank ($line, $target);
				//$target.replaceWith($line);
				
			}
			else { // a little more work if moving to the menu tree
			// if it moved from menuless...change its menu name
/*			if ($line.parent().hasClass('menuless') )
			{
				changeMenuName($line);
			}
*/			
			// assign the appropriate lineage class
			var newLevel =  getLevel($target) + 1;
			$line.removeClass('parent child grandchild');
			if ( $target.attr('id') == 'side-menu' || $target.attr('id') == 'new-menu' || newLevel == 1 ) {
				$line.addClass('parent');
			} else if (newLevel == 2){
				$line.addClass('child');
			} else $line.addClass('grandchild');
			$target.addClass('collapsable');
			// move it into it's proper home
			moveAfter($line, $target);
		}
	}
	
	function isAnyDescendant ( $target, $item )
	{	
		var itemLevel = getLevel($item);
		var $nextItem = $item.next('li').not('.blank');
		if ( getLevel($nextItem) != itemLevel + 1) return false
		if ( $nextItem.html() == $target.html() ) return true;
		while ($nextItem.length > 0 && getLevel($nextItem) > itemLevel )
		{
			if ( $nextItem.attr('id') == $target.attr('id') ) return true;
			$nextItem = $nextItem.next('li').not('.blank');
		}
		return false;
	}
	
	function isImmediateParent( $target, $item )
	{	
		// target can't be immediate parent if item doesn't have a parent
		if ($item.hasClass('parent') ) return false;
		
		// throw it out if the target's level isn't one less than item's level (immediate predecessors only)
		var targetLevel = getLevel($target);
		if (targetLevel +1 != getLevel($item) ) return false;
		
		$prevItem = $item.prevAll('li').not('.blank');
		// true if the imm prev element is the target
		if ( $prevItem.eq(0).attr('id') == $target.attr('id') ) return true;

		var $targetNext = $target.nextAll('li').not('.blank');
		// if any item has lesser or lower level than target before reaching item, item is not a direct child
		while ($targetNext.length > 0 && getLevel($targetNext.eq(0)) > targetLevel) {
			// if this is the immediate child, otherwise, keep looking
			if($targetNext.eq(0).attr('id') == $item.attr('id') ) return true;
			$targetNext = $targetNext.nextAll('li').not('.blank');
		}
		return false;
	}
	
	// returns the line item's parent...assumes a parent exists
/*	function getParent($line)
	{	
		var lineLevel = getLevel($line);
		var $prevLine = $line.prev('li');
		if ( getLevel($prevLine) + 1 == lineLevel) return $prevLine;
		
		
	}
*/	
	//------------------------------- Drag and Drop -----------------------------------------------
	
	makeDragable( $( '#top li').not('#new-menu, #new-menuless, #side-menu')  );
	makeDragable( $( '#side li').not('#new-menu, #new-menuless, #side-menu')  );
	makeDragable( $( '.menuless li').not('#new-menu, #new-menuless, #side-menu')  );
	
	function validBlankTarget( $item, $target )
	{		
		// if the target is the dragged item, don't create a child
		//  unless, this is the last child of it's kind and it isn
		if ( $target.attr('id') == $item.attr('id') ) return false
		
		// target can't open blank below self if the next line is the item being moved
		if ( $target.nextAll('li').filter(':visible').eq(0).attr('id') == $item.attr('id') ) return false;		
		
		itemDepth = childDepth($item);
		targetDepth = childDepth($target);
		targetLevel = getLevel($target);
	//	console.log('itemDepth: ' + itemDepth + ' targetDepth: ' + targetDepth + ' targetLevel: ' + targetLevel + '  ' + $target.children('span').children('span').html());

		// next, limit items with children from being placed somewhere that their tree won't fit
		// if target is a collapse element...then the following blank is it's child: it's level is one deeper
		// otherwise, blank level is the same as target level
		var blankLevel =  $target.hasClass('collapsable') ? targetLevel + 1 : targetLevel;
		if (blankLevel + itemDepth > 3) return false;	
		

		// item can't target any of it's children
		if ( isAnyDescendant ( $target, $item ) ) return false;

		return true;
	}
	
	// the target's next item is a blank
	// if the target is collapsable, the blank should be one of it's children: set lineage one deaper than target
	// otherwise, the blank is a sibling: blank's lineage is same as target lineage
	function getBlankLevel($target)
	{
		
		if ($target.hasClass('collapsable') )
		{
			return lineageFromLevel( getLevel($target) + 1);	
		}
		else
		{
			return getLineage($target);
		}
	}
	
	function makeDragable ( $dragitem )
	{
		// position cursor relative to bottom: topmenu and menuless' bottom is the bottom of the helper but
		//		sidemenu has the 'bottom' value at the top of the draggable (weird)

		$dragitem.draggable({
			revert: 'invalid', // when not dropped, the item will revert back to its initial position
			handle: '.drag-title',
			zIndex: 2700,
			start: function(event, ui){
				// signal that something is being moved
				$('#addPage_container').removeClass('not-dragging');
				// if this not a menuless, open a space before every other child of same parent
			
				$item = $(this);

				// test each (non-menuless) item to see if item can be put in front (or behind in the case of the last listed element) of it
				$('ul.menu-item li').not('#new-menu, #side-menu').filter(':visible').each(function(index) {
				//console.log($(this).nextAll('li.parent').attr('id') );
					var $target = $(this);
					
					
					
					// this requires more thought: after won't work, needs to be after all closed
					// therefore; place before next li that is visible( or append)
					if ( validBlankTarget($item, $target) ) { 
						// determine the new blank element's lineage(parent, child,grandchild)
						var posn = getBlankLevel($target);
						var newBlankLi = '<li class="blank ' + posn + '"><span class="drag-title"></span></li>';
						var $allVisLi = $target.nextAll('li').filter(':visible');
						// if there are no li after the target, it is the last item in the list, put the item after it.
						if ($target.next('li').length == 0 ) { 
							$target.after(newBlankLi);
						}
						// otherwise, if there are no visible li left, put it at the end
						else if ($allVisLi.length == 0) {
							var lastSib = $target.siblings().length - 1;
							$target.siblings().eq(lastSib).after(newBlankLi);
						}
						// otherwise, there is at least one visible li following the target, put the blank before it
						else {
							$allVisLi.eq(0).before(newBlankLi);
						}
						$target.css('padding-bottom', '0px' );
					}
					//if ( $('ul.menu-item li').not('#new-menu, #side-menu').filter(':visible').eq(index +1).hasClass('.blank') ) $target.css('padding', '0px'); 
					//if ( $target.attr('id') != $item.attr('id') &&  $target.nextAll('li').filter(':visible').length == 0) $('ul.menu-item').append('<li class="blank"><span class="drag-title"></span></li>');
				});
				
				// unless the moving item is the first item, always add a target to the front of the list
				//if ( $('ul.menu-item li').not('#new-menu, #side-menu').eq(0).attr('id') != $item.attr('id') ) {$('#new-menu').after('<li class="blank parent"><span class="drag-title"></span></li>');
				if ( $('#new-menu').next().attr('id') != $item.attr('id') ) 
				{
					$('#new-menu').css('padding-bottom', '0px').after('<li class="blank parent"><span class="drag-title"></span></li>');
				
				}
				if ( $('#side-menu').next().attr('id') != $item.attr('id') ) $('#side-menu').css('padding-bottom', '0px').after('<li class="blank parent"><span class="drag-title"></span></li>');
				
				// make all dynamically generated blank fields droppable and set their height
				$('li.blank').each(function(index){
					makeDroppable( $(this) );
					//$(this).css('height', '12px');
				});
				
				
			
			},
			stop:  function(event, ui){
				$('#addPage_container').addClass('not-dragging');
				// wait a few micros before testing to see if it is being dropped and calculated (draggable.stop fires before droppable.drop)
				

				setTimeout(function(){if ( ! $('#addPage_container').hasClass('dropping') ) {$('li.blank').remove();$('ul.menu-item li').css('padding-bottom','12px'); }},500);
			},
			helper: function (e) {
			var title = $(this).find('span').html()
			//title = $.trim(title).substring(0,11)+'...';
				return $("<div id='dragHelper'>"+ title +"</div>");
			},
			cursor: "pointer",
			cursorAt: {left: 5},
			
			
			addClasses: false,
			delay: 100,
			scroll: true
		});
		
	//	$dragitem.each(function(){
	//		if ($(this).parent().children('li').attr('id') == 'side-menu') $(this).draggable( "option", "cursorAt", { bottom: -20});
	//		//console.log ($(this).parent().children('li').attr('id') );
	//	});
		
	}
	
	
	makeDroppable( $("#side li").not('#side-menu') );
	makeDroppable( $("#top li").not('#new-menu, #side-menu') );
	
	makeDroppable( $("ul.menuless") )
	 
	function makeDroppable($dropitem)
	{
		$dropitem.droppable({
			accept: function ($item){
				var $target = $(this);
				
				// blank spots are always droppable
				if ( $target.hasClass('blank') ) return true;
				
				// menuless items can't target menuless field
				if ( $target.hasClass('menuless') && $item.parent().hasClass('menuless') ) return false;
				
				// menuless will accept any number of levels of children (need to warn that all children will be dropped)
				if ( $target.hasClass('menuless') || $target.attr('id') == 'new-menu' || $target.attr('id') == 'side-menu') return true;
								
				// grandchildren don't accept anything
				if ( $target.hasClass('grandchild') ) return false;
				
				// items don't target any descendant
				if ( isAnyDescendant ( $target, $item ) ) return false;
				
				// items don't target immediate parents
				if ( isImmediateParent( $target, $item ) ) return false	
							
				// children only accept elements with no children
				if ( $target.hasClass('child') &&  childDepth( $item ) == 0 ) return true;
				
				// parents accept elements with up to 1 child
				if ( $target.hasClass('parent') && childDepth( $item ) <= 1 ) return true;
				
				return false;
			},
			tolerance: 'pointer',
			over: function (event,ui){
				$item = $(this);
				if ($item.hasClass('blank') ) {
					$item.css('height','23px');
					$item.find('span').addClass('drag-title').stop().animate({height: '23px'},'fast');
				}	
			},
			out: function (event, ui) {
				$item = $(this);
				if ($item.hasClass('blank') ) $item.stop().animate({height: '12px'},'fast').find('span').removeClass('drag-title');
			},
			activeClass: 'active-drag',
			hoverClass:'hover-drop',
			drop: function (event, ui) {
				// indicate that drop sequence has started and that changes have been made
				$('#addPage_container').addClass('unsavedChanges dropping');
			
				var $item = ui.draggable;
				var $target = $(this);
				//console.log('target: ' + $target.html() );
				//destroy all blanks first except the target (if it is blank)
				$('li.blank').not($target).remove();
				//$('li.blank').each(function(){
				//	console.log( 'remaining targets: ' + $(this).html());
				//});
				
				//open the two branches if they are closed
				if ($target.hasClass('expandable') ) expandCollapse($target, false);
				if ($item.hasClass('expandable') ) expandCollapse($item, false);

				//	confirm before dropping if it has children and the target is menuless
				if ( $target.hasClass('menuless') &&  childDepth($item) > 0 ) {
					confirm('All sub-menus will become menuless as well.<br/> Do you wish to continue?', function() {return dropLineItem($item, $target)})
				}
				else dropLineItem($item, $target);

				// cleanup: sometimes lingering ui classes are left behind
				$('li').removeClass('active-drag');
				// signal that dropping has completed
				$('#addPage_container').removeClass('dropping');
			}
		});
	}

	// when moving to a blank target, the item actually replaces the target
	function moveBlank($item, $target)
	{
		$item.fadeOut(function(){
			//console.log('target of moveafter: ' + $target.html() );
			$target.replaceWith($item);
			if (! $item.hasClass('grandchild')){
				makeDroppable($item);
				$item.find('a.addPage').removeClass('addDisabled').children().attr('src', 'images/plus.png' );
			}
			$item.fadeIn();   		   
		});
		
	}
	
	// all the item's children are moved this way. The item itself is only moved this way if it is being added to another item's lineage (not placed into a blank position)
	// assign the item a new home after the target
	function moveAfter($item, $target){
		$item.fadeOut(function(){
			//console.log('target of moveafter: ' + $target.html() );
			$target.after($item);
			if (! $item.hasClass('grandchild')){
				makeDroppable($item);
				$item.find('a.addPage').removeClass('addDisabled').children().attr('src', 'images/plus.png' );
			}
			$item.fadeIn();   		   
		});
	}

	// append to the end of the menuless list
	function moveAppend($item, $target){
		$item.fadeOut(function(){
			$item.draggable("destroy");
			$item.droppable("destroy");
			$item.attr('class', 'parent');
			$item.find('a.addPage').addClass('addDisabled').children().attr('src', 'images/plus_no_select.png' );
			$target.append($item);
			makeDragable( $item );
			$item.fadeIn();     		   
		});
	}
	
});
