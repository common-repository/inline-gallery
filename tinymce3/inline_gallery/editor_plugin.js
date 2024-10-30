/**
 * Inline Gallery plugin. based largely on the wordpress plugin, since it does about the same thing
 */
(function(){
	var DOM = tinymce.DOM;
	tinymce.PluginManager.requireLangPack('inline_gallery');
	
	function gal_html(ed, url, name){
		if(!name){
			name = prompt(ed.getLang('inline_gallery.subfolder_prompt')) || '';
		}
		var igHTML = [
			'<img src="',
			url + '/img/trans.gif',
			'" class="mceIGgallery mceItemNoResize" title="',
			ed.getLang('inline_gallery.alt'),
			name ? ' ' + name : '',
			'" alt="',
			name,
			'" />'
		].join('');
		return igHTML;
	}

	tinymce.create('tinymce.plugins.InlineGallery', {
		init: function(ed, url){
			var t = this;

			ed.addCommand('IG_Gallery', function(){
				ed.execCommand('mceInsertContent', 0, gal_html(ed, url));
			});
			
			ed.addButton('inline_gallery', {
				title: 'inline_gallery.button',
				image: url + '/img/gallery.gif',
				cmd: 'IG_Gallery'
			});

			t._handleGallery(ed, url);
			
			ed.addShortcut('alt+g', ed.getLang('inline_gallery.insert_gallery'), 'IG_Gallery');
		},

		getInfo: function(){
			return {
				longname: 'Inline Gallery Plugin',
				author: 'Sabin Iacob (m0n5t3r)',
				authorurl: 'http://m0n5t3r.info',
				infourl: 'http://m0n5t3r.info',
				version: '2.0'
			};
		},

		_handleGallery: function(ed, url){
			ed.onInit.add(function(){
				ed.dom.loadCSS(url + '/css/content.css');
			});

			// Display morebreak instead if img in element path
			ed.onPostRender.add(function(){
				if(ed.theme.onResolveName){
					ed.theme.onResolveName.add(function(th, o){
						if(o.node.nodeName === 'IMG' || o.node.nodeName === 'img'){
							if(ed.dom.hasClass(o.node, 'mceIGgallery')){
								o.name = 'inline_gallery';
							}
						}
					});
				}
			});

			ed.onBeforeSetContent.add(function(ed, o){
				o.content = o.content.replace(/<!--gallery(\[([^\]]+)\])?-->/g, gal_html(ed, url, '$2'));
			});

			ed.onPostProcess.add(function(ed, o){
				if(o.get){
					o.content = o.content.replace(/<img[^>]+>/g, function(im){
						if(im.indexOf('class="mceIGgallery') !== -1){
							var m = im.match(/alt="(.*?)"/);
							var igtext = m && m[1] ? '[' + m[1] + ']' : '';

							im = '<!--gallery' + igtext + '-->';
						}
						return im;
					});
				}
			});

			// Set active buttons if user selected pagebreak or more break
			ed.onNodeChange.add(function(ed, cm, n){
				cm.setActive('inline_gallery', (n.nodeName === 'IMG' || n.nodeName === 'img') && ed.dom.hasClass(n, 'mceIGgallery'));
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('inline_gallery', tinymce.plugins.InlineGallery);
})();
