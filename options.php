<?php
/**
 * options for teh gallery
 *
 */
if(basename($_SERVER["PHP_SELF"]) == "options.php"){
	include "../../../wp-blog-header.php";
	include "../../../wp-admin/admin.php";
	$standalone = true;

	session_start();
}

include "lib/options-lib.php";

$ig_options = array();

$ig_root = get_option("ig_root"); 

$ig_url_preview = get_option("siteurl") . "/$ig_root/sample/fubar.jpg";

$fs_ig_root = ABSPATH . $ig_root; 

if(isset($_REQUEST["mkdir"]) && $standalone){
	check_admin_referer("inline_gallery-mkdir");

	wp_mkdir_p($fs_ig_root);

	add_option("ig_root_created", "true");

	$back = remove_query_arg("updated", wp_get_referer());
	wp_redirect($back);
	exit;
}

$ig_root_exists = is_dir($fs_ig_root);

if(!$ig_root_exists){
	$fs_ig_root = array_filter(explode("/", $ig_root));
	$pathlen = count($fs_ig_root);
	$last_path = "";
	for($i = $pathlen; $i > 0; $i--){
		$last_path = ABSPATH . join("/", array_slice($fs_ig_root, 0, $i));
		if(is_dir($last_path)){
			break;
		}
	}

	$ig_root_create = is_writeable($last_path)
		? '. However, I can <a href="' . wp_nonce_url(IG_WEB_BASE . "/options.php?mkdir=1", "inline_gallery-mkdir") . '">create it</a> for you.' 
		: " and I don't have the required permissions to create it. You'll have to do it manually.";
	$ig_root_warning = "<br /><strong>Warning!!!</strong> the folder specified above does not exist$ig_root_create";
}

$fx = array(
	""         => "no, thanks!",
	"lightbox" => "Lightbox v2",
	"slimbox"  => "Slimbox",
	"slimebox" => "Slimebox",
	"thickbox" => "Thickbox",
);

$fx_desc = <<<FX_DESC_END
<a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox v2</a> requires Prototype and Scriptaculous (provided by Wordpress)<br />
<a href="http://www.digitalia.be/software/slimbox">Slimbox</a> requires mootools (minimal distribution included)<br />
<a href="http://m0n5t3r.info/work/wordpress-plugins/slimebox/">Slimebox</a> requires mootools (minimal distribution included)<br />
<a href="http://jquery.com/demo/thickbox/">Thickbox</a> requires jQuery (provided by Wordpress)
FX_DESC_END;

if(get_option("ig_root_created") == "true"){
	echo '<div id="message" class="updated fade"><p><strong>Directory created.</strong></p></div>';
	delete_option("ig_root_created");
}

the_options_header("Inline Gallery " . ($wp_version < "2.5" ? "Options" : "Settings"));
the_form_header();

if(!defined("IG_HAVE_MBSTRING")) {
	display_message("<h3>Warning!</h3>The mbstring extension doesn't appear to be present. Depending on the character set and the language you are writing your captions in (for instance, Picasa uses the default Windows charset, which rarely is UTF-8), captions may display incorrectly.</strong>");
}

display_option($ig_options, "Folder containing galleries", "ig_root", "text", false, "Relative to blog root<br /> URL preview: <code>$ig_url_preview</code>$ig_root_warning");
display_option($ig_options, "File extensions allowed", "ig_extensions", "text", false, "File extensions, separated by commas");
display_option($ig_options, "Add type and size information to alt text", "ig_meta", "checkbox", false, "alt text preview: <br /><code>This is a photo" .  (get_option("ig_meta") ? "(JPEG,800x600)" : "") . "</code>");
display_option($ig_options, "Eye candy", "ig_eye_candy", "select", $fx, $fx_desc);
display_option($ig_options, "Do not include the required javascript library (my theme already provides it)", "ig_eye_candy_nolib", "checkbox", false);

the_form_footer($ig_options);

the_options_footer();
?>
