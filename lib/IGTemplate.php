<?php
/**
 * Inline Gallery template processor
 *
 * @package inline_gallery
 * @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
 */
class IGTemplate {
	/**
	 * header template
	 *
	 * @var string
	 */
	var $header;

	/**
	 * footer template
	 *
	 * @var string
	 */
	var $footer;

	/**
	 * gallery item template
	 *
	 * @var string
	 */
	var $item;

	/**
	 * content
	 *
	 * @var string
	 */
	var $content;

	/**
	 * build template
	 * 
	 * @param mixed $source array or string designating a path
	 */
	function __construct($source){
		if(is_array($source)){ // read from ini file
			$this->header = $source["header"];
			$this->footer = $source["footer"];
			$this->item   = $source["item"];
			$this->meta   = array();
			foreach($source as $k => $v){
				if(!in_array($k, array("header", "footer", "item"))){
					$this->meta[str_replace("meta_", "", $k)] = $v;
				}
			}
		} elseif(is_dir($source)) {
			$if = new IniFile(ig_p_join($source, "template.ini"));
			$this->header = @file_get_contents(ig_p_join($source, "header.tpl.php"));
			$this->footer = @file_get_contents(ig_p_join($source, "footer.tpl.php"));
			$this->item   = @file_get_contents(ig_p_join($source, "item.tpl.php"));
			$this->meta   = $if->data;
		} else {
			$this->header = $this->footer = $this->item = "";
		}
	}

	/**
	 * PHP4 compatibility decorator for __construct
	 * 
	 * @param mixed $source @see __construct()
	 */
	function IGTemplate($source){
		$this->__construct($source);
	}
	
	/**
	 * substitute content in the template; similar to the python simple template module:
	 * * $name and ${name} are replaced with $content["name"] or "" if $content["name"] is not set
	 * * $$ is replaced with $
	 * 
	 * @param string $template template string
	 * @param array &$content associative array carrying data to be substituted
	 * @return string
	 */
	function substitute($template, &$content){
		$vars = array();
		$vals = array();
		preg_match_all('/\{?\$(\{)?([\w_]+)(?(1)\})\}?/', $template, $matches);

		foreach($matches[0] as $k => $v){
			$vars[] = $v;
			$vals[] = isset($content[$matches[2][$k]]) ? $content[$matches[2][$k]] : "";
		}

		$vars[] = '$$';
		$vals[] = '$';

		return str_replace($vars, $vals, $template);
	}

	function header(&$content){
		return $this->substitute($this->header, $content);
	}

	function footer(&$content){
		return $this->substitute($this->footer, $content);
	}

	function item(&$content){
		return $this->substitute($this->item, $content);
	}

	function render(&$gallery, $params = array()){
		$ini = $gallery->ini->data;

		$group = $this->meta["group"];
		if(is_array($group))
			$group["count"] = intval($group["count"]);

		$cnt = 0;
		$ret = $this->header($ini["meta"]);

		$data = $gallery->data($params);

		foreach($data as $name => $meta) {
			if(is_array($group)) {
				if($cnt == 0)
					$ret .= $group["before"];
				elseif($cnt % $group["count"] == 0)
					$ret .= ($group["after"] . $group["before"]);
			}

			$cnt++;
			$ret .= $this->item($meta);
		}

		if(is_array($group)){
			$ret .= $group["after"];
		}

		$ret .= $this->footer($ini["meta"]);
		return $ret;
	}
}

?>
