<?php 
include "../../../../wp-config.php";	
include "../../../../wp-admin/admin.php";	
include "browser_functions.php";
include "JSON.php";
include "../lib/IGGallery.php";

$gal = $_REQUEST["gallery"];
$groot = ABSPATH . get_option("ig_root") . "/$gal";
		
switch($_REQUEST["todo"]){
	case "list":
		$gal = $_REQUEST["gallery"];
		$groot = ig_p_join(ABSPATH, get_option("ig_root"), "$gal");
		
		$params = array(
			"siteurl" => get_option("siteurl"),
			"ig_root" => get_option("ig_root"),
			"charset" => get_option("blog_charset"),
		);

		$g = new IGGallery($groot, $params, true);
		$data = $g->data();

		if($g->children) {
			foreach($g->children as $name => $gg) {
				$data[$name] = $gg->data();
			}
		}

		$json = new Services_JSON();
		echo $json->encode($data);
		break;
	case "set-caption":
		$caption = $_REQUEST["caption"];
		$img = $_REQUEST["img"];
		$file = $groot . "/" . $img;
		if(is_writable($file)){
			$sz = getimagesize($file);
			if($sz["mime"] == "image/jpeg"){
				$jpg = new JpgFile($file);
				$jpg->caption = trim($caption);
				$jpg->update();
			}
		}
		break;
}
?>
