<?php

//Manages the backend request of publication tabs

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getAllFiles&type=
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
			echo oeb_publish_file_eudat($fn,$metadata, $_SESSION['User']['linked_accounts']['b2share']['access_token']);
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
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getApprovalRequest
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getApprovalRequest") {
		if (getContactsIds($_SESSION['User']['Email'])){
			$filters = array ('approvers' => array('$in' => array(getContactsIds($_SESSION['User']['Email']))));
			echo submitedRegisters($filters);
			exit;
		}else echo '{}';exit;
	//https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getLog&reqId=id
	}elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "getLog") {
		if (isset($_REQUEST['reqId'])) {
			echo getPubRegister_fromId($_REQUEST['reqId'])['log']['error'];
			exit;
		}
	}
} else {
    echo '{}';
    exit;
}



