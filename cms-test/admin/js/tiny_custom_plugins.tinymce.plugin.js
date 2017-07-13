/* 	tiny_custom_plugins.tinymce.js

	About: all custom plugins used on this site. Plugins are loaded in ./tiny_mce_settings.js

	Plugins:
		youtube: 	
			add/edit youtube videos. works in conjunction with $('.lightbox') and $('.fullscreenbox')
			in /js/functions.js
		image_custom_scaling:
			custom image plugin that allows scaling images
*/


/* youtube */		
/*jshint maxlen:255 */
/*eslint max-len:0 */
/*global tinymce:true */

tinymce.PluginManager.add('youtube', function(editor, url) {

	function showDialog() {
		var win, width, height, data;
		var generalFormItems = [
			{name: 'source1', type: 'textbox', size: 40, autofocus: true, label: 'YouTube ID'}
		];



		if (editor.settings.media_poster !== false) {
			generalFormItems.push({name: 'poster', type: 'filepicker', filetype: 'image', size: 40, label: 'Poster'});
		}
		
		if (editor.settings.media_poster !== false) {
			generalFormItems.push({name: 'alt', type: 'textbox', size: 40, label: 'Poster Description'});
		}

		data = getData(editor.selection.getNode());
		
		win = editor.windowManager.open({
			title: 'Insert/Edit YouTube Videos',
			data: data,
			body: generalFormItems,
			onSubmit: function() {
				editor.insertContent(dataToHtml(this.toJSON()));
			}
		});
		
	}

	function dataToHtml(data) {
		
		if (!data.source1) {

				return '';

		}


		if (!data.poster) {
			data.poster = "https://img.youtube.com/vi/"+data.source1+"/default.jpg";
		}
		
		if (!data.alt) {
			data.alt = "Upload Image";
		}

		data.source1 = editor.convertURL(data.source1, "source");

		data.poster = editor.convertURL(data.poster, "poster");
		
		data.alt = editor.convertURL(data.alt, "alt");







			tinymce.each(data, function(value, key) {
				data[key] = editor.dom.encode(value);
			});

// NkwTG6UJT0c
// http://www.youtube.com/embed/NkwTG6UJT0c?rel=0&autoplay=1		
		
html = '<youtube><a class="lightbox fullscreenbox" href="http://www.youtube.com/embed/'+data.source1+'?rel=0&autoplay=1"><img alt="'+data.alt+'" src="'+data.poster+'"></a></youtube>';

		return html;
	}

	function getData(element) {
		
		if(! $(element).is('youtube')) $youtube = $(element).closest('youtube');
		else $youtube = $(element);
		
		tmpHref = $youtube.find('a').attr("href");
		ytID = typeof tmpHref == "string" ? tmpHref.match(".*/embed/(.*)\\?rel.*") : '';
		
		var data = {}
		data.source1 = ytID[1];
		data.poster = $youtube.find('img').attr("src");
		data.alt = $youtube.find('img').attr("alt");
		
		return data;
	}

	editor.on('ResolveName', function(e) {
		var name;

		if (e.target.nodeType == 1 && (name = e.target.getAttribute("data-mce-object"))) {
			e.name = name;
		}
	});

	editor.on('preInit', function() {
		// Make sure that any messy HTML is retained inside these
		var specialElements = editor.schema.getSpecialElements();
		tinymce.each('video audio iframe object'.split(' '), function(name) {
			specialElements[name] = new RegExp('<\/' + name + '[^>]*>','gi');
		});

		// Allow elements
		editor.schema.addValidElements('object[id|style|width|height|classid|codebase|*],embed[id|style|width|height|type|src|*],video[*],audio[*]');

		// Set allowFullscreen attribs as boolean
		var boolAttrs = editor.schema.getBoolAttrs();
		tinymce.each('webkitallowfullscreen mozallowfullscreen allowfullscreen'.split(' '), function(name) {
			boolAttrs[name] = {};
		});

		// Converts iframe, video etc into placeholder images
		editor.parser.addNodeFilter('iframe,video,audio,object,embed,script', function(nodes, name) {
			var i = nodes.length, ai, node, placeHolder, attrName, attrValue, attribs, innerHtml;
			var videoScript;

			while (i--) {
				node = nodes[i];

				if (node.name == 'script') {
					videoScript = getVideoScriptMatch(node.attr('src'));
					if (!videoScript) {
						continue;
					}
				}

				placeHolder = new tinymce.html.Node('img', 1);
				placeHolder.shortEnded = true;

				if (videoScript) {
					if (videoScript.width) {
						node.attr('width', videoScript.width.toString());
					}

					if (videoScript.height) {
						node.attr('height', videoScript.height.toString());
					}
				}

				// Prefix all attributes except width, height and style since we
				// will add these to the placeholder
				attribs = node.attributes;
				ai = attribs.length;
				while (ai--) {
					attrName = attribs[ai].name;
					attrValue = attribs[ai].value;

					if (attrName !== "width" && attrName !== "height" && attrName !== "style") {
						if (attrName == "data" || attrName == "src") {
							attrValue = editor.convertURL(attrValue, attrName);
						}

						placeHolder.attr('data-mce-p-' + attrName, attrValue);
					}
				}

				// Place the inner HTML contents inside an escaped attribute
				// This enables us to copy/paste the fake object
				innerHtml = node.firstChild && node.firstChild.value;
				if (innerHtml) {
					placeHolder.attr("data-mce-html", escape(innerHtml));
					placeHolder.firstChild = null;
				}

				placeHolder.attr({
					width: node.attr('width') || "300",
					height: node.attr('height') || (name == "audio" ? "30" : "150"),
					style: node.attr('style'),
					src: tinymce.Env.transparentSrc,
					"data-mce-object": name,
					"class": "mce-object mce-object-" + name
				});

				node.replace(placeHolder);
			}
		});

		// Replaces placeholder images with real elements for video, object, iframe etc
		editor.serializer.addAttributeFilter('data-mce-object', function(nodes, name) {
			var i = nodes.length, node, realElm, ai, attribs, innerHtml, innerNode, realElmName;

			while (i--) {
				node = nodes[i];
				realElmName = node.attr(name);
				realElm = new tinymce.html.Node(realElmName, 1);

				// Add width/height to everything but audio
				if (realElmName != "audio" && realElmName != "script") {
					realElm.attr({
						width: node.attr('width'),
						height: node.attr('height')
					});
				}

				realElm.attr({
					style: node.attr('style')
				});

				// Unprefix all placeholder attributes
				attribs = node.attributes;
				ai = attribs.length;
				while (ai--) {
					var attrName = attribs[ai].name;

					if (attrName.indexOf('data-mce-p-') === 0) {
						realElm.attr(attrName.substr(11), attribs[ai].value);
					}
				}

				if (realElmName == "script") {
					realElm.attr('type', 'text/javascript');
				}

				// Inject innerhtml
				innerHtml = node.attr('data-mce-html');
				if (innerHtml) {
					innerNode = new tinymce.html.Node('#text', 3);
					innerNode.raw = true;
					innerNode.value = unescape(innerHtml);
					realElm.append(innerNode);
				}

				node.replace(realElm);
			}
		});
	});

	editor.on('ObjectSelected', function(e) {
		var objectType = e.target.getAttribute('data-mce-object');

		if (objectType == "audio" || objectType == "script") {
			e.preventDefault();
		}
	});


	editor.addButton('youtube', {
		icon: 'media',
		tooltip: 'Insert/Edit Youtube Videos',
		onclick: showDialog,
		stateSelector: ['img[data-mce-object=video]', 'img[data-mce-object=iframe]', 'youtube']
	});

	editor.addMenuItem('youtube', {
		icon: 'media',
		text: 'Insert youTube Vidoes',
		onclick: showDialog,
		context: 'insert',
		prependToContext: true
	});
});


/* image_custom_scaling */
/*global tinymce:true */
tinymce.PluginManager.add('image_custom_scaling', function(editor) {
	function getImageSize(url, callback) {
		var img = document.createElement('img');

		function done(width, height) {
			if (img.parentNode) {
				img.parentNode.removeChild(img);
			}

			callback({width: width, height: height});
		}

		img.onload = function() {
			done(img.clientWidth, img.clientHeight);
		};

		img.onerror = function() {
			done();
		};

		var style = img.style;
		style.visibility = 'hidden';
		style.position = 'fixed';
		style.bottom = style.left = 0;
		style.width = style.height = 'auto';

		document.body.appendChild(img);
		img.src = url;
	}

	function applyPreview(items) {
		tinymce.each(items, function(item) {
			item.textStyle = function() {
				return editor.formatter.getCssText({inline: 'img', classes: [item.value]});
			};
		});

		return items;
	}

	function createImageList(callback) {
		return function() {
			var imageList = editor.settings.image_list;

			if (typeof(imageList) == "string") {
				tinymce.util.XHR.send({
					url: imageList,
					success: function(text) {
						callback(tinymce.util.JSON.parse(text));
					}
				});
			} else {
				callback(imageList);
			}
		};
	}

	function showDialog(imageList) {
		var win, data = {}, dom = editor.dom, imgElm = editor.selection.getNode();
		var width, height, imageListCtrl, classListCtrl;

		function buildValues(listSettingName, dataItemName, defaultItems) {
			var selectedItem, items = [];

			tinymce.each(editor.settings[listSettingName] || defaultItems, function(target) {
				var item = {
					text: target.text || target.title,
					value: target.value
				};

				items.push(item);

				if (data[dataItemName] === target.value || (!selectedItem && target.selected)) {
					selectedItem = item;
				}
			});

			if (selectedItem && !data[dataItemName]) {
				data[dataItemName] = selectedItem.value;
				selectedItem.selected = true;
			}

			return items;
		}

		function buildImageList() {
			var imageListItems = [{text: 'None', value: ''}];

			tinymce.each(imageList, function(image) {
				imageListItems.push({
					text: image.text || image.title,
					value: editor.convertURL(image.value || image.url, 'src'),
					menu: image.menu
				});
			});

			return imageListItems;
		}

		function recalcSize() {
			var widthCtrl, heightCtrl, newWidth, newHeight;
			
			if (editor.settings.image_dimensions === false) {
				width = null;
				height = null;
				return;
			}

			widthCtrl = win.find('#width')[0];
			heightCtrl = win.find('#height')[0];

			newWidth = widthCtrl.value();
			newHeight = heightCtrl.value();

			if (win.find('#constrain')[0].checked() && width && height && newWidth && newHeight) {
				if (width != newWidth) {
					newHeight = Math.round((newWidth / width) * newHeight);
					heightCtrl.value(newHeight);
				} else {
					newWidth = Math.round((newHeight / height) * newWidth);
					widthCtrl.value(newWidth);
				}
			}

			width = newWidth;
			height = newHeight;

		}

		
		function onSubmitForm() {
			function waitLoad(imgElm) {
				function selectImage() {
					imgElm.onload = imgElm.onerror = null;
					editor.selection.select(imgElm);
					editor.nodeChanged();
				}

				imgElm.onload = function() {
					selectImage();
				};

				imgElm.onerror = selectImage;
			}

			updateStyle();
			recalcSize();

			data = tinymce.extend(data, win.toJSON());

			if (!data.alt) {
				data.alt = '';
			}

			if (data.style === '') {
				data.style = null;
			}

			data = {
				src: data.src,
				alt: data.alt,
				style: data.style,
				"class": data["class"]
			};

			if (!data["class"]) {
				delete data["class"];
			}

			editor.undoManager.transact(function() {
				if (!data.src) {
					if (imgElm) {
						dom.remove(imgElm);
						editor.focus();
						editor.nodeChanged();
					}

					return;
				}

				if (!imgElm) {
					data.id = '__mcenew';
					editor.focus();
					editor.selection.setContent(dom.createHTML('img', data));
					imgElm = dom.get('__mcenew');
					dom.setAttrib(imgElm, 'id', null);
				} else {
					dom.setAttribs(imgElm, data);
				}

				waitLoad(imgElm);
			});
		}

		function removePixelSuffix(value) {
			if (value) {
				value = value.replace(/px$/, '');
			}

			return value;
		}
		
		function removePercentSuffix(value) {
			if (value) {
				value = value.replace(/%$/, '');
			}

			return value;
		}

		function srcChange() {
			if (imageListCtrl) {
				imageListCtrl.value(editor.convertURL(this.value(), 'src'));
			}
		}


		if (imgElm.nodeName == 'IMG' && !imgElm.getAttribute('data-mce-object') && !imgElm.getAttribute('data-mce-placeholder')) {
			data = {
				src: dom.getAttrib(imgElm, 'src'),
				alt: dom.getAttrib(imgElm, 'alt'),
				"class": dom.getAttrib(imgElm, 'class')
			};
		} else {
			imgElm = null;
		}

		if (imageList) {
			imageListCtrl = {
				type: 'listbox',
				label: 'Image list',
				values: buildImageList(),
				value: data.src && editor.convertURL(data.src, 'src'),
				onselect: function(e) {
					var altCtrl = win.find('#alt');

					if (!altCtrl.value() || (e.lastControl && altCtrl.value() == e.lastControl.text())) {
						altCtrl.value(e.control.text());
					}

					win.find('#src').value(e.control.value());
				},
				onPostRender: function() {
					imageListCtrl = this;
				}
			};
		}

		if (editor.settings.image_class_list) {
			classListCtrl = {
				name: 'class',
				type: 'listbox',
				label: 'Image Styles',
				values: applyPreview(buildValues('image_class_list', 'class'))
			};
		}

		// General settings shared between simple and advanced dialogs
		var generalFormItems = [
			{name: 'src', type: 'filepicker', filetype: 'image', label: 'Source', autofocus: true, onchange: srcChange},
			imageListCtrl
		];

		if (editor.settings.image_description !== false) {
			generalFormItems.push({name: 'alt', type: 'textbox', label: 'Image description'});
		}

		if (editor.settings.image_dimensions !== false) {
			generalFormItems.push({
				type: 'container',
				label: 'Dimensions',
				layout: 'flex',
				direction: 'row',
				align: 'center',
				spacing: 5,
				items: [
					{name: 'width', type: 'textbox', maxLength: 5, size: 3, onchange: recalcSize, ariaLabel: 'Width'},
					{type: 'label', text: 'x'},
					{name: 'height', type: 'textbox', maxLength: 5, size: 3, onchange: recalcSize, ariaLabel: 'Height'},
					{name: 'constrain', type: 'checkbox', checked: true, text: 'Constrain proportions'}
				]
			});
		}
				
		generalFormItems.push({
				type: 'container',
				label: 'Scale',
				layout: 'flex',
				direction: 'row',
				align: 'center',
				spacing: 5,
				items: [
					{name: 'scale', type: 'textbox', maxLength: 7, size: 2, onchange: updateStyle},
					{type: 'label', text: '%'}
				]
			});

		generalFormItems.push(classListCtrl);
		
		function updateStyle() {
			function addPixelSuffix(value) {
				if (value.length > 0 && /^[0-9]+$/.test(value)) {
					value += 'px';
				}

				return value;
			}
			function addPercentSuffix(value) {
				if (value.length > 0 && /^[0-9]+$/.test(value)) {
					value += '%';
				}

				return value;
			}

			if (!editor.settings.image_advtab) {
				return;
			}

			var data = win.toJSON();
			var css = dom.parseStyle(data.style);

			delete css.margin;
			css['margin-top'] = css['margin-bottom'] = addPixelSuffix(data.vspace);
			css['margin-left'] = css['margin-right'] = addPixelSuffix(data.hspace);
			css['border-width'] = addPixelSuffix(data.border);
			css['width'] = addPercentSuffix(data.scale);
			win.find('#style').value(dom.serializeStyle(dom.parseStyle(dom.serializeStyle(css))));
		}

		// don't require image_advtab true: always use advtab
//		if (editor.settings.image_advtab) {
			// Parse styles from img
			if (imgElm) {
				data.hspace = removePixelSuffix(imgElm.style.marginLeft || imgElm.style.marginRight);
				data.vspace = removePixelSuffix(imgElm.style.marginTop || imgElm.style.marginBottom);
				data.border = removePixelSuffix(imgElm.style.borderWidth);
				data.scale = removePercentSuffix(imgElm.style.width);
				data.style = editor.dom.serializeStyle(editor.dom.parseStyle(editor.dom.getAttrib(imgElm, 'style')));
			}

			// Advanced dialog shows general+advanced tabs
			win = editor.windowManager.open({
				title: 'Insert/edit image',
				data: data,
				bodyType: 'tabpanel',
				body: [
					{
						title: 'General',
						type: 'form',
						items: generalFormItems
					},

					{
						title: 'Advanced',
						type: 'form',
						pack: 'start',
						items: [
							{
								label: 'Style',
								name: 'style',
								type: 'textbox'
							},
							{
								type: 'form',
								layout: 'grid',
								packV: 'start',
								columns: 2,
								padding: 0,
								alignH: ['left', 'right'],
								defaults: {
									type: 'textbox',
									maxWidth: 50,
									onchange: updateStyle
								},
								items: [
									{label: 'Vertical space', name: 'vspace'},
									{label: 'Horizontal space', name: 'hspace'},
									{label: 'Border', name: 'border'}//,
								]
							}
						]
					}
				],
				onSubmit: onSubmitForm
			});
	//	} else {
	//		// Simple default dialog
	//		win = editor.windowManager.open({
	//			title: 'Insert/edit image',
	//			data: data,
	//			body: generalFormItems,
	//			onSubmit: onSubmitForm
	//		});
	//	}
	}

	editor.addButton('image', {
		icon: 'image',
		tooltip: 'Insert/edit image',
		onclick: createImageList(showDialog),
		stateSelector: 'img:not([data-mce-object],[data-mce-placeholder])'
	});

	editor.addMenuItem('image', {
		icon: 'image',
		text: 'Insert image',
		onclick: createImageList(showDialog),
		context: 'insert',
		prependToContext: true
	});
});
