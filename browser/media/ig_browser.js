(function($){
	var current = null;
	function browse() {
		var self = $(this);
		var icon = self.children('img').attr('src');
		var url = self.attr('href');
		var gname = self.attr('rel');
		$('#ig_images, #ig_preview').empty();
		$('#ig_browser_title').html(gname);
		$('#ig_galleries').hide();
		$('#ig_browser').show();
		$('#ig_images').addClass('loading');
		$.getJSON($('#browser_service').attr('href'), {gallery: gname, todo:'list'}, function(d){
			var files = $('<ul class="ig_main files"/>');
			var dirs = $('<ul class="ig_main dirs"/>');
			var idx = 0;
			for(var name in d) {
				var val = d[name];
				if(val.file) {
					$('<li/>')
						.append($('<a class="ig_thumb"/>')
							.attr({
								href: url + '/' + val.file, 
								id: 'ig_img' + idx,
								title: val.caption,
								rel: gname
								})
							.click(edit)
							.append($('<img/>').attr({src: url + '/thumbs/thumb-' + val.file})))
						.appendTo(files);
				} else {
					$('<li class="ig_gdir" />')
						.append($('<a/>')
							.attr({
									href: url + '/' + name,
									id: 'ig_subdir' + idx,
									title: name,
									rel: gname
								})
							.data('content', val)
							.data('name', name)
							.click(subdir)
							.append($('<img/>').attr('src', icon))
							.append($('<br/>'))
							.append($('<span>' + name + '</span>')))
						.appendTo(dirs);
				}
				idx++;
			}
			$('#ig_images').removeClass('loading').append(dirs).append(files);
			load(files.children('li:first'));
		});
		return false;
	}

	function subdir() {
		var self = $(this);
		var contents = self.data('content');
		var url = self.attr('href');
		var gname = self.attr('rel');
		var name = self.data('name');
		
		var dirs = $('<ul class="ig_subdir dirs"/>');
		var files = $('<ul class="ig_subdir files"/>');

		$('<li/>')
			.append($('<a/>')
				.attr({
						href: url + '/..',
						id: 'ig_main',
						title: 'Go up',
						rel: '..'
					})
				.html('..')
				.click(function(){
						current = null;
						$('#ig_images .ig_subdir').remove();
						$('#ig_images .ig_main').show();
						load($('#ig_images .files:visible li:first'));
						return false;
					}))
			.appendTo(dirs);
		
		var idx = 0;
		for(var img in contents) {
			var imd = contents[img]
			$('<li/>')
				.append($('<a class="ig_thumb"/>')
					.attr({
							href: url + '/' + img, 
							id: 'ig_img' + idx,
							title: imd.caption,
							rel: gname + '/' + name
						})
					.click(edit)
					.append($('<img/>').attr({src: url + '/thumbs/thumb-' + img})))
				.appendTo(files);
			idx++;
		}

		$('#ig_images .ig_main').hide();
		$('#ig_images').append(dirs).append(files);
		load(files.children('li:first'));

		return false;
	}

	function load(lnk) {
		if(typeof lnk === 'string') {
			lnk = $(lnk);
		}
		if(lnk.length === 0) {
			return;
		}
		var pw = $('#ig_preview').addClass('loading');
		var ls = $('#ig_images');
		if(current) { 
			current.removeClass('selected');
		}
		if(lnk[0].nodeName.toLowerCase() === 'li') {
			current = lnk;
			lnk = lnk.find('a');
		} else {
			current = lnk.parents('li');
		}

		current.addClass('selected-shadow');
		ls.animate({
				scrollTop: (ls.scrollTop() + current.position().top - 3)
			}, 'fast', function(){
				current.removeClass('selected-shadow');
				current.addClass('selected');
			});
		var img = $('<img/>').attr({src: lnk.attr('href'), alt: lnk.attr('title')}).css({margin: 5});
		img.load(function(){
			var imh = img[0].height;
			var imw = img[0].width;
			var pwh = pw.height() - 10;
			var pww = pw.width() - 10;
			var scale1 = imh/pwh;
			var scale2 = imw/pww;
			var scale = Math.max(1, scale1, scale2);
			img.attr({width: imw / scale, height: imh / scale});
			pw.removeClass('loading').empty().append(img);
			$('#ig_caption').val(lnk.attr('title'));
		});
	}

	function edit() {
		load($(this));
		return false;
	}
	
	function seq(dir) {
		if(!current || (current && !$.isFunction(current[dir]))) {
			return;
		}

		var thing = current[dir]();
		
		if(thing.length > 0) {
			load(thing);
		}
	}

	function next() { seq('next');}

	function prev() { seq('prev');}

	function keyHandler(e) {
		var t = $(e.target);
		if(t[0].tagName.toLowerCase() !== 'input') {
			switch(e.keyCode) {
				case 37: //left arrow
				case 80: //p
					prev();
					break;
				case 39: //right arrow
				case 78: //n
					next();
					break;
				default:
					return true;
			}
		} else if(t.is('#ig_caption') && e.keyCode === 13 && current) {
			var a = current.find('a');
			if(a.attr('title') !== t.val()) {
				t.attr({disabled: 'disabled'}).trigger('blur').parent().addClass('loading2');
				var href = a.attr('href');
				$('<div/>').load($('#browser_service').attr('href'), {
					gallery: a.attr('rel'),
					img: href.substring(href.lastIndexOf('/') + 1),
					caption: t.val(),
					todo: 'set-caption'
				}, function() {
					a.attr('title', t.val());
					seq('next');
					t.attr('disabled', '').trigger('focus').parent().removeClass('loading2');
				});
			} else {
				seq('next');
				t.trigger('focus');
			}
		}
	}

	$(document).ready(function(){
		$('.ig_dir a').each(function(){
			$(this).click(browse);
		});

		$('#ig_form').bind('submit', function(){
			return false;
		});

		$(document).keydown(keyHandler);

		$('#ig_menu_browse').click(function(){
			$('#ig_browser').hide();
			$('#ig_galleries').show();
		});
	});
})(jQuery);

