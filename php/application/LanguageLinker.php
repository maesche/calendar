<?php
require_once('application/XMLResourceBundle.php');
class LanguageLinker {
	public $resourceBundle;
	private $lang = "fr";
	
	public function __construct() {
		$this->loadBundle();
	}
	
	public function getLang() {
		return $this->lang;
	}
	
	public function setLang($lang) {
		$this->lang = $lang;
		$this->loadBundle();
	}
	
	private function loadBundle() {
		$path = get_include_path();
		echo $path;
		$this->resourceBundle = new XMLResourceBoundle("/Volumes/FILES/smeier/Sites/calendar/xml/lang", "lang.xml", $this->lang);
	}
}
?>