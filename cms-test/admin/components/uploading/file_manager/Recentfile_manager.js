var startTime = $.now();

function clearLoader(el)
{


	
		$(el).css("background-image", "none");
		$(el).css("vertical-align", "middle");
		
}

$(window).load(function(){

$('#loading_files').hide();
$('#files').show();

});
$(document).ready(function () {

var args = top.tinymce.activeEditor.windowManager.getParams(),
	window = args.window,
	input = args.input,
	type = args.type,				//image/file/media (from tinyMCE)
	file_name = args.file_name,		// folder name: content/members/ecom
	page_type = args.page_type,		// page_type : dflt/members/ecom
	target_id = args.target_id,
	$subfolders = $('#subfolders li'),
	subfolders = Array(),
	admin_url = $('#adminurl').val(),
	uploadurl = $('#uploadurl').val(),
	knownTypes 	= {'default' : 'new.png','tar' : 'Archive.png', 'doc' : 'doc.png', 
		'docx' : 'doc.png', 'avi' : 'avi.png', 'css' : 'css.png', 
		'eps' : 'eps.png', 'flv' : 'fla.png', 'html' : 'html.png', 
		'htm' : 'html.png', 'mp3' : 'mp3.png', 'pdf' : 'pdf.png', 
		'ppt' : 'ppt.png' , 'pps' : 'ppt.png', 'ppt' : 'pptx.png' , 
		'txt' : 'text_doc.png' , 'log' : 'text_doc.png' , 
		'msg' : 'text_doc.png' , 'odt' : 'text_doc.png' , 
		'pages' : 'text_doc.png' , 'rtf' : 'text_doc.png' , 
		'tex' : 'text_doc.png', 'wpd' : 'text_doc.png' , 
		'wps' : 'text_doc.png', 'wav' : 'wav.png', 'xls' : 'xls.png', 
		'xlsx' : 'xls.png', 'xlr' : 'xls.png', 'zip' : 'zip.png', 
		'zipx' : 'zip.png', 'aif' : 'music_doc.png', 'iff' : 'music_doc.png', 
		'm3u' : 'music_doc.png', 'm4a' : 'music_doc.png', 'mid' : 'music_doc.png', 
		'mpa' : 'music_doc.png', 'ra' : 'music_doc.png', 'wma' : 'music_doc.png', 
		'mov' : 'mov.png', '3g2' : 'mov.png', '3gp' : 'mov.png', 'asf' : 'mov.png', 
		'asx' : 'mov.png', 'flv' : 'mov.png', 'mp4' : 'mov.png', 'mpg' : 'mov.png','webm' : 'mov.png', 
		'rm' : 'mov.png', 'srt' : 'mov.png', 'swf' : 'mov.png', 'vob' : 'mov.png', 
		'wmv' : 'mov.png', 'max' : 'image_doc.png', 'obj' : 'image_doc.png', 
		'bmp' : 'image_doc.png', 'dds' : 'image_doc.png', 'psd' : 'image_doc.png', 
		'pspimage' : 'image_doc.png', 'tga' : 'image_doc.png', 'thm' : 'image_doc.png', 
		'tif' : 'image_doc.png', 'yuv' : 'image_doc.png', 'ai' : 'image_doc.png', 
		'eps' : 'image_doc.png', 'ps' : 'image_doc.png', 'svg' : 'image_doc.png', 
		'7z' : 'Archive.png', 'deb' : 'Archive.png', 'gz' : 'Archive.png', 
		'pkg' : 'Archive.png', 'rar' : 'Archive.png', 'rpm' : 'Archive.png', 
		'sit' : 'Archive.png', 'sitx' : 'Archive.png', 'gz' : 'Archive.png', 
		'tar.gz' : 'Archive.png'};

$subfolders.each(function(){
	var	key = $(this).attr('data-key'),
		value = $(this).text();
	subfolders.push({'key':key,'value':value});
});		
$('#confirm').jqm({overlay: 60, modal: true, trigger: false, target:false});	
writeMessage("<strong>Notifications and Error Messages</strong>",'');

$('#files').on('click', '.subfolders li span', function(){

	var subfolder = $(this).closest('li').attr('data-subfolder') + '/',
		$file_wrap = $(this).closest('.file_wrap'),
		filename = $file_wrap.attr('data-name'),
		alt = $file_wrap.attr('data-alt');
	if(subfolder=='original') subfolder = '';
	top.tinymce.activeEditor.windowManager.getParams().oninsert(uploadurl+subfolder+filename, alt);
	top.tinymce.activeEditor.windowManager.close();
});

$('#files').on('click', '.isFile .img_wrap', function(){

	var	$file_wrap = $(this).closest('.file_wrap'),
		filename = $file_wrap.attr('data-name'),
		alt = $file_wrap.attr('data-alt');
	top.tinymce.activeEditor.windowManager.getParams().oninsert(uploadurl+filename, alt);
	top.tinymce.activeEditor.windowManager.close();
});

	
$("a.grouped_elements").fancybox({
	'transitionIn'	:	'elastic',
	'transitionOut'	:	'elastic',
	'speedIn'		:	600, 
	'speedOut'		:	200
});
	
$('#files').on('click', '.name', function (){
	var $name_mask = $(this).next(),
		$name = $(this);

	$name_mask.val($name.html());
	$name.hide();
	$name_mask.show();
	$name_mask.focus();
});
	
$('#files').on('click', '.delete_button', function (){
	var that = this;
	confirm('Remove this file?', function () { deleteFile(that); } );	
});	

// clear the current error message when upload location pressed
$('#upload_btn').click(function(){
	$("#image_msg").hide();
});
$('#upload_btn').change(function(){
	var thisfile = $(this).val().split("\\").pop();
	$("#fake-file").html(thisfile);//.split("\").pop();' 
});
/*	
$('#upload_btn').fileupload({
		dataType: 'json',
		done: function (e, data) {
			var result = data.result.files['0'],
				file_ext = getFileExtension(result.name),
				isImage = typeof result.fullsize_url !== "undefined",
				oversizedImage = !isImage && (file_ext == 'jpg' || file_ext == 'jepg' || file_ext == 'png' || file_ext == 'gif'),
				isMedia = (file_ext == 'avi' || file_ext == 'wmv' || file_ext == 'flv' || file_ext == 'mpg' || file_ext == 'mp4' || file_ext == 'webm'),
				$uploadLI = $('#up_files span[id="up_'+data.files['0']['name']+'"]').closest('li');		
			
			// upload done, get rid of individual progress bar
			$uploadLI.remove();
			
			if( typeof result.error !== "undefined") 
				writeMessage(result.name + ": " +result.error, 'error');
			else
			{
				var localUpload = 'uploads/'+ file_name,
					resultLI = '';

				if(oversizedImage)
					writeMessage("Oversized Image Uploaded to Files: "+result.name, 'error');
				else if(type == 'image')
				{
					if(isImage) writeMessage("Image Uploaded: "+result.name, '');
					else if(isMedia) writeMessage("Uploaded to Media Section: "+result.name, '');
					else writeMessage("Uploaded to Files: "+result.name, '');
				}
				else if(type == 'media')
				{
					if(isImage) writeMessage("Uploaded to Images: "+result.name, '');
					else if(isMedia) writeMessage("Uploaded Media: "+result.name, '');
					else writeMessage("Uploaded to Files: "+result.name, '');
				}
				else writeMessage("File Uploaded: "+result.name, '');
				
				if(isImage && type != 'media') 
				{
resultLI  = "<div class=\"file_wrap\" data-file_id=\""+ result.id+"\" data-type=\"image\" data-name=\""+result.name+"\" data-alt=\""+result.name+"\">";
resultLI += "  <div class=\"button_group\">";
resultLI +=	"    <img class=\"delete_button\" src=\""+admin_url+"images/delete.png\" alt=\"Delete\" >";
resultLI += "    <ul class=\"subfolders\" style='left:20px;'>";
$.each(subfolders, function (key,data){
var subfolder = data.key,
	subfolderPretty = data.value;
resultLI += "      <li data-subfolder=\""+subfolder+"\">";
resultLI += "        <a class='grouped_elements' rel='group_"+result.id+"' href='"+uploadurl+subfolder+"/"+result.name+"'>";
resultLI += "          <img src=\""+admin_url+"images/view_icon.png\" alt='View'/>";
resultLI += "        </a>";
resultLI += "        <span>"+subfolderPretty+"</span>";
resultLI += "      </li>";
});
resultLI += "    </ul>";
resultLI += "  </div>";
resultLI += "  <div class=\"img_wrap\">";
resultLI += "    <span></span>";
resultLI += "    <img style='background-image: none;vertical-align: middle;' src=\""+uploadurl+"thumb/"+result.name+"\" alt=\"IMAGE\" >";
resultLI += "  </div>";
resultLI += "  <p class=\"name\">"+result.name+"</p>";
resultLI += "  <input type=\"text\" class=\"name_mask\" value=\"\">";
resultLI += "</div>";
						
					alphabetInsert(result.name, resultLI);
					//clear the no files found message
					$('#nofiles').hide();
					
					$("a.grouped_elements").fancybox({
						'transitionIn'	:	'elastic',
						'transitionOut'	:	'elastic',
						'speedIn'		:	600, 
						'speedOut'		:	200
					});
				}
				else if( !isImage && ( (type == 'media' && isMedia) || type == 'file' ))
				{
					iconName = file_ext in knownTypes ?  knownTypes[file_ext] :  knownTypes['default'];
				
resultLI  =	"<div class='file_wrap isFile' data-file_id='"+result.id+"' data-type='file' data-name=\""+result.name+"\" data-alt=\""+result.name+"\">";
resultLI +=	"  <div class='button_group'>";
resultLI +=	"    <img class='delete_button' src='"+admin_url+"images/delete.png' alt='Delete'>";
resultLI +=	"  </div>";
resultLI +=	"  <div class=\"img_wrap\">";
resultLI +=	"    <span></span>";
resultLI +=	"    <img src='images/page_icons/"+iconName+"' style='background-image: none;vertical-align: middle;' alt=\"FILE\">";
resultLI +=	"  </div>";
resultLI +=	"  <p class='name'>"+result.name+"</p>";
resultLI +=	"	<input type=\"text\" class=\"name_mask\" value=\"\">";
resultLI +=	"</div>";
				
					alphabetInsert(result.name, resultLI);
					//clear the no files found message
					$('#nofiles').hide();
				}	
				
				$('.name').tipTip({
					defaultPosition:'top',
					content:"Click to Change Alt Tag"
				});
			}	
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#total_progress .bar').css(
				'width',
				progress + '%'
			);
		},
		dropZone: $('body'),
		formData: {
			file_name: file_name,
			page_type: page_type,
			target_id: target_id
		},
		start: function (e){
			$('#total_progress').show();

		},
		stop: function (e) {
			$('#total_progress').css('width',0).hide();

			$("#fake-file").html('Click to Select a File');
		},
		maxChunkSize: 1000000
	}).bind('fileuploadadd', function (e, data) {
		var val = data.files[0];
		$('#up_files').prepend('<li><span class="up_name">' + val.name + '</span><div class="up_bar"><span id="up_' + val.name + '" class="loading">0%</span></div></div></li>')
	}).bind('fileuploadprogress', function (e, data) {

		if (data.lengthComputable) {
			var loader = $('#up_files span[id="up_'+data.files[0]['name']+'"]');
			var progress = Math.min( parseInt(data.loaded / data.total * 100, 10), 100);

			if(progress < 99)	loader.html(progress + '%');
			else loader.html('Resizing');
			
			loader.parent().css('width', progress*.9 + "%");
		}    
	});	
	
*/	

function doTipTips(){
	// download button
	$('.name').tipTip({
		defaultPosition:'top',
		content:"Click to Change Alt Tag"
	});
}	
	
function alphabetInsert(resultName, resultLI)
{
	var $existingFiles = $('#files .file_wrap');
	
	if($existingFiles.length)
	{
		$existingFiles.each(function(index){
			if($(this).attr('data-name') > resultName )
			{
				$(this).before(resultLI);
				return false
			}
			if(index+1 == $existingFiles.length) $('#files').append(resultLI); 
		});
	}else $('#files').prepend(resultLI);	
}
	
function deleteFile(that)
{
	var file_name = $(that).closest('#files').attr('data-page_type'),
		file = $(that).closest('.file_wrap').attr('data-name');
	
	file_name = file_name == 'dflt' ? 'content' : file_name;
	$.ajax({
		url: "js/jquery.fileupload/upload_handler.php",
		type: 'GET',
		data: {
			_method : "DELETE", 
			file_name: file_name,
			file: file
		},
		//beforeSend: function(){$loadingImage.show();},
		error: function () { 
			writeMessage('Could not delete file', 'error');
		},
		success: function (strResponse) 
		{	
			resp = $.parseJSON(strResponse);
			if ( typeof resp.success === "undefined" || !resp.success)
			{
				writeMessage('Could not delete file', 'error');
			}
			else // success: update the images
			{	
				writeMessage('Successfully Deleted '+file, '');
				$(that).closest('.file_wrap').fadeOut('slow',function(){$(that).closest('.file_wrap').remove();});
			}
		}
	});
}	
	
var processingUpdateAlt = false,
	activeAltFocus = false;
$('#files').on('blur', '.name_mask',function(){ 
	activeAltFocus = false;
	updateAlt($(this)); 
});
$('#files').on('focusin', '.name_mask',function(){ 
	activeAltFocus = $(this); 
});
$(document).keypress(function(e) {
	if(e.which == 13) {
		if(typeof activeAltFocus === 'object') updateAlt(activeAltFocus);
	}
});


	
function writeMessage(message,type)
{
	var error_class = type == 'error' ? 'error' : '',
		$messages = $('#messages');

	if($('#messages li').length > 5) $('#messages li:first-child').next().remove();
	$messages.append('<li class="'+error_class+'">'+message+'</li>');
}

function updateAlt($name_mask)
{
	var alt = $name_mask.val(),
		$name=$name_mask.prev(),
		$file_wrap = $name_mask.closest('.file_wrap'),
		type = $file_wrap.attr('data-type'),
		file_id = $file_wrap.attr('data-file_id');
	// prevent double sends
	if(processingUpdateAlt) return;
	if(alt==$name.html())
	{
		activeAltFocus = false;
		$name_mask.hide();
		$name.show();
		return;
	}
	processingUpdateAlt = true;

	$.ajax({
		url:"components/uploading/ajax.php",
		type: 'POST',
		data: {
			option:	'update_alt',
			alt:	alt,
			file_id:file_id,
			type:	type
		},
		error: function (){
			writeMessage("Error updating Alt tag", 'error');
			processingUpdateAlt = false;
			activeAltFocus = $name_mask;
			$name_mask.focus();
		},
		success: function (strResponse)
		{
			if (strResponse && strResponse  != 'success')
			{
				writeMessage(strResponse, 'error');
				activeAltFocus = $name_mask;
				$name_mask.focus();
			} else {
				writeMessage('Changed Alt Tag to: '+alt, 'error');
				$name_mask.hide();
				$name.html(alt);
				$file_wrap.attr('data-alt', alt);
				$name.show();
				activeAltFocus = false;
			}
			processingUpdateAlt = false;
		}
	});
}
});
