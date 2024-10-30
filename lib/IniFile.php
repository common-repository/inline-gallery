<?php
/**
 * ini file operations
 *
 * @package inline_gallery
 * @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
 */
class IniFile{
	/**
	 * file path
	 *
	 * @var string
	 */
	var $file;

	/**
	 * ini data
	 *
	 * @var array
	 */
	var $data;

	/**
	 * ini handler constructor
	 * 
	 * @param string $file file path
	 */
	function __construct($file){
		if(is_readable($file)){
			$this->file = $file;
			$this->parse($file);
		}
	}

	/**
	 * PHP4 compatibility decorator for __construct
	 * 
	 * @param string $file @see __construct()
	 */
	function IGIni($file){
		$this->__construct($file);
	}

	/**
	 * parse an ini file into an array
	 *
	 * @param string $file file path
	 * @param string $def default field name
	 * @return mixed array when called statically or void when called in class context
	*/
	function &parse($file, $def = "default"){
		if(!is_readable($file))
			return array("ain't it cute? BUT IT'S WRONG!!!");
			
		$file = explode("\n", preg_replace('/[;#].*$|\r/', "", file_get_contents($file))); //read file, strip comments, explode
	
		$ret = array();
		$section = "Global"; //default section
		
		foreach($file as $line){
			$line = trim($line);
	
			if($p = strpos($line, "[") !== false){ //section header
				$section = trim(str_replace("]","", substr($line, $p)));
			} elseif(!empty($line)) {
				$line = explode("=", $line, 2);
				$name = count($line) == 2 ? trim($line[0]) : $def;
				$value = isset($line[1]) ? $line[1] : $line[0];
				
				$ret[$section][$name] = trim($value);
			}
		}

		if(is_object($this)){
			$this->data = $ret;
		} else {
			return $ret;
		}
	}

	function save($file = ""){
		if(!$file)
			$file = $this->file;

		if(!is_writable($file))
			return;

		$txt = "";
		foreach($this->data as $section => $content){
			if($section != "Global"){
				$txt .= "[$section]\n";
			}
			foreach($content as $k => $v){
				$txt .= "$k = $v\n";
			}
			$txt .= "\n";
		}

		$tf = ig_p_join(dirname($file), "." . basename($file));

		$fp = fopen($tf, "w");
		fwrite($fp, $txt);
		fclose($fp);
		rename($tf, $file);
	}
} // END class IniFile
?>
