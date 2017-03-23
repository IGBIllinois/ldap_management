<?php
// 	Class which loads and encapsulates all installed extensions

class extensions{

	private static $extensionpath = "../extensions/";
	private static $extensions = array();
	
	function init(){
		$loadorder = json_decode(file_get_contents(self::$extensionpath."extensions.json"));
// 		echo "Load Order: <pre>"; var_dump($loadorder); echo "</pre>";
		for($i=0; $i<count($loadorder); $i++){
			$loadextension = json_decode(file_get_contents(self::$extensionpath.$loadorder[$i]."/ext.json"),true);
			if($loadextension == NULL){
				echo html::error_message("JSON Error: ".json_last_error_msg());
			}
// 			echo "Loaded JSON: <pre>"; var_dump($loadextension); echo "</pre>";
			foreach($loadextension as $type=>$typeinfo){
				if(!isset(self::$extensions[$type])){
					self::$extensions[$type] = array();
				}
				// TODO set basic type attributes
				if(!isset(self::$extensions[$type]['attributes'])){
					self::$extensions[$type]['attributes'] = array();
				}
				for($attr=0; $attr<count($typeinfo['attributes']); $attr++){
					$typeinfo['attributes'][$attr]['ext'] = $loadorder[$i];
					self::$extensions[$type]['attributes'][] = $typeinfo['attributes'][$attr];
				}
			}
		}
// 		echo "Extensions: <pre>"; var_dump(self::$extensions); echo "</pre>";
	}
	
	function get_attributes($type){
		if(isset(self::$extensions[$type])){
			return self::$extensions[$type]['attributes'];
		}
		return false;
	}
	
	function format($value,$format){
		if($format == "date"){
			return strftime('%m/%d/%Y %I:%M:%S %p', strtotime($value));
		}
		if($format == "timestamp"){
			return strftime('%m/%d/%Y %I:%M:%S %p', $value);
		}
		if($format == "dn"){
			if(preg_match("/(u|g)id=(.*?),/um", $value, $matches)){
				return $matches[2];
			}
			return $value;
		}
		return $value;
	}
	
}