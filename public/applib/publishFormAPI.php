<?php

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "getAllFiles") {
		echo(participantFiles());
		exit;
		
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getUserInfo") {
		echo user_info();
		exit;
		
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getFileInfo") {
		$fn = $_REQUEST['files'];
		echo file_Info($fn);

        exit;
	}
	elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "publish") {
		$metadata = $_REQUEST['metadata'];
		$fn = $_REQUEST['fileId'];
		
		$doi = oeb_publish_file_eudat($fn,$metadata);
		var_dump($doi);
		//check regex in prod enviroment!!!
		if (preg_match("/b2share.\w{32}/",$doi)){
			if (registerDOIToVRE ($fn, $doi)){
				echo $doi;
			}
		}
		exit;
	}else {
        echo "IN";
        var_dump($_REQUEST);
    	} 
} else {
    echo '{}';
    exit;
}

function participantFiles() {
	//initiallize variables
	$block_json="{}";
	$files = array();

	//get data from DB
	$proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));        
    $file_filter = array(
            "data_type" => "participant",
            "project"   => $proj_name_active
    );
	$filteredFiles = getGSFiles_filteredBy($file_filter);
	
	foreach ($filteredFiles as $key => $value) {
		//get data type name for each file
		$datatype_name = getDataTypeName($value['data_type']);
		$value['datatype_name'] = $datatype_name;
		array_push($files, $value);
		
	}
	
	$block_json = json_encode($files, JSON_PRETTY_PRINT);

	return $block_json;

}

function user_info() {
	//initiallize variables
	$block_json="{}";

	//user logged
	$userId = $_SESSION["User"]["id"];

	//type of user
	$user = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("oeb_community"=>1, "id"=>1, "Name"=>1, "Surname"=>1, "Email" =>1));

	$block_json = json_encode($user, JSON_PRETTY_PRINT);

	return $block_json;
}

function file_Info($fn){
	//initiallize variables
	$block_json="{}";

	//file fn
	//$fileData = $GLOBALS['filesCol']->findOne(array('_id' => $fn, 'owner' => $_SESSION['User']['id']), array("data_type" =>1));
	$fileData = getGSFile_fromId($fn, "");

	$block_json = json_encode($fileData, JSON_PRETTY_PRINT);

	return $block_json;
	
}
