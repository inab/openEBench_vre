<?php

//Manages the backend request of publication tabs

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getAllFiles&type=participant
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "getAllFiles") {
		if (isset($_REQUEST['type'])) {
			if ($_REQUEST['type'] == 'participant') {
				echo(files("participant"));
				exit;
			} elseif($_REQUEST['type'] == 'assessment') {
				echo(files("assessment"));
				exit;
			}
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getUserInfo
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getUserInfo") {
		echo getUser("current");
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getFileInfo&files=OpEBUSER5e301d61da6f8_5fc673d72eae08
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getFileInfo") {
		if (isset($_REQUEST['files'])) {
			$fn = $_REQUEST['files'];
			echo file_Info($fn);
        	exit;
		}
	}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=publish&metadata=json&fileId=fn
	elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "publish") {
		if (isset($_REQUEST['metadata']) && isset($_REQUEST['fileId'])) {
			$metadata = $_REQUEST['metadata'];
			$fn = $_REQUEST['fileId'];
			
			$doi = oeb_publish_file_eudat($fn,$metadata);
			
			//check regex in prod enviroment!!!
			if (preg_match("/b2share.\w{32}/",$doi)){
				if (registerDOIToVRE ($fn, $doi)){
					echo $doi;
				}
			}
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getRole
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getRole") {
		$block_json = json_encode($_SESSION['User']['TokenInfo']['oeb:roles'], JSON_PRETTY_PRINT);
		echo $block_json;
		exit; 
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=request&fileId=fn&msg=message
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "request") {
		if (isset($_REQUEST['fileId']) && isset($_REQUEST['msg'])) {
			$fn = $_REQUEST['fileId'];
			$msg = $_REQUEST['msg'];

			//get community TODO
			//gets associative array with key: contact_id and value: email
			$approversContacts = getContactEmail (getBenchmarkingContactsIds('OEBC004'));

			$approversContactsIds = array();
			
			foreach ($approversContacts as $key => $value) {
				sendRequestToApprover("meritxell.ferret@bsc.es", $_SESSION['User']['id'], $fn);
				array_push($approversContactsIds, $key);
			}
			
			
			$metadata = array('_id' => createLabel('oebreq', 'pubRegistersCol'), 'fileId'=>$_REQUEST['fileId'], "requester" => 
			$_SESSION['User']['id'], "approvers" => $approversContactsIds,"current_status" =>"pending approval", 
			"history_actions" => array(array("action" => 'request', "user" => $_SESSION['User']['id'], "timestamp" =>date('H:i:s Y-m-d'), "observations" => $msg)));           
			
			
			//upload to nextcloud -- TODO
			echo uploadReqRegister($fn, $metadata);
        	exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getSubmitRegisters
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getSubmitRegisters") {
		$filters = array ('requester' => $_SESSION['User']['id']);
		echo submitedRegisters($filters);
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=proceedReq&actionReq=deny&reqId=id&msg=message
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "proceedReq") {
		if (isset($_REQUEST['actionReq']) && isset($_REQUEST['reqId']) && isset($_REQUEST['msg'])) {
			echo actionRequest($_REQUEST['reqId'], $_REQUEST['actionReq'], $_REQUEST['msg']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=listOfChallenge&community_id=id
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "listOfChallenge") {
		if (isset($_REQUEST['community_id'])) {
			echo getChallengesFromACommunity($_REQUEST['community_id']);
			exit;
		}
		
	
	}
} else {
    echo '{}';
    exit;
}



/**********************************FUNCIONS****************************** */

/**
 * Gets all files of user
 * @param type of file to filter (participant, assessment)
 * @return string (json) with files information
 */
function files($type) {
	//initiallize variables
	$block_json="{}";
	$files = array();

	//get data from DB
	$proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));
	if ($type == "participant") {
		$file_filter = array(
            "data_type" => "participant",
            "project"   => $proj_name_active
    	);

	} elseif ($type == "assessment") {
		$file_filter = array(
            "data_type" => "assessment",
            "project"   => $proj_name_active
    	);

	}  
    
	$filteredFiles = getGSFiles_filteredBy($file_filter);
	
	foreach ($filteredFiles as $key => $value) {
		//get data type name for each file
		$datatype_name = getDataTypeName($value['data_type']);
		$value['datatype_name'] = $datatype_name;

		//get if file is already request to publish
		$found = $GLOBALS['pubRegistersCol']->findOne(array("fileId" => $value['_id']));
		if(count($found) !== 0) {
			$value['current_status'] = $found['current_status'];
		}
		
		//get challenge status
		//TODO
		
		array_push($files, $value);
		
	}
	
	$block_json = json_encode($files, JSON_PRETTY_PRINT);

	return $block_json;

}


/**
 * Gets file information
 * @param file id to search
 * @return string (json format) with the info of the given file.
 */
function file_Info($fn){

	$block_json="{}";
	$fileData = getGSFile_fromId($fn, "");
	$block_json = json_encode($fileData, JSON_PRETTY_PRINT);

	return $block_json;
	
}

/**
 * Gets information from mongo (to fill status files tables)
 * @param filters what to look for
 * @return string (json format) with the info of the register or empty
 */
function submitedRegisters($filters) {
	//initiallize variables
	$block_json="{}";
	$reg = array();

	//get data from DB
	try {
		$regData = $GLOBALS['pubRegistersCol']->find($filters);

		foreach ($regData as $r) {
			$user = $GLOBALS['usersCol']->findOne(array('id' => $r['requester']));
			$file = $GLOBALS['filesCol']->findOne(array('_id' => $r['fileId']));
			$r['requester_name'] = $user['Name'];
			$r['file_path'] = $file['path'];
			array_push($reg, $r) ;
		}

		$block_json = json_encode($reg, JSON_PRETTY_PRINT);

	} catch (MongoCursorException $e) {
		$_SESSION['errorMongo'] = "Error message: ".$e->getMessage()."Error code: ".$e->getCode();
	}

	return $block_json;
	
}

/**
 * Manages user action of a submited request
 * @param id the file id 
 * @param action the action to make
 * @return 1 if correctly updated in mongo, 0 otherwise. (updateReqRegister)
 */
function actionRequest($id, $action, $msg){
	//approve
	if($action == 'approve'){
		//TODO: upload to OEB and get oeb_id
		//new action
		$new_action = array(
			"action" => "approve",
			"user" => $_SESSION['User']['id'],
			"timestamp" => date('H:i:s Y-m-d'),
			"observations" => $msg
		  );
		if (updateReqRegister ($id, array('current_status' => 'approved'))) {
			if(insertAttrInReqRegister($id, $new_action)){
				return 1;
			}
		}

	}

	//deny
	elseif ($action == 'deny'){
		//new action
		$new_action = array(
			"action" => "deny",
			"user" => $_SESSION['User']['id'],
			"timestamp" => date('H:i:s Y-m-d'),
			"observations" => $msg
		  );
		if (updateReqRegister ($id, array('current_status' => 'denied'))) {
			if(insertAttrInReqRegister($id, $new_action)){
				return 1;
			}
		}
		

		
	}

	//cancel
	elseif($action == 'cancel'){
		//new action
		$new_action = array(
			"action" => "cancel",
			"user" => $_SESSION['User']['id'],
			"timestamp" => date('H:i:s Y-m-d'),
			"observations" => $msg
		  );
		if (updateReqRegister ($id, array('current_status' => 'cancelled'))) {
			if(insertAttrInReqRegister($id, $new_action)){
				return 1;
			}
		}

		
	}
	return 0;
}

/**
 * Gets possible approvers
 * @param fileId the file to approve (get the challenge first)
 * @return array of possible approvers
 */
function getApprovers($fileId) {
	$result = null;





	return $result;

}


/*
Function to know the minimal role.
*/ 

