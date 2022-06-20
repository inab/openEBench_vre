<?php

//Controller of publication worklfow
require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getAllFiles&type=
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == "getAllFiles" && isset($_REQUEST['type'])) {
		echo getPublishableFiles(json_decode($_REQUEST['type']));
		exit;

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
			if (isset($_SESSION['User']['linked_accounts']['b2share']['access_token'])){
				echo oeb_publish_file_eudat($fn,$metadata, $_SESSION['User']['linked_accounts']['b2share']['access_token']);
			}
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getRole
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getRole") {
		$block_json ="{}";
		$roles = array();
		if(isset($_SESSION['User']['TokenInfo']['oeb:roles'])){
			$roles["roles"] = getBEFromRoles($_SESSION['User']['TokenInfo']['oeb:roles']);
		} else $roles["roles"] = [];
		
		if (isset($_SESSION['User']['linked_accounts']['b2share']['access_token'])){
			$roles["tokenEudat"]=true;
		} 
		$block_json = json_encode($roles, JSON_PRETTY_PRINT);
		echo $block_json;
		exit; 
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=requestPublish&fileId=fn
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "requestPublish") {
		if (isset($_REQUEST['fileId']) && isset($_REQUEST['metadata'])) {
			$fn = $_REQUEST['fileId'];
			$metadataForm = $_REQUEST['metadata'];
			echo proceedRequest_register_NC($fn, $metadataForm);
			exit; 
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getSubmitRegisters
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getSubmitRegisters") {
		$filters = array ('requester' => $_SESSION['User']['id']);
		echo submitedRegisters($filters);
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=proceedReq&actionReq=deny&reqId=id&reason=blabla
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "proceedReq") {
		if (isset($_REQUEST['actionReq']) && isset($_REQUEST['reqId'])) {
			if(isset($_REQUEST['reason'])) {
				echo actionRequest($_REQUEST['reqId'], $_REQUEST['actionReq'], $_REQUEST['reason']);
			} else echo actionRequest($_REQUEST['reqId'], $_REQUEST['actionReq']);
		}
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=listOfBE&community_id=id
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "listOfBE") {
		if (isset($_REQUEST['community_id'])) {
			echo getBenchmarkingEventsQL($_REQUEST['community_id']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=listOfChallenge&BE_id=id
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "listOfChallenge") {
		if (isset($_REQUEST['BE_id'])) {
			echo getChallengesQL($_REQUEST['BE_id']);
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
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getContacts
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getContacts") {
		echo getAllContacts();
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getContactOEB
	}elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getContactOEB") {
		echo getContactsIds($_SESSION['User']['Email']);
		exit;
		
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getApprovalRequest
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getApprovalRequest") {
			$filters = array ('approvers' => array('$in' => array($_SESSION['User']['Email'])));
			echo submitedRegisters($filters);
			exit;
	
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getLog&reqId=id
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getLog") {
		if (isset($_REQUEST['reqId'])) {
			echo json_encode(OEBDataPetition::selectAllOEBPetitions(array("_id" => $_REQUEST['reqId']))[0]['history_actions']);
			exit;
		}
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getNotAutomaticBE
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getNotAutomaticBE") {
		echo json_encode(getBenchEventidsNotAutomatic());
		exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getNumPetitions&BEId=id
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getNumNotAutoPetitions") {
		if (isset($_REQUEST['BEId'])) {
			echo json_encode(getNumOfPetitionBench($_SESSION['User']['id'], $_REQUEST['BEId'], true));
			exit;
		}
		
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'importFromUrl'){
		if (isset($_REQUEST['fileId'])) {
			echo getData_fromURL(getAttr_fromGSFileId($_REQUEST['fileId'], "urls", 1)[0]['url'], array("data_type" => "participant"));
			exit;
		}
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'getNCFile'){
		if (isset($_REQUEST['fn'])) {
			set_time_limit(0); 
			ini_set('memory_limit', '512M');
			$url = getAttr_fromGSFileId($_REQUEST['fn'], "urls", 1)[0]['url'];
			downloadFile($url);
			exit(0);
		}
	} elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == 'toolToSubmit'){
		if (isset($_REQUEST['metadata'])) {
			$form = json_decode($_REQUEST['metadata'], true);
			if(checkToolAlreadySubmitted($form['tool_id'], $form['benchmarking_event_id'])){
				echo "1";
			} else echo "0";
			
		}
	}
} else {
    echo '{}';
    exit;
}



