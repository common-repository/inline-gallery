<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "utils.php";

$cwd = realpath(ig_p_join(dirname(__FILE__), "..", ""));
$abspath = ABSPATH;

if(DIRECTORY_SEPARATOR != "/"){
	$abspath = str_replace(DIRECTORY_SEPARATOR, "/", ABSPATH);
	$cwd = str_replace(DIRECTORY_SEPARATOR, "/", $cwd);
}

define("IG_WEB_BASE", "/" . str_replace($abspath, "", $cwd));
define("IG_FS_BASE", $cwd);
define("IG_DIR", basename(IG_WEB_BASE));

?>
