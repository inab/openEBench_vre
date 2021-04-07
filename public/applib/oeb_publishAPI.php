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
				//sendRequestToApprover("meritxell.ferret@bsc.es", $_SESSION['User']['id'], $fn);
				array_push($approversContactsIds, $key);
			}
			
			
			$metadata = array('_id' => createLabel('oebreq', 'pubRegistersCol'), 'fileId'=>$_REQUEST['fileId'], "requester" => 
			$_SESSION['User']['id'], "approvers" => $approversContactsIds,"current_status" =>"pending approval", 
			"history_actions" => array(array("action" => 'request', "user" => $_SESSION['User']['id'], "timestamp" =>date('H:i:s Y-m-d'), "observations" => $msg)));           
			
			
			//upload to nextcloud 
			//new nc_connection
			if ($result = ncUploadFile("", $fn, "Drop", array('{http://owncloud.org/ns}vre_id' => $fn))){
				$r = uploadReqRegister($fn, $metadata);
				if ($r) {
					//register to mongo: files_metadates
					$newMetadata = array("nc_upload" => array(array("response" => "resposta", "file" => "fileinfo")));
					if(addMetadataBNS($fn,$newMetadata) ){
						echo "1";
						exit;
					} else {
						echo "0";
						exit;
					}
				} else {
					echo "0";
					exit;
				}
			} else {
				echo "0";
				exit;
			}


        	
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

