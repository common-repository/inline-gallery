<?php
include_once "pjmt/Toolkit_Version.php";     
include_once "pjmt/JPEG.php";
include_once "pjmt/JFIF.php";
include_once "pjmt/PictureInfo.php";
include_once "pjmt/XMP.php";
include_once "pjmt/Photoshop_IRB.php";
include_once "pjmt/EXIF.php";
include_once "pjmt/Photoshop_File_Info.php";

class JpgFile{
	var $head;
	var $name;
	var $title;
	var $author;
	var $authorsposition;
	var $caption;
	var $captionwriter;
	var $jobname;
	var $copyrightstatus;
	var $copyrightnotice;
	var $ownerurl;
	var $keywords;
	var $category;
	var $supplementalcategories;
	var $date;
	var $city;
	var $state;
	var $country;
	var $credit;
	var $source;
	var $headline;
	var $instructions;
	var $transmissionreference;
	var $urgency;
	var $fields = array(
		"title",
		"author",
		"authorsposition",
		"caption",
		"captionwriter",
		"jobname",
		"copyrightstatus",
		"copyrightnotice",
		"ownerurl",
		"keywords",
		"category",
		"supplementalcategories",
		"date",
		"city",
		"state",
		"country",
		"credit",
		"source",
		"headline",
		"instructions",
		"transmissionreference",
		"urgency");
	var $exif;
	var $xmp;
	var $irb;
	
	function JpgFile($filename){
		$this->__construct($filename);
	}
	
	function __construct($filename){
		$this->name = $filename;
		$this->head = get_jpeg_header_data($filename);
		
		$this->xmp = read_xmp_array_from_text(get_XMP_text($this->head));
		$this->irb = get_Photoshop_IRB($this->head);
		$this->exif = get_EXIF_JPEG($this->name);
		
		$data = get_photoshop_file_info($this->exif, $this->xmp, $this->irb);

		foreach($this->fields as $f){
			$this->{$f} = $data[$f];
		}
	}
	
	function update(){
		$data = array();
		foreach($this->fields as $f){
			$data[$f] = $this->{$f};
		}
		
		$this->head = put_photoshop_file_info($this->head, $data, $this->exif, $this->xmp, $this->irb);
		
		if(put_jpeg_header_data( $this->name, $this->name . ".tmp", $this->head)){
			rename($this->name . ".tmp", $this->name);
			return true;
		}
		return false;
	}
}
?>
