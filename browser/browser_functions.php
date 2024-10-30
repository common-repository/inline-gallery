<?php 
//include "../../../wp-config.php";

include_once dirname(__FILE__) . "/../lib/pathconf.php";

/**
 * show a list of galleries in the gallery r00t
 * @return string spits out HTML
 */
function ig_list_galleries(){
	$ret = "";
	
	$fs_root = ABSPATH . "/" . get_option("ig_root");
	$gallery_root = htmlentities(get_option("siteurl") . "/" . get_option("ig_root"));
	$imglocation = htmlentities(get_option("siteurl")) . IG_WEB_BASE . "/browser/media/gnome-fs-directory2.png";
	$template = '<li class="ig_dir"><a href="' . $gallery_root . '/{$dir}" rel="{$name}" title="Click to open"><img src="' . $imglocation . '" alt="directory icon" />&nbsp;<span>{$name}</span></a></li>';
	
	$entries = ig_lsdir($fs_root);
	
	foreach($entries as $e){
		if(!is_dir($fs_root . "/" . $e)){
			continue;
		}
		$temp = str_replace('{$name}', $e, $template);
		$ret .= str_replace('{$dir}', $e, $temp);
	}
	
	echo "<ul>",$ret,"</ul>";
}

/**
 * get a directory listing
 * 
 * @param $path
 * @return array
 */
function ig_lsdir($path, $deep=false, $ignored=array("template", "thumbs")){
	array_push($ignored, ".", "..");
	$ret = array();

	if(!is_dir($path)){
		return $ret;
	}
	
	$dir = dir($path);
	
	while(false !== ($e = $dir->read())){
		if(in_array($e, $ignored)){
			continue;
		}
		
		$fp = ig_p_join($path, $e);

		if(is_dir($fp) && !is_link($fp) && $deep) { // don't follow symlinks, avoid security problems/infinite recursion
			$ret[] = array($e => ig_lsdir($fp));
		} else {
			$ret[] = $e;
		}
	}
	
	return $ret;
}
?>
