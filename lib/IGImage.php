<?php

include_once "JpgFile.php";

/**
 * gallery image; adapter for various metadata holders
 *
 * @package inline_gallery
 * @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
 */
class IGImage {
	/**
	 * image metadata
	 *
	 * @var array
	 */
	var $meta;

	/**
	 * build the gallery item
	 * 
	 * @param string $filename image file path
	 */
	function __construct($filename) {
		if(is_readable($filename)){
			$sz = @getimagesize($filename);
			if(!$sz)
				return;
			$this->meta = array();
			switch($sz["mime"]){
				case "image/jpeg":
					$f = new JpgFile($filename);
					foreach($f->fields as $field){
						$this->meta[$field] = trim($f->$field);
					}
				break;				
			}
		}
	}

	function IGImage($filename){
		$this->__construct($filename);
	}

}

?>
