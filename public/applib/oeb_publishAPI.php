<?php

//Manages the backend request of publication tabs

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getAllFiles&type=participant
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "getAllFiles" && isset($_REQUEST['type'])) {
		echo(getPublishableFiles($_REQUEST['type']));

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
			
			//inlcude token parameter
			$doi = oeb_publish_file_eudat($fn,$metadata, "");

			//check regex in prod enviroment!!!
			if (preg_match("/b2share.\w{32}/",$doi)){
				registerDOIToVRE ($fn, $doi);
				exit;
			
			}
			
			exit;
			
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getRole
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getRole") {
		$block_json = json_encode($_SESSION['User']['TokenInfo']['oeb:roles'], JSON_PRETTY_PRINT);
		echo $block_json;
		exit; 
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=requestPublish&fileId=fn
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "requestPublish") {
		if (isset($_REQUEST['fileId']) && isset($_REQUEST['metadata'])) {
			$fn = $_REQUEST['fileId'];
			$metadataForm = $_REQUEST['metadata'];
			echo proceedRequest_register_NC($fn, $metadataForm, "");
			exit; 
		}else {
			echo "{}";
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getSubmitRegisters
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getSubmitRegisters") {
		$filters = array ('requester' => $_SESSION['User']['id']);
		echo submitedRegisters($filters);
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=proceedReq&actionReq=deny&reqId=id
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "proceedReq") {
		if (isset($_REQUEST['actionReq']) && isset($_REQUEST['reqId'])) {
			echo actionRequest($_REQUEST['reqId'], $_REQUEST['actionReq']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=listOfChallenge&community_id=id
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "listOfChallenge") {
		if (isset($_REQUEST['community_id'])) {
			echo getChallengesFromACommunity($_REQUEST['community_id']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getOEBdata
	} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getOEBdata") {
		if (isset($_REQUEST['benchmarkingEvent'])) {
			echo getOEBdataFromBenchmarkingEvent($_REQUEST['benchmarkingEvent']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getTools
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getTools") {
		echo getTools();
		exit; 
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getContacts&community_id=id	
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getContacts") {
		if (isset($_REQUEST['community_id'])) {
			echo getAllContactsOfCommunity($_REQUEST['community_id']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getPublishedFiles
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getPublishedFiles") {
		
	}
} else {
    echo '{}';
    exit;
}

function getOEBdataFromBenchmarkingEvent ($BE_id) {
	$block_json = '{}';
	$result = array();
	$result["community_id"] = getBenchmarkingEvents($BE_id, "community_id");

	$tool = getTool_fromId($value['tool'],1);
	$result["oeb_workflow"] = $tool["workflow_id"];
	$block_json = json_encode($result, JSON_PRETTY_PRINT);

	return $block_json;


}



/**
 * 
 * @param type - participant or consolidated
 */
function proceedRequest_register_NC($fileId, $metaForm, $type) {

	//jsonResponse class (errors or successfully)
	$response_json = new JsonResponse();

	//GET data: from meta Form
	$form = json_decode($metaForm, true);
	$community = $form['community_id'];
	$benchmarkingEvent_id = $form['benchmarking_event_id'];

	$executionfolder_id = getAttr_fromGSFileId($fileId, "parentDir");
	$executionfolder_name = basename(getAttr_fromGSFileId($executionfolder_id, "path"));
	$participantFile_id = getAttr_fromGSFileId($fileId, "input_files")[0];

	//1. check if associated participant has nc_url in mongo VRE
	/*
	if ($n = getAttr_fromGSFileId($participantFile_id, "nc_url")){
		//update form: participant_source from nc_url
		$form['participant_file']  = $n;
	}
	*/
	
	//2. Gets APPROVERS
	//gets associative array with key: contact_id and value: email
	$approversContacts = getContactEmail (getCommunities($community, "community_contact_ids"));
	$approversContactsIds = array();
	foreach ($approversContacts as $key => $value) {
		array_push($approversContactsIds, $key);
	}

	//3. REGISTER the petition in mongo

	$metadata = array('_id' => createLabel('vre-oebreq', 'pubRegistersCol'), 'fileIds'=>array($fileId,$participantFile_id), "requester" => 
		$_SESSION['User']['id'], "approvers" => $approversContactsIds,"current_status" =>"pending approval", 
		"history_actions" => array(array("action" => 'request', "user" => $_SESSION['User']['id'], "timestamp" =>date('H:i:s Y-m-d'))),
		"oeb_metadata" => $form);           
	$req_id = uploadReqRegister($fileId, $metadata);
	
	if ($req_id === 0) {
		// return error msg via BlockResponse
		$response_json->setCode(400);
		$response_json->setMessage("Error creating request.");

		return $response_json->getResponse();
	}


	//4. UPLOAD to nextcloud and get url share link
	// UPLOAD consolidated file
	$url_consolidated =ncUploadFile("https://dev-openebench.bsc.es/nextcloud/", $fileId, $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name);

	//UPLOAD participant file (in case url is needed)
	$url_participant = ncUploadFile("https://dev-openebench.bsc.es/nextcloud/", $participantFile_id, $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name);

	//UPLOAD tar file
	$filter =  array("data_type" => "tool_statistics" , "parentDir"   => $executionfolder_id);
	$files_list = getGSFiles_filteredBy($filter);

	$simpleArray = array();
	foreach ($files_list as $value){
		$simpleArray = $value;
	}
	$id_tar = $simpleArray['_id'];
	$url_tar =ncUploadFile("https://dev-openebench.bsc.es/nextcloud/", $id_tar, $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name);
		
	if (is_null($url_consolidated) || is_null($url_participant) || is_null($url_tar)) {
		// return error msg via BlockResponse
		$response_json->setCode(500);
		$response_json->setMessage("Error uploading file to nextcloud.");
		//updateReqRegister ($req_id, array('current_status' => 'error', 'log' => $response_json->getResponse()));

		return $response_json->getResponse();
	}	

	//5. EDIT metadata: to paricipant (in case url is needed) and consolidated file in mongo
	try{
		addMetadataBNS($fileId, array("nc_url" => $url_consolidated."/download"));
		addMetadataBNS($participantFile_id, array("nc_url" => $url_participant));

		//EDIT metadata form: add url nc to participant (in case not url) and consolidated
		$form['participant_file']  = $url_participant;
		$form['consolidated_oeb_data']  = $url_consolidated."/download";
		$form['dataset_submission_id'] = $req_id;
		updateReqRegister($req_id,array("oeb_metadata" => $form));

		//EDIT metadata for petition: add attr visualitzation_file: uri and 
		updateReqRegister($req_id,array("visualitzation_url" => $url_tar));

		//6.Notify approvers
		//sendRequestToApprover("txellfe@gmail.com", $_SESSION['User']['id'], $fileId);

		//7.return JSON RESULT with all url files
		//TODO: show approvers to user

		$response = array("files" => array($fileId => $url_consolidated, $participantFile_id => $url_participant,
			$id_tar => $url_tar), "petition"=>$req_id);
		$response_json->setCode(200);
		$response_json->setMessage($response);

	} catch (MongoCursorException $e) {

		$response_json->setCode(500);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}
	
	return $response_json->getResponse();
}
