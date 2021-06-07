<?php

///////////////////////////////////////////////////////////////////
// OEB METADATA MANAGEMENT
///////////////////////////////////////////////////////////////////

/**
 * Get list of benchmarking challenges associated to a given results file (i.e. OEB-metrics) or run folder
 * @param file_id  Identifier of file or folder in VRE
 * @return array of OEB benchmarking challenge identifiers
 */
function getChallenges_associated_to_outfile($file_id){
        $challenges_ids=array();

	//fetch execution run that generated the given file or folder
	$execution_data = getExecutionInfo_fromResultId($file_id);
        if (!count($execution_data)){
            $_SESSION['errorData']['Error'][]="Cannot infer OEB benchmarking challenges associated to the given file.";
            return $challenges_ids;
	}
	// look for the argument value of 'challenges_ids'
	$args_challenges=(isset($execution_data['arguments']['challenges_ids'])? $execution_data['arguments']['challenges_ids'] : array());

	// convert from challenge-name (GO, EC, SwissTrees...) to challenge_id -> TODO
	$challenges_ids = $args_challenges;
	return $challenges_ids;
}

///////////////////////////////////////////////////////////////////
// REGISTRY OF OEB DATASET PUBLICATIONS
///////////////////////////////////////////////////////////////////

/**
 * Lists users files elegible for being  publicated according to the OEB publication rules
 * @param datatype (string or array). Filter out files by this list of accepted type/s of dataset.
 * @return string (json) Document with an array of file entries 
 */
function getPublishableFiles($datatype) {
        //initiallize variables
        $block_json="{}";
        $files = array();
	// Find user files with the given allowed datatype/s

	//get path of the user workspace
	$proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));

	// get list of user files with the allowed datatypes
	if (is_array($datatype)){
        	$file_filter = array(
            	"data_type" => array('$in' => $datatype),
	    	"project"   => $proj_name_active
        	);
	}else{
        	$file_filter = array(
            	"data_type" => $datatype,
	    	"project"   => $proj_name_active
        	);
	}
        $filteredFiles = getGSFiles_filteredBy($file_filter);

	// apply further filters to the files based on OEB metadata
	foreach ($filteredFiles as $key => $value) {
                //check if file is already requested to be published
                $found = $GLOBALS['pubRegistersCol']->findOne(array("fileIds" => array('$in'=> array($value['_id']))));

                if(count($found) !== 0 || !is_null($found)) {
                        $value['current_status'] = $found['current_status'];
                        if (isset($found['dataset_OEBid'])){
                                $value['oeb_id'] = $found['dataset_OEBid'];
                        }

		}

                //get both files: consolidated and participant
                $value['files']['participant']['id'] = $value['input_files'][0];
                $value['files']['participant']['path'] = $value['sources'][0];
                $value['files']['consolidated']['id'] = $value['_id'];
                $value['files']['consolidated']['path'] = $value['path'];

                //get challenge names
		if ($value['data_type'] != "participant"){
			// if "consolidated" or "metrics", fetch run metadata
			$value['oeb_challenges'] = getChallenges_associated_to_outfile($value['_id']);
		}

		if (isset($value['tool'])){
			$tool = getTool_fromId($value['tool'],1);
                        if (isset($tool['community-specific_metadata'])){
                                if (isset($tool['community-specific_metadata']['benchmarking_event_id'])){
                                        $value['benchmarking_event']['be_id'] = $tool['community-specific_metadata']['benchmarking_event_id'];
                                        $value['benchmarking_event']['be_name'] = getBenchmarkingEvents($value['benchmarking_event']['be_id'],"name");
                                }else{
                                        $value['benchmarking_event']=$value['tool'];
                                }
                                $value['benchmarking_event']['workflow_id'] = $tool['community-specific_metadata']['workflow_id'];
                        }
                        
			

		}else{
			// file is not the result of a VRE run. No 'tool'/event associated
			$value['benchmarking_event']="NA";
		}
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
        $challenges = array(getChallenges_associated_to_outfile($fn));

        //source
        $fileData += array("fileSource_path" =>$GLOBALS['dataDir'].getAttr_fromGSFileId($fileData['input_files'][0] ,'path'));
        
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
                //get user submited requests
                $regData = $GLOBALS['pubRegistersCol']->find($filters);
                foreach ($regData as $v) {
                        $r['id'] = $v['_id'];
                        $user = $GLOBALS['usersCol']->findOne(array('id' => $v['requester']));
                        $r['requester_name'] = $user['Name'];
                        $r['files'] = array();
                        foreach ($v['fileIds'] as $value) {
                                $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($value,'path', 1);
                               
                                $file_name  = basename($file_path);
                                $f = ["id" => $value, "name" => $file_name, "nc_url" => getAttr_fromGSFileId($value, "nc_url", 1)];
                                array_push($r['files'], $f);
                               
                        }
                        $r['approvers'] = $v['approvers'];
                        $r['bench_event'] = $v['oeb_metadata']['benchmarking_event_id'];
                        $r['tool'] = array("tool_id" =>$v['oeb_metadata']['tool_id'], "tool_name" =>  getToolss($v['oeb_metadata']['tool_id'], "name"));
                        $r['history_actions'] = $v['history_actions'];
                        $r['status'] = $v['current_status'];
                        $r['oeb_id'] = $v['dataset_OEBid'];
                        array_push($reg, $r) ;
                }

                $block_json = json_encode($reg);

        } catch (MongoCursorException $e) {
                $_SESSION['errorMongo'] = "Error message: ".$e->getMessage()."Error code: ".$e->getCode();
                var_dump("ss");
        }
        
        return $block_json;

}

/**
 * Manages user action of a submited request, if approve push data to OEB 
 * @param id the req id 
 * @param action the action to make
 * @return Json response object
 */
function actionRequest($id, $action){
        //jsonResponse class (errors or successfully)
	$response_json = new JsonResponse();

        //approve
        if($action == 'approve'){
           //0. Input files
           //create consolidated json in temp folder
           $oeb_form = getPubRegister_fromId($id)['oeb_metadata'];
           // build temporal directories
	   $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."oeb_form";
           if (!is_dir($wd)){
                mkdir($wd);
	   }
           
           $r = file_put_contents($wd."/".$id.".json", json_encode($oeb_form));
           if (!$r) {
                // return error msg via BlockResponse
		$response_json->setCode(404);
		$response_json->setMessage("Cannnot access temporary dir or write content");

		return $response_json->getResponse();    
                
           }
           $config_json = $wd."/".$id.".json";

           //get token 
           $tk = $_SESSION['User']['Token']['access_token'];
           $_GLOBALS['OEB_submission_repository'] = "/home/user/OEB_level2_new";
           //1. Execute script to buffer: push_data_to_oeb.py -i oeb_form.json -cr oeb_token.json -tk
	   $cmd = $_GLOBALS['OEB_submission_repository']."/APP/.py3env/bin/python ".$_GLOBALS['OEB_submission_repository']."/APP/push_data_to_oeb.py -i '".$config_json."' -cr ".$_GLOBALS['OEB_submission_repository']."/APP/dev-oeb_token.json -tk '".$tk."'";
           $retvalue = my_exec($cmd);

           //2. Execute migration to oeb database: curl
           if ($retvalue['return'] != 0){
                updateReqRegister ($id, array('current_status' => 'error', "log" =>array("cmd"=> $cmd, "error" => $retvalue['stderr'])));
                $response_json->setCode(400);
                $response_json->setMessage("<b>ERROR</b> pushing datasets to OpenEBench for Request ID:<b>".$id."</b>. Cannot upload data to buffer.");

		return $response_json->getResponse();    
           }
          $response = migrateToOEB($tk);

          if (!$response) {
                updateReqRegister ($id, array('current_status' => 'error', "log" =>array("cmd"=> $cmd, "error" => $retvalue['stderr'])));
                $response_json->setCode(400);
                $response_json->setMessage("<b>ERROR</b> migration to database");

		return $response_json->getResponse();  

          }
                //get dataset umbrella
                $OEB_id = null;
                foreach ($response as $value) {
                   if ($value['orig_id'] == $id){
                     $OEB_id = $value['_id'];
                   }
                }

           //3. update VRE mongo: hisotry actions, new attr oeb_id, status published. 
           //Other registers (same user?, BE and tool), passed to obsolete
                if (!is_null($OEB_id)) {
                        try {
                                //get all registers with same BE and same tool
                                $filters = array( 'oeb_metadata.benchmarking_event_id' => $oeb_form['benchmarking_event_id'], 
                                'oeb_metadata.tool_id' => $oeb_form['tool_id'] );
                                $regData = $GLOBALS['pubRegistersCol']->find($filters);
                
                                foreach ($regData as $doc) {
                                        updateReqRegister ($doc['_id'], array('current_status' => 'obsolete'));
                                }
                        
                                $new_action = array(
                                        "action" => "approve",
                                        "user" => $_SESSION['User']['id'],
                                        "timestamp" => date('H:i:s Y-m-d')
                                );
                                if (updateReqRegister ($id, array('current_status' => 'published', "dataset_OEBid" => $OEB_id))) {
                                        if(insertAttrInReqRegister($id, $new_action)){
                                                //notify requester
                                                newNotification(array('_id' => createLabel('oeb-not', 'oebNotificationsCol'), "receiver" => getPubRegister_fromId($id)['requester'], 
                                                "content" => "Your request has been approved: ".$id, "created_at"=>  new \MongoDB\BSON\UTCDateTime(),"is_seen" => 0));
                                                $response_json->setCode(200);
			                        $response_json->setMessage("Successfully");
                                        }
                                }

                        } catch (MongoCursorException $e) {

                                $response_json->setCode(500);
                                $response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
                                return $response_json->getResponse();
                        }
                }
        }

        //deny
        else{
                try {
                         //new action
                        $new_action = array(
                                "action" => $action,
                                "user" => $_SESSION['User']['id'],
                                "timestamp" => date('H:i:s Y-m-d')
                        );
                        if ($action == 'deny'){
                                updateReqRegister ($id, array('current_status' => 'denied'));
                                //notify requester
                                newNotification(array('_id' => createLabel('oeb-not', 'oebNotificationsCol'), "receiver" => getPubRegister_fromId($id)['requester'], 
                                "content" => "Your request has been denied: ".$id, "created_at"=> new \MongoDB\BSON\UTCDateTime(),"is_seen" => 0));

                        } elseif ($action == 'cancel'){
                                updateReqRegister ($id, array('current_status' => 'cancelled'));
                        }
                        if (insertAttrInReqRegister($id, $new_action)){
                                $response_json->setCode(200);
			        $response_json->setMessage("Successfully");
                        }
                        

               
                } catch (MongoCursorException $e) {

                        $response_json->setCode(500);
                        $response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
                        return $response_json->getResponse();
                }
               
        }
        return $response_json->getResponse();
}


/**
 * MAnages petition: upload files to nextcloud, registers petitions in mongo, notify approvers.
 * @param fileId - id of the file
 * @param metaForm - form data
 * @param type - participant or consolidated
 * @return Json response object
 */
function proceedRequest_register_NC($fileId, $metaForm, $type) {
        //type: TODO in future releases

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
        $upload_participant_nc = true;
        $url_participant ="";
	
	if ($url_participant = getAttr_fromGSFileId($participantFile_id, "nc_url")){
		//update form: participant_source from nc_url
		$form['participant_file']  = $n;
                $upload_participant_nc = false;
	}
	
	//2. Gets APPROVERS
	//gets associative array with key: contact_id and value: email
	$approversContacts = getContactEmail (getCommunities($community, "community_contact_ids"));
	$approversContactsIds = array();
	foreach ($approversContacts as $key => $value) {
		array_push($approversContactsIds, $key);
	}
        //TODO: delete for prod:
        array_push($approversContactsIds,"Meritxell.Ferret");

	//3. REGISTER the petition in mongo

	$metadata = array('_id' => createLabel('vre-oebreq', 'pubRegistersCol'), 'fileIds'=>array($fileId,$participantFile_id), "requester" => 
		$_SESSION['User']['id'], "approvers" => $approversContactsIds,"current_status" =>"pending approval", 
		"history_actions" => array(array("action" => 'request', "user" => $_SESSION['User']['id'], "timestamp" =>date('H:i:s Y-m-d'))),
		"oeb_metadata" => $form);           
	$req_id = uploadReqRegister($fileId, $metadata);
	$log ="";
	if ($req_id === 0) {
		// return error msg via BlockResponse
		$response_json->setCode(400);
		$response_json->setMessage("Error creating request.");

		return $response_json->getResponse();
	}else {
                $log = "Petition successfully created with identifier: ".$req_id;
        }


	//4. UPLOAD to nextcloud and get url share link
	// UPLOAD consolidated file
	$url_consolidated =ncUploadFile("https://dev-openebench.bsc.es/nextcloud/", $fileId, $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name);

	//UPLOAD participant file (in case url is needed)
        if ($upload_participant_nc){
                $url_participant = ncUploadFile("https://dev-openebench.bsc.es/nextcloud/", $participantFile_id, $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name);
        }
	

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
		updateReqRegister ($req_id, array('current_status' => 'error', 'log' => array("error" => "Error uploading files to nextlcloud")));

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

		//6.Notify approvers --> TODO: now approver: Meritxell. Get user id (VRE) from contact id to get notification
                newNotification(array('_id' => createLabel('oeb-not', 'oebNotificationsCol'), "receiver" => 'OpEBUSER5fd78d4e6585e', 
                "content" => "New pending approval request ".$req_id, "created_at"=> new \MongoDB\BSON\UTCDateTime(),"is_seen" => 0));
		if (!sendRequestToApprover("meritxell.ferret@bsc.es", $_SESSION['User']['Name'], $req_id)){
			// return error msg via BlockResponse
			$response_json->setCode(500);
			$response_json->setMessage("Error sending email to approvers.");
			//updateReqRegister ($req_id, array('current_status' => 'error', 'log' => $response_json->getResponse()));

			return $response_json->getResponse();
		}

		//7.return JSON RESULT with all url files

		$response = array("files" => array($fileId => $url_consolidated, $participantFile_id => $url_participant,
			$id_tar => $url_tar), "petition"=>$req_id, "email" => "Approvers correctly notified.");
		$response_json->setCode(200);
		$response_json->setMessage($response);

	} catch (MongoCursorException $e) {

		$response_json->setCode(500);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}
	
	return $response_json->getResponse();
}

/**
 * Executes an external script
 * @param $cmd - entire comand to execute
 * @param $input
 * @return array with standard error, standard output, and return number execution
 */
function my_exec($cmd, $input=''){
        $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $stdout=stream_get_contents($pipes[1]);
        fclose($pipes[1]);
          
        $stderr=stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $rtn=proc_close($proc);
        return array('stdout'=>$stdout,
                       'stderr'=>$stderr,
                       'return'=>$rtn
                      );
}
/**
 * GEts information from OEB api necessary to fill the form
 * @param BE_id - benchamrking event id
 * @return json with community_id and oeb_workflow attributes
 */
function getOEBdataFromBenchmarkingEvent ($BE_id) {
	$block_json = '{}';
	$result = array();
	$result["community_id"] = getBenchmarkingEvents($BE_id, "community_id");

	$tool = getTool_fromId($value['tool'],1);
	$result["oeb_workflow"] = $tool["workflow_id"];
	$block_json = json_encode($result, JSON_PRETTY_PRINT);

	return $block_json;


}
