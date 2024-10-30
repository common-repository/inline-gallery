// EN lang variables

if (navigator.userAgent.indexOf('Mac OS') != -1) {
// Mac OS browsers use Ctrl to hit accesskeys
	var metaKey = 'Ctrl';
}
else {
	var metaKey = 'Alt';
}

tinyMCE.addToLang('',{
	inline_gallery_button : 'Add image gallery (' + metaKey + '-g)',
	inline_gallery_alt : 'gallery'
});
