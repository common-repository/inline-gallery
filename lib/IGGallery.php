<?php
include_once IG_FS_BASE . "/lib/IniFile.php";
include_once IG_FS_BASE . "/lib/IGImage.php";
include_once IG_FS_BASE . "/lib/IGTemplate.php";

/**
 * teh gallery
 *
 * @package inline_gallery
 * @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
 */
class IGGallery {
	/**
	 * file list
	 *
	 * @var array
	 */
	var $images;

	/**
	 * child galleries
	 *
	 * @var array
	 */
	var $children;

	/**
	 * ini settings
	 *
	 * @var object
	 */
	var $ini;

	/**
	 * template processor
	 *
	 * @var object
	 */
	var $template;

	/**
	 * base path
	 *
	 * @var string
	 */
	var $base;

	/**
	 * gallery metadata
	 *
	 * @var array
	 */
	var $meta;

	/**
	 * construct a gallery
	 * 
	 * @param string $path base path
	 */
	function __construct($path, $params = array(), $deep=false, $exts=array("jpg", "png", "gif")){
		if(!is_dir($path)){
			return; // should have been an exception/error, but need to keep compatible with the damn php 4
		}

		$inifiles = array("desc.txt", "Picasa.ini");
		foreach($inifiles as $if){
			$ip = ig_p_join($path, $if);
			if(is_readable($ip)){
				$this->ini = new IniFile($ip);
				break; //no need to read more than one
			}
		}
		
		$this->base = $path;
		$this->meta["label"] = basename($path);
		$this->extensions = $exts;
		$this->xclude = array(".", "..", "template", "thumbs");
		$this->params = $params;
		$this->ls($path, $deep);
		$t = $this->data();
	}

	/**
	 * PHP4 compatibility decorator for __construct
	 * 
	 * @param string $path @see __construct()
	 */
	function IGGallery($path, $deep=false, $exts=array("jpg", "png", "gif")){
		$this->__construct($path, $deep, $exts);
	}

	function ls($path, $deep=false){
		$ret = array();
		if(!is_dir($path))
			return;
		
		$this->children = array();
		$this->images = array();

		$params = $this->params;
		if($deep) {
			$params["parent"] = basename($path);
		}
		
		$dir = dir($path);

		while(false !== ($e = $dir->read())){
			if(in_array($e, $this->xclude))
				continue;

			$fp = ig_p_join($path, $e);
			if(is_dir($fp) && !is_link($fp) && $deep)
				$this->children[$e] = new IGGallery($fp, $params);
			elseif(in_array(strtolower(file_ext($e)), $this->extensions)){
				$this->images[$e] = $fp;
			}
		}
		
		ksort($this->images);
	}

	function &data() {
		if(isset($this->_data)) {
			return $this->_data;
		}

		$ret = array();
		$ini = $this->ini->data;
		$params = $this->params;
		
		foreach($this->images as $name => $path) {
			$if = new IGImage($path);
			
			$meta = $if->meta;
			$meta["caption"] = empty($meta["caption"]) ? $ini[$name]["caption"] : $meta["caption"];
			$meta["caption"] = empty($meta["caption"]) ? $name : $meta["caption"];

			if(defined("IG_HAVE_MBSTRING")){
				if(isset($ini["encoding"]["utf8"])) {
					if($ini["encoding"]["utf8"] == 1) {
						$enc = "UTF-8";
					}
				} else {
					$enc = mb_detect_encoding($meta["caption"], mb_detect_order(), true);
				}
				$meta["caption"] = mb_convert_encoding($meta["caption"], $params["charset"], $enc);
			}

			$meta["prefix"] = "$params[siteurl]/$params[ig_root]/" . (isset($params["parent"]) ? "$params[parent]/" : "") . $this->meta["label"];
			$meta["file"]   = $name;
			$meta["label"]  = $this->meta["label"];

			$ret[$name] = $meta;
			unset($if);
		}

		$this->_data = $ret;
		return $ret;
	}

	function render(){
		$ini_data = $this->ini->data;
		if(isset($ini_data["template"])){
			$tpl = $ini_data["template"];
		} elseif(is_dir(($p = ig_p_join($this->base, "template")))) {
			$tpl = $p;
		} else {
			$tpl = ig_p_join(IG_FS_BASE, "template");
		}
		
		$template = new IGTemplate($tpl);

		return $this->images ? $template->render($this, $this->params) : "";
	}
}
//end class IGGallery
?>
