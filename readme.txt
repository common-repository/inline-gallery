=== Inline Gallery ===
Contributors: m0n5t3r, others needed
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=iacobs%40gmail%2ecom&item_name=Support%20for%20Inline%20Gallery&no_shipping=1&no_note=1&tax=0&currency_code=EUR&lc=RO&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: images, gallery, post, IPTC, XMP
Requires at least: 2.0.0
Tested up to: 2.5.0
Stable tag: 0.3.9

An easy way to turn posts into image galleries. Very customizable. Can read and set IPTC and XMP captions

== Description ==

This plugin offers a way of including arbitrary amounts of images in posts; 
the basic ideas came from [AutoGallery](http://kyrres.net/2005/02/04/autogallery/). 
Here is a [little demo](http://m0n5t3r.info/work/wordpress-plugins/inline-gallery/inline-gallery-demo/) 
of what it can do; also see [my photos](http://m0n5t3r.info/photo-galleries/) 
for another flavour :-)

**Features**:

* plug and play, just like any other Wordpress plug-in
* easy to configure
* does no processing on the images (except for getting the image metadata), 
  so it's lightning fast
* you can have multiple galleries per post
* integrated with Wordpress 2.0's wysiwyg editor (the way QuickTags is implemented 
  doesn't allow easy placing of buttons in the toolbar, but that's not a big problem, 
  since you are editing code, anyway)
* (since 0.2) output is customizable with templates
* (since 0.3.0) supports XMP, IPTC and EXIF captions; The order of preference is:
  -  XMP Dublin Core title and description
  -  XMP Photoshop Headline text
  -  IPTC caption
  -  EXIF comment
  
  This means you can also set captions for your files using Photoshop or Picasa, 
  if you don't particularly enjoy editing ini files.
* (since 0.3.0) Picasa.ini support (Picasa writes captions there for files that 
  do not support IPTC)
* (since 0.3.1) Admin page AJAX-ish gallery browser and caption editor using mootools 
  (needs write access to your image files)
* (since 0.3.5) Ships with Lightbox v2, Slimbox, Slimebox, Thickbox and
  allows to switch between them from the options page

== Installation ==

1. download
2. unzip and copy the `inline_gallery` folder into `wp-content/plugins`
3. activate the plugin
4. go to Options &rarr; Inline Gallery to set it up. Here is an explanation of the
   settings:
  * the root folder is the path relative to your site URL, so if you access
   your wordpress at `http://example.com/blog/` and the images are under
   `http://example.com/blog/wp-content/galleries`, you should set the
   gallery root to `wp-content/galleries`
  * the image types list is simply a comma separated list of file extensions
5. you're done

== Frequently Asked Questions ==

= How is it licensed? =

GPL

= Does it require PHP safe mode to be off? =

laboratory tests say it doesn't; however, this needs testing in the wild
(hosting environments can be very different, depending on the host's
willingness to pay for a good sysadmin).

= Requirements? =

* Wordpress 2.0 or later
* the gd library enabled in PHP

= Plugin could not be activated because it triggered a fatal error. wtf? =

it's most probably due to exceeding memory_limit; Inline Gallery uses a big
library (the PHP JPEG Metadata Toolkit), and eats up about 3MB of server memory 
when fully loaded.

Version 0.3.9 delays loading the code until it is actually needed, so this 
shouldn't happen any more. If it does, it probably wouldn't work anyway in
your current setup (try disabling some plugins).

= How many images can it display? =

It depends on the available memory; benchmarks in this area are still needed.

= How about speed? =

Since version 0.3.9, Inline Gallery uses the Wordpress caching framework, so 
it can take advantage of high performance caching if needed; and since PJMT
(the biggest memory hog) is loaded only when needed, a cache hit usually
results in lower overall memory consumption.

== Screenshots ==

1. Plug-in options page
2. Gallery browser, showing available galleries
3. Image caption editor
4. The gallery button showing up in the visual editor

== Usage ==

* write post
* upload photos to a directory with the same name as the post slug; subalbums 
  are subdirectories of the main gallery with the same structure
* if you intend to use the default template, upload thumbnails to a
  subdirectory of the gallery called "thumbs"
* use `<!--gallery-->` in the post where you want the main gallery to appear,
* or `<!--gallery[subname]-->` for a subgallery

You can add captions to the images in several ways:

* using the admin page ajax-ish caption editor; it needs write access to your
  photos, as it stores the captions inside the images (JPEG only for now)
* using a text editor: create a file called "desc.txt" and write descriptions
  there; it is standard ini format, like this:
  `
  #comment
  ;another comment
  [keyword]               ;comments can stay here, too
  variable=value
  `
  Recognized section names:
  - meta: gallery-wide information, like gallery name, headline, etc; passed to
    header and footer templates
  - template: must contain values for header, footer, item; can contain other
    template metadata (recognized so far: group_count, group_before, group_after)
  - image file name: image metadata
* using Google Picasa (the desktop application, not the web service); add
  captions to your images before uploading and don't forget to also upload
  Picasa.ini
* using Adobe Photoshop and any other desktop image processing software that
  supports either XMP or the older IPTC standard

a desc.txt file could look like this:
`
[meta]
name=This is a short description of the gallery

[image1.jpg]
caption=this is an alt text
...
`

The thumbnail dimensions are of your choice. The thumbnails can be easily generated 
using Photoshop's batch mode or ImageMagick's convert in a shell one-liner 
(bash example: `for a in *.JPG; do convert --resize $a thumbs/thumb-$a`)

== Installing the sample gallery ==

* Copy the "sample" directory under the gallery root you chose
* create a post or a page called "sample", enter `<!--gallery-->` as the
  contents or press the "insert gallery" button and then OK without entering
  anything, publish, view post
* if you see a black cat, then you have everything working correctly :D

== Inserting a gallery in a post ==

* in code edit mode: 
   - enter `<!--gallery-->` where you want your main gallery to appear, or                        
   - enter `<!--gallery[subfolder]-->` if you want to insert a sub-gallery
* in visual editing mode, click the "insert gallery" button (it kind of looks
  like the "insert image" button), and enter `subfolder` in the input box for
  a 2nd level gallery, leave it blank or press cancel for the top gallery

Note that the top folder doesn't need to contain images, it can be just a place 
to store sub-galleries. 

== The gallery browser ==

* Is located under Manage &rarr; Galleries
* Uses a folder icon from the Etiquette icon theme
* Is tested in Firefox 2.0, Opera 9 and Internet Exploder 6, so far, both
  with the default theme and wp-admin-tiger; pixel-perfect in Firefox and
  Opera, some CSS problems make it unusable in MSIE (patches welcome, i.e. I
  won't fix it)
* Is designed for lazy people:
 - click a gallery, the first image is loaded
 - click the caption field, edit, press enter, it is saved
 - the next image is loaded and the caption field is already active
 - allows quick navigation among the images in the loaded gallery by using 
   the left and right arrow keys or **n** and **p** when the caption editing 
   field is not active
* Needs write access to the gallery folder and files to update the captions
  and will reset file ownership to the uid of the web server process; on shared
  hosts that run the web server process under the same uid as the one you use
  to connect via (s)ftp it is not an issue
* Is in the "works for me" phase, needs testing and may also kill your pet :P

