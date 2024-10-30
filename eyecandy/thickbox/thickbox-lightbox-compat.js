jQuery(document).ready(function(){
	jQuery("a.thickbox").attr("rel", function(){
		return this.rel.replace(/[\[\]]/g, "_");
	});
});
