// EN lang variables

var metaKey = 'Alt';

if (navigator.userAgent.indexOf('Mac OS') != -1) {
// Mac OS browsers use Ctrl to hit accesskeys
	metaKey = 'Ctrl';
}

tinyMCE.addI18n('en.inline_gallery', {
	button: 'Add image gallery (' + metaKey + '+g)',
	alt: 'Image Gallery',
	insert_gallery: 'Insert Gallery',
	subfolder_prompt: 'Please enter the subfolder name (blank or press cancel for main gallery folder)'
});
