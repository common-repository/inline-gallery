(function() {
	tinyMCE.importPluginLanguagePack("inline_gallery");

	var TinyMCE_inline_gallery = {
		getInfo: function() {
			return {
				longname: "Inline Gallery Plugin",
				author: "m0n5t3r",
				authorurl: "http://m0n5t3r.info/",
				infourl: "http://m0n5t3r.info/",
				version: 1
			};
		},

		getControlHTML: function(control_name){
			switch (control_name) {
				case "inline_gallery":
					return tinyMCE.getButtonHTML(control_name, "lang_inline_gallery_button", "{$pluginurl}/images/gallery.gif", "inline_gallery");
					break;
				default:
					return "";
			}

			return "";
		},

		execCommand: function(editor_id, element, command, user_interface, value){
			var inst = tinyMCE.getInstanceById(editor_id);
			var focusElm = inst.getFocusElement();
			var doc = inst.getDoc();

			function getAttrib(elm, name) {
				return elm.getAttribute(name) ? elm.getAttribute(name) : "";
			}

			// Handle commands
			switch (command) {
					case "inline_gallery":
						var flag = "";
						var template = new Array();
						var altGallery = tinyMCE.getLang("lang_inline_gallery_alt", "gallery");

						// Is selection a image
						if (focusElm !== null && focusElm.nodeName.toLowerCase() == "img") {
							flag = getAttrib(focusElm, "class");
			
							if (flag != "mce_plugin_inline_gallery") // Not ours
								return true;
			
							var action = "update";
						}

						var galleryID = "["+prompt("Enter the gallery name like '/name/' (blank or press cancel for main gallery)")+"]";
						galleryID = galleryID == "[]" || galleryID == "[null]" ? "" : galleryID;
						
						var html = ''
							+ '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" '
							+ ' width="100%" height="10px" '
							+ 'alt="'+altGallery+galleryID+'" title="'+altGallery+galleryID+'" class="mce_plugin_inline_gallery" name="mce_plugin_inline_gallery" />';
						tinyMCE.execCommand("mceInsertContent",true,html);
						tinyMCE.selectedInstance.repaint();
						return true;
						break;
					default:
						return false;
			}

			// Pass to next handler in chain
			return false;
		},

		cleanup: function(type, content){
			var startPos = 0;
			var altGallery = tinyMCE.getLang('lang_inline_gallery_alt', 'gallery');
			var galleryID = "";
			switch (type) {
				case "insert_to_editor":
					startPos = 0;

					// Parse all <!--gallery*--> tags and replace them with images
					while ((startPos = content.indexOf('<!--gallery', startPos)) != -1) {
						var tagLength = content.indexOf('-->',startPos + 11) - startPos + 3;
						//get gallery ID if there is one
						if (content.substring(startPos + 12, startPos + 11) == "["){
							galleryID = "[" + content.substring(startPos + tagLength - 4, startPos + 12) + "]";
							galleryID = galleryID == "[]" ? "" : galleryID;
						}
						// Insert image
						var contentAfter = content.substring(startPos + tagLength);
						content = content.substring(0, startPos);
						content += '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" ';
						content += ' width="100%" height="10px" ';
						content += 'alt="'+altGallery+galleryID+'" title="'+altGallery+galleryID+'" class="mce_plugin_inline_gallery" />';
						content += contentAfter;

						startPos++;
					}

					// It's supposed to be WYSIWYG, right?
					content = content.replace(new RegExp('&', 'g'), '&amp;');

					break;

				case "get_from_editor":
					// Parse all img tags and replace them with <!--gallery-->
					startPos = -1;
					while ((startPos = content.indexOf("<img", startPos+1)) != -1) {
						var endPos = content.indexOf("/>", startPos);
						var attribs = this._parseAttributes(content.substring(startPos + 4, endPos));

						if (attribs["class"] == "mce_plugin_inline_gallery" || attribs["name"] == "mce_plugin_inline_gallery") {
							endPos += 2;
							galleryID = attribs['alt'].substring(altGallery.length);
			
							var embedHTML = "<!--gallery" + galleryID + "-->";
			
							// Insert embed/object chunk
							var chunkBefore = content.substring(0, startPos);
							var chunkAfter = content.substring(endPos);
							content = chunkBefore + embedHTML + chunkAfter;
						}
					}
					break;
				default:
					return content;
			}

			// Pass through to next handler in chain
			return content;
		},

		handleNodeChange: function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection){
			tinyMCE.switchClass(editor_id + "_inline_gallery", "mceButtonNormal");
			if(node === null){
				return;
			}

			do {
				if (node.nodeName.toLowerCase() === "img" && tinyMCE.getAttrib(node, "class").indexOf("mce_plugin_inline_gallery") === 0){
					tinyMCE.switchClass(editor_id + "_inline_gallery", "mceButtonSelected");
				}
			} while ((node = node.parentNode));

			return true;
		},

		_parseAttributes: function(attribute_string){
			var attributeName = "";
			var attributeValue = "";
			var withInName;
			var withInValue;
			var attributes = new Array();
			var whiteSpaceRegExp = new RegExp('^[ \n\r\t]+', 'g');

			if (attribute_string === null || attribute_string.length < 2)
				return null;

			withInName = withInValue = false;

			for (var i=0; i<attribute_string.length; i++) {
				var chr = attribute_string.charAt(i);

				if ((chr == '"' || chr == "'") && !withInValue)
					withInValue = true;
				else if ((chr == '"' || chr == "'") && withInValue) {
					withInValue = false;

					var pos = attributeName.lastIndexOf(' ');
					if (pos != -1)
						attributeName = attributeName.substring(pos+1);

					attributes[attributeName.toLowerCase()] = attributeValue.substring(1).toLowerCase();

					attributeName = "";
					attributeValue = "";
				} else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
					withInName = true;

				if (chr == '=' && withInName)
					withInName = false;

				if (withInName)
					attributeName += chr;

				if (withInValue)
					attributeValue += chr;
			}

			return attributes;
		}
	};

	tinyMCE.addPlugin("inline_gallery", TinyMCE_inline_gallery);
})();
