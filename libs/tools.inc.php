<?php


function parseSHFile_PMES($rfn){

	$SH_parsed = array();
    $json = json_decode(trim(file_get_contents($rfn)));

    $json_array = json_decode(json_encode($json[0]), True);
    foreach ($json_array as $k => $v_obj){
        $v     = json_encode($v_obj, JSON_PRETTY_PRINT);
        $v_txt = "<pre style='white-space: pre-wrap;'>$v</pre>";
        $SH_parsed[$k]=$v_txt;
    }
    return $SH_parsed;
}

// TOOL DEVELOPERS

function getInputMetadata($input) {
	
	$output = "";	

	foreach($input as $k => $v) {

		if((is_array($v) && $input["type"] != "enum" && $input["type"] != "enum_multiple") ||
			(is_array($v) && $k == "default" && ($input["type"] == "enum" || $input["type"] == "enum_multiple")))
			$v = implode(", ", $v); 

		if(is_array($v) && $k == "enum_items" && ($input["type"] == "enum" || $input["type"] == "enum_multiple")) {

			$ei = "";		
	
			foreach($v as $kei => $vei) {

				$ei .= $kei;
				$ei .= " (".implode(", ", $vei).")<br>";

			}

			$ei = rtrim($ei, "<br>");
			$v = $ei;

		}

		if(is_bool($v)) $v = $v ? 'true' : 'false';

		$output .= "<strong>$k:</strong> $v<br>";

	}

	return $output;

}

function getArgument($arg, $class = null, $defval = null, $input_name = null) {

	if(!$input_name) $input_name = "arguments";

	// llegir required (*)
	$output = '';

	switch($arg["type"]) {

		case "integer":
		case "number":

			if(isset($arg["default"])) 
				$val = 'value="'.$arg["default"].'"'; 
			else 
				$val = 'placeholder="default value"';

			if($defval) $val = 'value="'.$defval.'"';

			$output .= '<input 
										type="number" 
										name="'.$input_name.'['.$arg["name"].']" 
										'.$val.'
										class="form-control '.$class.'" >';
			break;

		case "string":

			if(isset($arg["default"])) 
				$val = 'value="'.$arg["default"].'"'; 
			else 
				$val = 'placeholder="default value"';

			if($defval) $val = 'value="'.$defval.'"';

			$output .= '<input 
										type="text" 
										name="'.$input_name.'['.$arg["name"].']" 
										'.$val.'
										class="form-control '.$class.'" >';
			break;

		case "boolean":

			$options["name"] = [1, 0];
			$options["description"] = ["True", "False"];

			if(isset($defval)) {
				$val = ($defval == 1|| preg_match('/True/',$defval)) ? ["True"] : ["False"];
			}elseif(isset($arg["default"])) {
				 $val = $arg["default"];
			}else{
				$noselected = true;
				$val = ["True"];
			}
			if (!is_array($val)) $val = array($val);

			$output .= '<select  
										name="'.$input_name.'['.$arg["name"].']" 
										class="form-control '.$class.'">';
										for ($i=0; $i<count($options['name']); $i++) {
											/*if(strtolower($options["description"][$i]) == strtolower($val)) $sel = "selected";
											else $sel = "";*/
											if(in_array($options['name'][$i], $val)) $sel = "selected";
											else $sel = "";

											if($noselected) $sel = "";

											$output .= '<option value="'.$options["name"][$i].'" '.$sel.'>'.$options["description"][$i].'</option>';
										}
			$output .= '</select>';
			break;
	
		case "enum":

			$options = $arg["enum_items"];

			if($defval) $val = array($defval);
			elseif(isset($arg["default"])) $val = $arg["default"];
			else $val = [];
			if (!is_array($val)) $val = array($val);

			$output .= '<select  
										name="'.$input_name.'['.$arg["name"].']" 
										class="form-control '.$class.'">';
										for ($i=0; $i<count($options['name']); $i++) {
											if(in_array($options['name'][$i], $val)) $sel = "selected";
											else $sel = "";
											if(isset($options["description"])) $output .= '<option value="'.$options["name"][$i].'" '.$sel.'>'.$options["description"][$i].'</option>';
											else $output .= '<option value="'.$options["name"][$i].'" '.$sel.'>'.$options["name"][$i].'</option>';
										}
			$output .= '</select>';
			break;

		case "enum_multiple":

			$options = $arg["enum_items"];

			if($defval) $val = $defval;
			elseif(isset($arg["default"])) $val = $arg["default"];
			else $val=[];
			if (!is_array($val)) $val = array($val);

			$output .= '<select  
				name="'.$input_name.'['.$arg["name"].'][]" 
				class="form-control select-multiple" 
				multiple="multiple">';
			for ($i=0; $i<count($options['name']); $i++) {
				if(in_array($options['name'][$i], $val)) $sel = "selected";
				else $sel = "";
				$output .= '<option value="'.$options["name"][$i].'" '.$sel.'>'.$options["description"][$i].'</option>';
			}
			$output .= '</select>';
			break;
	

	}

	return $output;

}

function generateLogo($toolid) {
	$result = $GLOBALS['toolsDevMetaCol']->findOne(array("_id" => $toolid));

	$path = $GLOBALS['dataDir']."/".$result["user_id"]."/".$GLOBALS['devUser_dir'].$toolid."/logo/";
	if(!file_exists($path)) mkpath($path);

	$text = ($result["step3"]["tool_spec"]["name"]=="My Tool"? str_replace(array("_","-")," ",$toolid) : $result["step3"]["tool_spec"]["name"]);

	$tsize = strlen($text);
	if($tsize < 5) $tsize = 5;

	// image size
	$w = 600;
	$h = 600;
	// Create the image
	$im = imagecreatetruecolor($w, $h);

	// Create some colors
	$rgb_colors = array(array(0,204,170),
			    array(164,198,57),
			    array(255,126,0),
			    array(175,0,42),
			    array(178,190,181),
			    array(205,149,117),
		      );
	$background = imagecolorallocate($im, 255, 255, 255);
	$rnd = array_rand($rgb_colors); # select random color
	$color = imagecolorallocate($im, $rgb_colors[$rnd][0], $rgb_colors[$rnd][1], $rgb_colors[$rnd][2]);

	imagefilledrectangle($im, 0, 0, $w, $w, $background);

	// Font size
	$fsize = intval(1500/$tsize);
	// The text to draw
	$text = strtoupper($text);
	// Replace path by your own font path
	$font = __DIR__.'/../assets/global/fonts/Deutschlander.ttf';

	// calculating x-position
	$tb = imagettfbbox($fsize, 0, $font, $text);
	$x = ceil(($w - $tb[2]) / 2); // lower left X coordinate for text

	$y = ($h/2)+((abs($tb[5] - $tb[1]))/2);

	// Add the text
	imagettftext($im, $fsize, 0, $x, $y, $color, $font, $text);

	// Using imagepng() results in clearer text compared with imagejpeg()
	imagepng($im, $path.'logo.png');
	imagedestroy($im);
}


// Generates an execution-metadata set given a result file ID

function getExecutionInfo_fromResultId($result_id){

        $executionInfo = array();

        // check given file is a result file
        $result = getGSFile_fromId($result_id);
	
        if (!$result){
            $_SESSION['errorData']['Error'][]="Internal error. Given result file (ID $result_id) not found.";
            return $executionInfo;
	}
        if (!isset($file['tool'])){
            $_SESSION['errorData']['Error'][]="Compulsory metadata attribute missing: 'tool'. Make sure the given file/folder (ID $result_id) has been generated during a job execution.";
            return $executionInfo;
	}

        // look for the corresponding run ID
	$execution_id = 0;
	$execution    = array();

	// given id corresponds to a run folder
	if ($file['type'] == "dir"){
		$execution_id = $result_id;
		$execution    = $result;
	// given id corresponds to a result file
	}else{
        	$execution_id = $file['parentDir'];
        	$execution    = getGSFile_fromId($execution_id);
        	if (!$execution){
            		$_SESSION['errorData']['Error'][]="Internal error. Cannot find the associated execution for the given result file (ID $result_id).";
            		return $executionInfo;
        	}
	}

        // build executionInfo
        $executionInfo['arguments']   = (isset($execution['arguments'])? $executionInfo['arguments']: array());
        $executionInfo['input_files'] = (isset($execution['input_files'])? $executionInfo['input_files']: array());
        $executionInfo['tool']        = (isset($execution['tool'])? $executionInfo['tool']: null);
        $executionInfo['submission_file'] = (isset($execution['submission_file'])? $executionInfo['submission_file']: null);
        $executionInfo['log_file'] = (isset($execution['log_file'])? $executionInfo['log_file']: null);

        return $executionInfo;
}

function getExecutionList_fromInputId($file_id){
	
        $executionList = array();
}
?>
