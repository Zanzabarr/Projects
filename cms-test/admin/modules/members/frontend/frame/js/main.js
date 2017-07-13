$(function () {
    'use strict';
	var root = $('#root').val(),
		framePath = $('#framePath').val(),
		securePath = $('#securePath').val(),
		isChrome = /chrome/.test(navigator.userAgent.toLowerCase());

	// if this is not in a frame, provide a button to go back to normal view
	if (window.location == window.parent.location)
	{
		$('body').append(
			$('<a />', {
				'class' : "file_btn",
				'id': 'go_main',
				'href' : root + "members/ftp#content",
				
			}).html('Return To Site')
		);
	}
	
	//disable form submissions since all is handled in ajax
	$('form').submit(function(e){e.preventDefault()});
	
	// prevent all anchor clicks frm receiving click
	//$('#drop, #sorted-folders').on('click', 'a', function (e) {e.preventDefault();});
	// make files dblclickable for upload
	$('#drop').on('dblclick', 'a:first-child', function (e) {window.location.replace($(this).attr("href"));} );
	
    // Initialize the jQuery File Upload widget:
    $('.fileupload').fileupload({
		maxChunkSize: 1000000 ,
		url: 'upload_handler.php',
		dropZone: '#fileArea',
		redirect: window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
		
	}).bind('fileuploadadd', function (e, data) {
		var val = data.files[0];
		$('#drop ul').prepend('<li unselectable="on"><div class="outer"><div class="inner"><div id="up_' + val.name + '" class="loading">0%</div><div class="up_bar"></div></div></div><span>' + val.name + '</span><span>' +getFileSize( val.size ) + '</span></li>')
	}).bind('fileuploadprogress', function (e, data) {

		if (data.lengthComputable) {
			var loader = $('#drop div[id="up_'+data.files[0]['name']+'"]');
			var progress = Math.min( parseInt(data.loaded / data.total * 100, 10), 100);

			loader.html(progress + '%');
			loader.next().css('height', progress*0.9 + "%");
		}    
	}).bind('fileuploaddone', function (e, data) {
		var results = $.parseJSON(data.result),
			fileInfo = results['files']['0'],
			$li = $('div[id="up_'+data.files['0']['name']+'"]').closest('li');

		if ( fileInfo['error']) {
			writeError("Ajax error encountered: '" + fileInfo['error'] + "'");
			
		} else {
			$li.replaceWith( buildLi(fileInfo.name, fileInfo.size, fileInfo.url, fileInfo.delete_url) );
			// add chrome drag handler
			$('.dragoff').each(function(e){setDragoff($(this));});
		}
	});

    // Enable iframe cross-domain access via redirect option:
    $('.fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );
	
	$(".upDown input").on("change", function(e) {
		var $other_button = $(this).closest('th').siblings().find('.upDown'),
			cur_selected_path = $('#sorted-folders .selected-folder').data('path'),
			sort_order = $('#head_name input').prop('checked') ? 'asc' : 'desc',
			sort_by = $('#head_modified input').prop('checked') ? 'name' : 'date_updated';
		
		// don't change the checkbox if it is currently inactive
		if($(this).parent().hasClass('inactive'))
		{
			$(this).prop('checked', !$(this).prop('checked'));
		}
		

		// set both to inactive
		$(".upDown").removeClass('inactive');
		
		// highlight active
		$other_button.addClass('inactive');

		// redisplay the tree
		
		displayFolderTree(sort_order, sort_by, false, cur_selected_path);	
	});
	
	
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});		
	$('.fileupload').bind('dragover', function (e) {
		var dropZone = $(this),
			timeout = window.dropZoneTimeout;
		if (!timeout) {
			dropZone.addClass('in');
		} else {
			clearTimeout(timeout);
		}
		window.dropZoneTimeout = setTimeout(function () {
			window.dropZoneTimeout = null;
			dropZone.removeClass('in');
		}, 100);
	});

	// file delete
	$('#drop').on('click', '.delFile', function (e){var $that=$(this);deleteFile(e, $that) } );
	
	// folder delete
	$('#sorted-folders').on('click', '.deleteFolder', function (e){var $that=$(this);deleteFolder(e, $that) } );

	
	//--------------------Folder Actions------------------------//
	
	// get the folder tree, with file area updating enabled
	displayFolderTree('asc', 'name', true, false);
	
	
	
	// open/close folders
	$('#sorted-folders').on('click', 'tr', function (e) { 
		
		var target = (e.target.tagName).toLowerCase();
		
		if (target != 'input' && target != 'a'){
			e.preventDefault();
			if (target != 'span')loadFileArea( $(this) );
			else toggleFolder( $(this) );
		}
	});
	
	//rename file: start it
	$('#drop').on('dblclick', 'span.rename_file_mask', function (e) {
	if ($('#drop').hasClass('readOnly')) return false;
		var oldname = $(this).text(),
			$mask = $(this),
			$input = $(this).next(),
			$cover = $('<div>').attr('id','fieldCover').appendTo('body');
		//create the input
		$mask.hide();
		$input.show();
		$input.focus();
	}); 
	// rename file: send it
	$('#drop').on('focusout', '.rename_file', function (e) {
		var $input = $(this),
			msg = '',
			$mask = $input.prev(),
			$cover = $('#fieldCover'),
			maskText = $.trim($mask.text()),
			maskExt = maskText.lastIndexOf(".") ? maskText.substr(maskText.lastIndexOf(".") + 1) : '',
			inputVal = $.trim($input.val()),
			inputExt = inputVal.lastIndexOf(".") ? inputVal.substr(inputVal.lastIndexOf(".") + 1) : '',
			tooShort = inputVal.length < 5,
			path = $('.fileinputs').attr('title');
				
		if(inputExt == '') msg = "Can't end with '.'";
		if(inputExt == inputVal) inputExt = '';
		if(maskExt == maskText) maskExt = '';
		if(tooShort) msg = "Filename must be at least 4 characters long";

		if ( inputVal == maskText || inputVal == '' || msg ||   ( inputExt != maskExt && !confirm("Changing the extension may make this file unusable. Continue anyway?") ))
		{
			//end by closing everything
			if(msg) writeError(msg);
			$input.hide().val(maskText);
			$mask.show();
			$cover.remove();
			return false;
		}
		
		$.post('asynch/frontend.php', { 
			action: 'rename_file', 
			from: maskText,
			to: inputVal,
			path: path
			}, function (data) {
				if(data.newName)
				{
					$input.hide().val(data.newName);
					$mask.text(data.newName).show();
					$cover.remove();
				}
				else 
				{
					writeError(data.message);
					$input.hide().val(maskText);
					$mask.show();
					$cover.remove();
				}
			}, 'json'
		);
	});

	//new folder: start it
	$('#sorted-folders').on('click', '.add_folder', function (e) {
		var $mask = $(this).siblings('a').andSelf(),
			$input = $(this).next(),
			$cover = $('<div>').attr('id','fieldCover').appendTo('body');

		//create the input
		$mask.addClass('hide');
		$input.show();
		$input.focus();
	});
	
	$('#sorted-folders').on('focusout', '.add_folder_input', add_folder );	

	
	//------------------------ FUNCTIONS-------------------------//
	function deleteFile (e, $that) 
	{
		e.preventDefault();

		if( confirm('Are you sure you want to delete "' + $that.data('name') + '"' ) )
		{
			$.post($that.attr('href'), function (data) {
				if(data.success){
					$that.closest('li').fadeOut('slow',function(){$(this).remove()});
				}
				
			}, 'json');
		}
		
	}
	
	function deleteFolder (e, $that) 
	{
		e.preventDefault();
		
		var $dataSrc = $that.closest('tr'),
			name = $dataSrc.data('name'),
			path = $dataSrc.data('path'),
			parentPath = $dataSrc.data('parent-path');

		if( confirm('Are you sure you want to delete "' + name + '" and all contents and subfolders?' ) )
		{
			$.post('asynch/frontend.php', { 
			action: 'delete_folder', 
			path: path
			},  function (data) {
				if(data.success){
					var sort_order = $('#head_name input').prop('checked') ? 'asc' : 'desc',
						sort_by = $('#head_modified input').prop('checked') ? 'name' : 'date_updated',
						new_path = parentPath,
						path_selector = '#sorted-folders tr[data-path="'+parentPath+'"]';
		
					// redisplay the tree
					displayFolderTree(sort_order, sort_by, path_selector ,new_path );
				}
				
			}, 'json');
		}
		
	}
	
		//------------------------ FUNCTIONS-------------------------//
	function deleteFile (e, $that) 
	{
		e.preventDefault();

		if( confirm('Are you sure you want to delete ' + $that.data('name') ) )
		{
			$.post($that.attr('href'), function (data) {
				if(data.success){
					$that.closest('li').fadeOut('slow',function(){$(this).remove()});
				}
				
			}, 'json');
		}
	}
	
	
	
	function add_folder() {

		var $input = $(this),
			msg = '',
			$mask = $input.prev().siblings('a').andSelf(),
			$cover = $('#fieldCover'),
			inputVal = $.trim($input.val()),
			path = $input.closest('tr').data('path');

		if (  inputVal == '')
		{
			//end by closing everything
			$input.hide().val('');
			$mask.removeClass('hide');
			$cover.remove();
			return false;
		}
		
		$.post('asynch/frontend.php', { 
			action: 'create_folder', 
			name: inputVal,
			path: path
			}, function (data) {
			
				if(typeof data.newName !== 'undefined')
				{
					var sort_order = $('#head_name input').prop('checked') ? 'asc' : 'desc',
						sort_by = $('#head_modified input').prop('checked') ? 'name' : 'date_updated',
						new_path = path + "/" + data.newName,
						path_selector = '#sorted-folders tr[data-path="'+new_path+'"]';
		
					// redisplay the tree
					displayFolderTree(sort_order, sort_by, path_selector ,new_path );	
					
					$mask.removeClass('hide');
					$input.hide().val('');
					$cover.remove();
				}
				else if ( typeof data.merge !== 'undefined')
				{
					writeMessage('Folder already exists, choose another name.')
					$input.val(data.merge.name);
					$input.focus();
				}
				else if (typeof data.safename !== 'undefined')
				{
					// safename is given if the user defined name has been altered
					writeMessage(data.message);
					$input.val(data.safename);
					$input.focus();
				}
				else 
				{
					var error = typeof data.error !== 'undefined' ? data.error : 'Error Creating Folder';
					writeError(data.error);
					$input.hide().val('');
					$mask.removeClass('hide');
					$cover.remove();
				}
			}, 'json'
		);
	
	}
	
	
	function toggleFolder($tr)
	{
		// toggle the children
		// if the tr has children
		if($tr.hasClass('has-sub'))
		{
			// find if the children are currently open
			var showThem = $tr.next().filter(":hidden").length;
			
			if(showThem) $tr.addClass('closable');
			else $tr.removeClass('closable');
			
			$tr.nextAll('tr').each(function(i,e){
				// if the $tr path is the beginning of this path, it is a child and must be toggled
				if ( $(this).data('path').indexOf($tr.data('path')) == 0 )
				{
					// on show, only show immediate children, on hide: hide them all
					if (showThem && $(this).data('parent-path') == $tr.data('path') ) $(this).show();
					else $(this).hide().removeClass('closable');
				}
			}); 
		}
	}
	
	function getFileSize(size)
	{
		if (size > 1024*1024) size = Math.round(size/(1024*1024)*Math.pow(10,21)/Math.pow(10,19))/100 + " MB";
		else size = Math.round(size/1024*Math.pow(10,21)/Math.pow(10,19))/100 + " KB";
		return size;
	}
	
	function isImage(name)
	{	
		var arType = name.split('.');
		var lastEl = arType.length - 1;
		if(lastEl < 1) return false;
		var type = arType[lastEl];
		return type == 'png' || type == 'jpg' || type == 'jpeg' || type == 'gif' ; 
	}
	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@  BUILD LI  @@@@@@@@@@@@@@@@@@@ //
	function buildLi(name,size,url,deleteUrl)
	{
		var displaysize = getFileSize( size ),
			validImage = isImage(name),
			displayurl = '../images/Text-Document.png';
					
		if(validImage && size < 1024*1024) displayurl = "asynch/serve_image.php?path=" + url;
		else if (validImage) displayurl = '../images/image_doc.png';
/*			
		return '<li><a href="asynch/serve_image.php?path=' + url + '"><div class="outer"><div class="inner"><img src="' + displayurl + '" onerror="this.onerror=null;this.src=\'../images/image_doc.png\'"></div></div></a><span class="rename_file_mask">'+name+'</span><input class="rename_file" type="text" value="' + name + '"/><span>' + displaysize + '</span><a class="dragoff" draggable ="true" data-downloadurl="application/octet-stream:'+name+':'+framePath+'asynch/serve_image.php?path='+url+'" href="asynch/serve_image.php?path=' + url + '">'+ name +'</a></li>';
*/		
		var liImage = '<div class="outer"><div class="inner"><img src="' + displayurl 
						+ '" onerror="this.onerror=null;this.src=\'../images/image_doc.png\'"></div></div>',
			rename = '<span class="rename_file_mask" title="'+name+'">'+name+'</span><input class="rename_file" type="text" value="' + name + '"/>',
			dispSize = '<span>' + displaysize + '</span>',
			download = '<a class="dragoff" draggable ="true" data-downloadurl="application/octet-stream:'+name+':' + framePath 
						+ 'asynch/serve_image.php?path='+url+'" href="asynch/serve_image.php?path=' + url + '">'+ name +'</a>',
			del = '<a class="delFile" data-name="'+name+'" href="'+deleteUrl+'"></a>',
			copy = '';
		
		return '<li>' +liImage + rename + dispSize + download + del + copy + '</li>';
		
	}
	
	// Load existing files:
	// @@@@@@@@@@@@@@@@@@@@@@@@@@@@  LI DRAG & DROP  @@@@@@@@@@@@@@@@@@@ //
	function displayFiles(folder) 
	{
		$.getJSON('upload_handler.php', { folder: folder, file_name: 'ftp' },function (data) {

			if (data['files'] && data['files'].length) {
				
				var items = [];
				
				$.each(data['files'], function(key, val) {
					items.push( buildLi(val.name, val.size, val.url, val.delete_url) );
				});
				
				$('#drop').html(
					$('<ul/>', {
						html: items.join('')
					})
				);
				
				// make contents selectable
				$("#drop li").draggable({
					cancel:'.dragoff',
					helper:'clone',
					cursorAt: { top: 5, left:5},
					revert: 'invalid',
					start: function( event, ui ) {
						// disable transitions (dragging slows way down)
						ui.helper.css(  {
							'-moz-transition': 'none',
							'-webkit-transition': 'none',
							'-ms-transition': 'none',
							'-o-transition': 'color 0 ease-in',
							'transition': 'none' 
						});
					},
				});	
				
				// enable chrome dragging			
				$('.dragoff').each(function(e){setDragoff($(this));});
				
			} else $('#drop').html('<ul></ul>');
		});
	}
	
	// TODO : this is awful
	function writeError(msg)
	{
		$('#msg-box').text(msg);
	}
	function writeMessage(msg)
	{
		$('#msg-box').text(msg);
	}

	function openParentsRec(selected_path)
	{
		var $current = $('#sorted-folders tr[data-path="'+selected_path+'"]'),
			parentPath = $current.data('parent-path'),
			$siblings = $('#sorted-folders tr[data-parent-path="'+parentPath+'"]');
		// show current and its siblings
		$current.show().addClass('closable');
		$siblings.show();
		// move up a level
		if( $current.data('parent-path') ) openParentsRec(parentPath);
	}
	

	function invalidDragTarget($drag, $target)
	{	
		var dragIsFile = $drag.closest('#drop').length,
			targetReadOnly = $target.data('constraint') == 'read only';
		
		if(dragIsFile && targetReadOnly) return true;
		
		return (
			($drag.attr('data-path') 
				&& ($target.data('path').indexOf($drag.data('path')) == 0 
					|| $target.data('path') == $drag.data('parent-path') 
					)
			) || $target.data('path') == $('#upload_path').html()
		)
		
													
	}
	
	
	// if update_file_area is false, file area isn't updated
	// if update_file_area is true, updates file area using the first element on the list
	// if update_file_area is a selector, use it to find the desired file to update
	//		NOTE: must be a selector, not a jquery object since the object doesn't exist until after the tree is built
	function displayFolderTree(sort_order, sort_by, update_file_area, selected_path)
	{	
		$.getJSON('asynch/frontend.php', { action: 'get_tree', sort_order: sort_order, sort_by: sort_by}, function (data) {
			if ( data['error'] ) {
				writeError(data['error']);
			} else if ( data.folders ) {	
				displayFolders(data['folders'], 0);
				if (update_file_area) 
				{
					if (typeof update_file_area === 'boolean')
						loadFileArea($('#sorted-folders tr').first());
					else {
						loadFileArea($(update_file_area).first());
						// in case of error, try getting the first element again
						if($('#upload_path').text() == 'null') loadFileArea($('#sorted-folders tr').first());
					}
				}
				
				// initial display has children open, close them
				$('#sorted-folders tr').each(function (i,e){
					if($(this).data('parent-path') ) $(this).hide();
				});
				
				if(selected_path){	
					openParentsRec(selected_path);
					$('#sorted-folders tr').removeClass('selected-folder');
					// set selected
					$('#sorted-folders [data-path="' + selected_path + '"]').addClass('selected-folder');
					// open children
					$('#sorted-folders [data-parent-path="'+ selected_path +'"]').show();
					
				}
			}				
		}); 
	}	
	
	// @@@@@@@@@@@@@@@@   TR DRAG & DROP   @@@@@@@@@@@@@@@@@@@@@@@@@//
	function displayFolders(obj)
	{
		var $target = $('#sorted-folders');
		$target.html('');
		displayFoldersRec(obj, $target, 0, '');
		
		$('.dragoff').each(function(e){setDragoff($(this));});
	/*              // Currently disabled but ready to go
					// TODO prevent dragging if folder is read only
					// prvent dropping if folder is read only
					// Server side, write the code to do the move
	
		$('#sorted-folders tr').not('[data-parent-path=""]').draggable({
			cancel: 'a , input, .buttons',
			helper:  function(event) {
				return $('<span><span/>').attr('class', 'dragFolder').text('Move: ' + $(this).find('td').first().text() ).appendTo('body');
			},
			delay: 300,
			scroll: false,
			revert:'invalid'
		});
 */
		$('#sorted-folders tr').droppable({
			tolerance: 'pointer',
			greedy: false,
			over: function(event, ui){if (! invalidDragTarget(ui.draggable,$(this)) ) $(this).addClass('openFolder');},
			out: function( event ){$(this).removeClass('openFolder');},
			accept: function (draggable){
				return ! invalidDragTarget(draggable , $(this));
			},
			drop: function (e, ui) {
				$( this ).removeClass('openFolder');
				if (ui.draggable.attr('data-path')) 
				{	// move folders if confirmed
					if (confirm("Do you wish to move " + ui.draggable.children('span:first-child').text() + ' to: ' + $(this).data('path') + '?')) 
					{
//console						console.log('dropped folder on: '+$(this).data('path'));
						//displayFolderTree('asc', 'name', true, false);
						$.post('asynch/frontend.php', { 
							action: 'move_folder', 
							fromPath: ui.draggable.data('path'),
							toPath: $(this).data('path')
							}, function (data) {
								if (data.error) writeError(data.error)
								//else console.log('SUCCESS:maybe I should just redraw it');
						}, 'json');
					} 
				} else {
					$.post('asynch/frontend.php', { 
						action: 'move_file', 
						fromPath: $('.fileinputs').attr('title') + "/" + ui.draggable.find('span').first().text(),
						toPath: $(this).data('path')
						}, function (data) {
							if (data.error) writeError(data.error)
							else {
								ui.helper.remove();
								ui.draggable.remove();
							}
					}, 'json');
				}
			}
		});

	}
	
	// @@@@@@@@@@@@@@ Build TR @@@@@@@@@@@@@@@@@@@@ //
	function displayFoldersRec(obj, $target, depth, parent_path)
	{
		var closeclass = depth ? "closeFolder" : 'openFolder';
		
		$.each(obj, function(key, val) {
			if(typeof val.subfolder !== "undefined" ) { // object, call recursively			
				var subfolderSize = 0,
					$tr = $("<tr>", {
						'class':'closeFolder',
						'data-path': val.path,
						'data-parent-path': parent_path,
						'data-constraint': val.constraint,
						'data-name':val.name
					}).html(
						'<td unselectable="on" style="padding-left:'+depth*1.2+'em"><span class="folderImage"></span>' + val.name+'</td><td unselectable="on" class="lastModified" title="'+val.local_date+'">'+val.local_day+'</td>'
					),
					archiveZipHTML = val.constraint != 'write only' ? '<a class="dragoff zip" draggable ="true" title="Download Folder Contents" data-downloadurl="application/octet-stream:'+val.name+'.zip:'+framePath+'asynch/serve_image.php?path='+securePath+val.path+'" href="asynch/serve_image.php?path=' + securePath+val.path + '"></a><a class="dragoff archive" draggable ="true" title="Download All Subfolders" data-downloadurl="application/octet-stream:'+val.name+'_archive.zip:'+framePath+'asynch/serve_image.php?path='+securePath+val.path+'&all" href="asynch/serve_image.php?path=' + securePath+val.path + '&all"></a>': '',
					
					addFolderHTML = val.constraint != 'read only' ? '<a class="add_folder" title="Create a new folder in '+val.name+'"></a><input class="add_folder_input" type="text">' : '',
					
					delFolder = val.constraint == 'none' && parent_path ? '<a class="deleteFolder" title="Delete Folder:&quot;'+val.name+'&quot; and all contents including sub-folders." href="#"></a>' : '',
					
					$buttons = $("<span>", {
						'class':'buttons'
					}).html(addFolderHTML+archiveZipHTML+delFolder);
					
				$tr.children().last().prepend($buttons);
					
					
					
				$.each(val.subfolder, function (k, v){
					subfolderSize++;
					return false;
				});
				if (subfolderSize) {
					$tr.addClass('has-sub');
					$tr.appendTo($target)
					displayFoldersRec(val.subfolder,$target, depth+1, val.path );
				} else $tr.appendTo($target);
				
			} else {
			
			$target.append($tr);
			}
			
		});		
	}
			
		
	function loadFileArea ($tr)
	{
		var $sortedList = $('#sorted-folders tr'),
			$folder	= $tr.children('td').first(),
			$input = $('#fileArea .fakefile'),
			$inParent = $input.parent(),
			$realInput = $input.prev(),
			constraint = $tr.data('constraint'),
			constraintClass ='';
			
		if (constraint=='read only') constraintClass= 'readOnly';
		else if (constraint=='write only') constraintClass= 'writeOnly';	
		
		// set the selected tr
		$sortedList.removeClass('selected-folder');
		$tr.addClass('selected-folder');

		displayFiles($tr.data('path'));
		if($tr.data('constraint') == 'read only') 
		{
			$input.html('<span>Download From</span><span id="upload_path" title="'+$tr.data('path')+'">' + $tr.data('name') +'</span>');
			$realInput.prop('disabled', true);
		}
		else 
		{
			$input.html('<span id="upload_file_btn" class="file_btn">Upload Files</span><span>to:</span><span id="upload_path" title="'+$tr.data('path')+'">' + $tr.data('name') +'</span>');
			$realInput.prop('disabled', false);
		}

		$inParent.attr('title', $tr.data('path'));

		$('#drop').attr('class', constraintClass);

		$('.upload_path').text($folder.text()).attr('title', $tr.data('path'));
		//prepFolderCreate($folder.text(), $tr.data('constraint'))
		
		
		// reset the folder data
		$('.fileupload').fileupload({
			formData: {folder: $tr.data('path')}
		});
	}
	
	function prepFolderCreate(name, constraint)
	{
	/*	var $add_folder = $('#add_folder');

		if ( constraint == "read only" ){
			$add_folder.removeClass('valid').attr('title',name + "Branch is Read Only: Can't Create Folder");	
		}	else  	{
			$add_folder.addClass('valid').attr('title','');
		} 
*/	}

	// force dragged items to download in place (desktop)
	// currently only works in chrome
	function setDragoff($file)
	{ 
		if (isChrome)
		{
			var	file = $file[0],
				fileDetail = '';
			if(typeof file.dataset === "undefined") {
				// Grab it the old way
				fileDetail = file.getAttribute("data-downloadurl");
			} else {
				fileDetail = file.dataset.downloadurl;
			}
			file.addEventListener("dragstart",function(evt){
				evt.dataTransfer.setData("DownloadURL",fileDetail);
			},false);
		} else {
			// if this is a drop li, make it undraggable in 
			//if($file.parent('li').length) 
				$file.prop('draggable', false)
		}
	}
});
