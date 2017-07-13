
/////////////////////////////////////////////////////////
////               tinySTANDARD                      ////
/////////////////////////////////////////////////////////
// tinyMCE standard settings, applies to editors with no upload capability
// no image handling functions provided but media (with embed tab) and links are available
// this var is used in uploading component to set upload capable versions
var tinyStandard = {
    selector: ".mceEditor",
	document_base_url : config['site_path'],
	body_id : 'content_area',
	body_class: 'tinymce',
	content_css : "css/basic.css,css/tiny.css,http://fonts.googleapis.com/css?family=Open+Sans|Oswald",	
	importcss_append: true,
	image_advtab:true,
	resize: "both",
	autoresize_min_height: 100,
	object_resizing: false,
	// image & link list is set in uploader, list only appears if uploader present
	//menubar: false,
	toolbar_items_size: 'small',
 	image_dimensions: false,
	font_formats: "Oswald=Oswald,sans-serif;"+
        "Open Sans=Open Sans,Arial,sans-serif;"+
        "Thirsty Script=ThirstyScriptRegular,cursive",
	style_formats: [
		{title: "Headers", items: [
			{title: "Header 1", format: "h1"},
			{title: "Header 2", format: "h2"},
			{title: "Header 3", format: "h3"},
			{title: "Header 4", format: "h4"},
			{title: "Header 5", format: "h5"},
			{title: "Header 6", format: "h6"}
		]},
		{title: "Inline", items: [
			{title: "Bold", icon: "bold", format: "bold"},
			{title: "Italic", icon: "italic", format: "italic"},
			{title: "Underline", icon: "underline", format: "underline"},
			{title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
			{title: "Superscript", icon: "superscript", format: "superscript"},
			{title: "Subscript", icon: "subscript", format: "subscript"}
		]},
		{title: "Div", format: "div"},
		{title: "Paragraph", format: "p"},
		{title: "Italics", format: "italic"},
		{title: "Smaller", format: "p", classes:"smaller"}		
	],
	target_list: [
        {title: 'None', value: ''},
        {title: 'Same page', value: '_self'},
        {title: 'New page', value: '_blank'}
    ],
	rel_list: [
		{title: 'None', value: ''},
		{title: 'Force Download', value: 'force_download'}
	],
	image_class_list: [
		{title: 'None', value: ' '},
		{title: 'Half to the Left', value: 'half leftimg'},
		{title: 'Half to the Right', value: 'half rightimg'},
		{title: 'Third to the Left', value: 'third leftimg'},
		{title: 'Third to the Right', value: 'third rightimg'},
		{title: 'Centered with space on sides', value: 'center'}
	],
/*	image_class_list: [
		{title: 'None', value: ' '},
		{title: 'Width 75%', value: 'width75'},
		{title: 'Width 50%', value: 'width50'},
		{title: 'Width 33%', value: 'width33'},
		{title: 'Width 25%', value: 'width25'}
	],
	importcss_file_filter: "css/basic.css",
*/    plugins: [
        "advlist autoresize autolink lists link charmap print preview hr anchor",
        "searchreplace wordcount visualblocks code fullscreen",
        "table contextmenu paste importcss textcolor"
    ],
	toolbar1: "undo redo | bold, italic, underline, strikethrough alignleft aligncenter alignright alignjustify | bullist, numlist  outdent, indent | forecolor, backcolor | , searchreplace",
	toolbar2: "styleselect fontselect, fontsizeselect link, unlink  table  | code print, fullscreen visualblocks ",
	valid_elements : "*[*]",
	setup: function(ed) {
		var $curEditorData = $('#'+ed.id),
			jumpType = $curEditorData.attr('data-jump-type'),
			jump = $curEditorData.attr('data-jump');
		
		if(ed.id == 'newsletter_content')
		{
			ed.settings.remove_script_host = false;
			ed.settings.relative_urls = false;
		}
		
		if(typeof jump !== "undefined")
		{
			var tmpTip = "Toggle Site/CMS Editing";
			if(typeof jumpType !== "undefined")
			{
				if(jumpType == 'front') tmpTip =  "Jump to Site View";
				else if (jumpType == 'back') tmpTip =  "Jump to CMS View";
			}			
			// for development purposes, assume true for all cases
			ed.addButton('jump', {
				tooltip: tmpTip,
				text: 'Jump',
				onclick: function() {
				
						window.location = jump;

				}
			});
			ed.settings.toolbar1 = 'jump | ' + ed.settings.toolbar1 ;
		}
	}
}

/////////////////////////////////////////////////////////
////               tinyUPLOADABLE                    ////
///////////////////////////////////////////////////////// 
// set up the uploader/filebrowser functionality in tinyMCE
tinyUploadable = $.extend({}, tinyStandard); // from /admin/js/tiny_mce_settings.js

tinyUploadable.selector = ".mceUploadable";
tinyUploadable.plugins = [
	"advlist autoresize autolink lists link charmap print preview hr anchor",
	"searchreplace wordcount visualblocks code fullscreen",
	"table contextmenu paste importcss textcolor image_custom_scaling youtube"
];
tinyUploadable.toolbar2 = "styleselect fontselect, fontsizeselect | link, unlink  image,youtube table  | code print, fullscreen visualblocks ";
tinyUploadable.file_browser_callback = function(field_name, url, type, win){tiny_browser_callback(field_name, url, type, win)};

/////////////////////////////////////////////////////////
////               inlineSTANDARD                    ////
/////////////////////////////////////////////////////////
// includes save functions but no uploading
// requires:

inlineStandard = $.extend({}, tinyStandard); 
inlineStandard.selector = ".inlineStandard";
inlineStandard.plugins = [
	"advlist autoresize autolink lists link charmap print preview hr anchor",
	"searchreplace wordcount visualblocks code fullscreen",
	"table contextmenu paste importcss textcolor save"
];
inlineStandard.content_css = '';
inlineStandard.save_enabledwhendirty = true;
inlineStandard.save_onsavecallback = function(){tiny_save_callback()};
inlineStandard.toolbar1 = "save | code fullscreen visualblocks | forecolor, backcolor | link, unlink table    ";
inlineStandard.toolbar2 = "undo redo | bold, italic, underline | alignleft aligncenter alignright | bullist, numlist | searchreplace ";
inlineStandard.toolbar3 = "outdent, indent   | styleselect | fontselect fontsizeselect  ";
inlineStandard.inline = true;
inlineStandard.menubar = false;

/////////////////////////////////////////////////////////
////               inlineUploadable                  ////
/////////////////////////////////////////////////////////
inlineUploadable = $.extend({}, inlineStandard); 
inlineUploadable.selector = ".inlineUploadable";
inlineUploadable.plugins = [
	"advlist autoresize autolink lists link charmap print hr anchor",
	"visualblocks code searchreplace",
	"table contextmenu paste importcss textcolor save youtube image_custom_scaling"
];
inlineUploadable.toolbar1 = "save | code fullscreen visualblocks | forecolor, backcolor | link unlink image youtube table";
inlineUploadable.file_browser_callback = function(field_name, url, type, win){tiny_browser_callback(field_name, url, type, win)};


/////////////////////////////////////////////////////////
////                 INITIALIZE                      ////
/////////////////////////////////////////////////////////
// initialize backend dudes
tinymce.init(tinyUploadable);
tinymce.init(tinyStandard);

// initialize frontend editors
tinymce.init(inlineStandard);
tinymce.init(inlineUploadable);

function tiny_browser_callback(field_name, url, type, win)
{
	var title='',
		myWidth = 0,
		myHeight = 0,
		img_desc_field = field_name.replace('-inp',''),
		final_digit = parseInt(img_desc_field.substr(img_desc_field.length - 1, 1)),
		$curEditorData = $('#'+tinymce.activeEditor.id),
		
		page_type = $curEditorData.attr('data-upload-type'),/* Type of page: default,blog,ecom: used with target_id to grab all images associated with a page*/
		page_id_field = $curEditorData.attr('data-id-field'),
		page_id = $curEditorData.attr('data-id-val');
		
	if (type=='file') title = 'File Manager';
	else if(type=='media') title = 'Media Manager';
	else if(type=='youtube') title = 'YouTube Manager';
	else if(type=='image') title = 'Image Manager';
	else title = 'Ooops';

		
	// figure out img field placement
	img_desc_field = img_desc_field.substr(0, img_desc_field.length -1);
	final_digit += 1;
	img_desc_field = img_desc_field + final_digit;

	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	myWidth = myWidth*.6;
	myHeight = myHeight *.6;
	myWidth = myWidth < 600 ? 600 : myWidth;
	myHeight = myHeight < 300 ? 300 : myHeight;
	
	tinymce.activeEditor.windowManager.open({
		title: title,
		url: config['admin_url'] + "components/uploading/file_manager/file_manager.php?page_type="+page_type+"&page_id="+page_id+"&type="+type,
		width: myWidth,
		height: myHeight,
		resizable :'yes',
		maximizable : 'yes'
		}, 
		{
		oninsert: function(url,image_desc) {
			// figure out the img description from the field name
			win.document.getElementById(field_name).value = url;
			var field_label =$('#'+img_desc_field).siblings('label').html();
			if(field_label=='Image description')
				win.document.getElementById(img_desc_field).value = image_desc;
		},
		window : win,
		input : field_name,
		type : type,
		page_type: page_type,
		target_id: page_id,
		target_id_field : page_id_field
	});
}

function tiny_save_callback()
{
		var $curEditorData = $('#'+tinymce.activeEditor.id),
			tableName = $curEditorData.attr('data-table'),
			idField = $curEditorData.attr('data-id-field'),
			idVal = $curEditorData.attr('data-id-val'),
			insertField = $curEditorData.attr('data-field');
			
		
		$.ajax({
			url: config['admin_url']+"components/inline_editing/ajax.php",
			type: 'POST',
			data: {
				option : "front_edit",
				table_name : tableName, 
				id_field: idField,
				id_val: idVal,
				field: insertField,
				tiny_contents : tinymce.activeEditor.getContent()
			},
			//beforeSend: function(){$loadingImage.show();},
			error: function () { 
				$('#inline_edit_message').html('Error saving, changes not saved');
			},
			success: function (strResponse) 
			{	
				tinymce.activeEditor.setProgressState(false);
				if(!strResponse)
				{
					$('#inline_edit_message').html('Changes Saved');
				}
				else
				{	
					resp = $.parseJSON(strResponse);	
					$('#inline_edit_message').html(resp.error);
				}
			}
		});
		tinymce.activeEditor.setProgressState(true);
}

