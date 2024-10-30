<?php
/*
Plugin Name: Inline Gallery
Plugin URI: http://m0n5t3r.info/work/wordpress-plugins/inline-gallery/
Description: A flexible way to include photos in your posts. After activation go to Options &rarr; Inline Gallery to configure.
Version: 0.3.9
Author: m0n5t3r
Author URI: http://m0n5t3r.info/
*/

include_once dirname(__FILE__) . "/lib/pathconf.php";

if(!class_exists("WP_Scripts")){
	include_once(IG_FS_BASE . "/lib/script-loader.php");
}

class InlineGallery {
	function displayOptions(){
		include IG_FS_BASE . "/options.php";
	}

	function displayBrowser(){
		$prefix = IG_FS_BASE . "/browser";
		$subpage = isset($_GET["load"]) ? "$prefix/$_GET[load].php" : "$prefix/browser.php";
		$base = get_option("siteurl") . "/wp-admin/edit.php?page=inline_gallery_browser";
		if($subpage != realpath($subpage) || !is_readable($subpage)){
			$subpage = "$prefix/browser.php";
		}
		include $subpage;
	}
	
	function filter($text, $name = "") {
		include_once IG_FS_BASE . "/lib/IGGallery.php";

		global $id, $post, $ig_is_gallery;

		$gallery = "";
		if (strpos($text,"<!--gallery") !== false) {
			$name = $name != "" ? $name : $post->post_name;
			$prefix = get_option("siteurl");

			$ig_root = trim(get_option("ig_root"), "/");
			$charset = get_option('blog_charset');
			$fs_path = realpath(ig_p_join(ABSPATH, $ig_root, $name));
			
			if(!is_dir($fs_path)) {
				return $text;
			}

			$render_params = array(
				"siteurl" => $prefix,
				"ig_root" => $ig_root,
				"charset" => $charset,
			);

			$st = stat($fs_path);
			$need_save = false;
			$gal = wp_cache_get($fs_path . $st["mtime"], "inline_gallery");

			if(!$gal) {
				$gal = new IGGallery($fs_path, $render_params, true);
				$need_save = true;
			}

			$gallery = str_replace("<!--gallery-->", $gal->render(), $text); // insert main gallery

			foreach($gal->children as $g => $cg){
				$gallery = str_replace("<!--gallery[$g]-->", $cg->render(), $gallery);
				//TODO remove this in a later version
				$gallery = str_replace("<!--gallery[/$g/]-->", $cg->render(), $gallery);
			}

			if($need_save) {
				wp_cache_set($fs_path . $st["mtime"], $gal, "inline_gallery");
			}

			$ig_is_gallery = true;

		} else {
			$gallery = $text;
			$ig_is_gallery = false;
		}

		return $gallery;
	}

	function browserJS(){
		wp_enqueue_script("inline_gallery_browser", get_option("siteurl") . IG_WEB_BASE . "/browser/media/ig_browser.js", array("jquery12", "dimensions"));
	}

	function browserCSS(){ 
		?><link rel="stylesheet" href="<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/browser/media/ig_browser.css" media="screen,projection" />
		<link rel="bookmark" href="<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/browser/browser-service.php" type="text/html" id="browser_service" />
		<style type="text/css">
			<!--
			.loading { background: #eee url(<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/browser/media/ajax-loader2.gif) center center no-repeat !important;}
			.loading2 { background: #eee url(<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/browser/media/ajax-loader2.gif) left center no-repeat !important;}
			-->
		</style><?php 
	}
}

class IGAdmin {
	 
	function &tinymcePlugin(&$plugins){
		$plugins[] = "-inline_gallery";
		return $plugins;
	}

	function &tinymceButton(&$buttons){
		$buttons[] = "separator";
		$buttons[] = "inline_gallery";
		return $buttons;
	}

	function loadTinymcePlugin(){
		echo "tinyMCE.loadPlugin('inline_gallery', '". get_option("siteurl") . IG_WEB_BASE . "/tinymce/inline_gallery');\n";
	}

	function tinymceCSS($css){
		return $css . "," . get_option("siteurl") . IG_WEB_BASE . "/tinymce/inline_gallery/inline_gallery.css";
	}

	function &tinymce3Plugin(&$plugins){
		$plugins["inline_gallery"] = get_option("siteurl") . IG_WEB_BASE . '/tinymce3/inline_gallery/editor_plugin.js';
		return $plugins;
	}

	function menu(){
		add_options_page("Inline Gallery Options", "Inline Gallery", 5, "inline_gallery");
		add_management_page("Galleries", "Galleries", 5, "inline_gallery_browser");
	}

	function &postsFilter(&$data){
		if(is_singular() || is_front_page() || is_archive()) {
			foreach($data as $key => $post){
				$data[$key]->post_content = InlineGallery::filter($post->post_content, $post->post_name);
			}
		}
		return $data;
	}

	function activate(){
		global $wp_rewrite;
		//an array containing our option names and default values
		$ig_options=array(
			"ig_root" => "wp-content/uploads/galleries",
			"ig_extensions" => "jpg,jpeg,gif,png",
			"ig_meta" => true,
			"ig_eye_candy" => "",
			"ig_eye_candy_nolib" => false,
		);

		//make sure options exist
		foreach($ig_options as $k => $v){
			if(get_option($k) === false){
				add_option($k, $v);
			}
		}
	}

	function registerCSS($handle, $src){
		global $ig_styles;
		if(!isset($ig_styles)){
			$ig_styles = array("registry" => array(), "queue" => array());
		}
		$ig_styles["registry"][$handle] = $src;
	}

	function enqueueCSS($handle, $src = ""){
		global $ig_styles;

		if(!isset($ig_styles["registry"][$handle])){
			if(!empty($src)){
				IGAdmin::registerCSS($handle, $src);
			} else {
				return; 
			}
		}

		$ig_styles["queue"][] = $ig_styles["registry"][$handle];
	}

	function printCSS(){
		global $ig_styles;

		$template = '<link rel="stylesheet" type="text/css" href="' . get_option("siteurl") . '/%s" />';
		foreach($ig_styles["queue"] as $css){
			echo sprintf($template, $css), "\n";
		}
	}

	function init(){
		global $wp_version;
		$eyecandy_prefix = IG_WEB_BASE . "/eyecandy";
		$libs = get_option("ig_eye_candy_nolib");

		// register scripts
		wp_register_script("mootools", "$eyecandy_prefix/libs/mootools.v1.00.js");
		wp_register_script("jquery12", "$eyecandy_prefix/libs/jquery/jquery.js");
		wp_register_script("dimensions", "$eyecandy_prefix/libs/jquery/jquery.dimensions.min.js", array("jquery12"));
		wp_register_script("lightbox", "$eyecandy_prefix/lightbox/lightbox.js", !$libs ? array("scriptaculous-effects") : array());
		wp_register_script("slimbox", "$eyecandy_prefix/slimbox/slimbox.js", !$libs ? array("mootools") : array());
		wp_register_script("slimebox", "$eyecandy_prefix/slimebox/slimebox.js", !$libs ? array("mootools") : array());
		wp_register_script("thickbox-lightbox-compat", "$eyecandy_prefix/thickbox/thickbox-lightbox-compat.js", !$libs ? array("jquery12") : array());
		wp_register_script("thickbox", "$eyecandy_prefix/thickbox/thickbox.js", !$libs ? array("jquery12") : array());

		IGAdmin::registerCSS("lightbox", "$eyecandy_prefix/lightbox/css/lightbox.css");
		IGAdmin::registerCSS("slimbox", "$eyecandy_prefix/slimbox/css/slimbox.css");
		IGAdmin::registerCSS("slimebox", "$eyecandy_prefix/slimebox/css/slimebox.css");
		IGAdmin::registerCSS("thickbox", "$eyecandy_prefix/thickbox/thickbox.css");

		if(extension_loaded('mbstring')){
			define('IG_HAVE_MBSTRING', 1);
			mb_internal_encoding('UTF-8');
			$encs = array(
				'UTF-8',
				'UTF-7',
				'ASCII',
				'ISO-8859-1',
				'EUC-JP',
				'SJIS',
				'eucJP-win',
				'SJIS-win',
				'JIS',
				'ISO-2022-JP'
			);
			mb_detect_order($encs);
		}
		
		if(is_admin()) {
			if(!(current_user_can('edit_posts') || current_user_can('edit_pages'))) {
				return;
			}

			$manage_hook = sanitize_title("Manage");
			$options_hook = $wp_version < "2.4.0" ? sanitize_title("Options") : sanitize_title("Settings");

			add_action("${manage_hook}_page_inline_gallery_browser", array("InlineGallery", "displayBrowser"));
			add_action("${options_hook}_page_inline_gallery", array("InlineGallery", "displayOptions"));

			add_action("admin_print_scripts-${manage_hook}_page_inline_gallery_browser", array("InlineGallery", "browserJS"));
			add_action("admin_head-${manage_hook}_page_inline_gallery_browser", array("InlineGallery", "browserCSS"));

			add_action("admin_head", array("IGAdmin", "menu"));
		} else {
			if($wp_version < "2.4.0") {
				add_filter("mce_plugins", array("IGAdmin", "tinymcePlugin"));
				add_filter("mce_css", array("IGAdmin", "tinymceCSS"));
				add_action("tinymce_before_init", array("IGAdmin", "loadTinymcePlugin"));
			} else {
				add_filter("mce_external_plugins", array("IGAdmin", "tinymce3Plugin"));
			}

			add_filter("mce_buttons", array("IGAdmin", "tinymceButton"));
			add_filter("the_posts", array("IGAdmin", "postsFilter"), 99);
			
			add_action("wp_print_scripts", array("IGAdmin", "eyecandy"));
			add_action("wp_print_scripts", array("IGAdmin", "printCSS"));
		}

	}

	function tb_locations(){
		?><script type="text/javascript">
	//<![CDATA[
	tb_pathToImage = "<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/eyecandy/thickbox/loadingAnimation.gif";
	tb_closeImage = "<?php echo get_option("siteurl") . IG_WEB_BASE; ?>/eyecandy/thickbox/tb-close.png";
	//]]>
	</script><?php
	}

	function eyecandy(){
		global $ig_is_gallery;
		if($ig_is_gallery){
			$eyecandy = get_option("ig_eye_candy");
			if(!empty($eyecandy)){
				wp_enqueue_script($eyecandy);
				IGAdmin::enqueueCSS($eyecandy);
				if($eyecandy == "thickbox") {
					wp_enqueue_script("thickbox-lightbox-compat");
					add_action("wp_head", array("IGAdmin", "tb_locations"));
				}
			}
		}
	}
}


add_action("init", array("IGAdmin", "init"));
add_action("activate_" . plugin_basename(__FILE__), array("IGAdmin", "activate"));

//add_action("wp_footer", create_function('', 'echo memory_get_peak_usage()/1024;'));
?>
