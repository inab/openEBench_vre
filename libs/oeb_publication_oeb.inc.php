<?php

///////////////////////////////////////////////////////////////////
// OEB METADATA MANAGEMENT
///////////////////////////////////////////////////////////////////

/**
 * Get list of benchmarking challenges associated to a given results file 
 * (i.e. OEB-metrics) or run folder
 * @param file_id  Identifier of file or folder in VRE
 * @return array of OEB benchmarking challenge identifiers
 */
function getChallenges_associated_to_outfile($file_id){
    $challenges_ids=array();

    //fetch execution run that generated the given file or folder
    $execution_data = getExecutionInfo_fromResultId($file_id);
    if (!count($execution_data)){
        $_SESSION['errorData']['Error'][]="Cannot infer OEB benchmarking 
        challenges associated to the given file.";
        return $challenges_ids;
    }
    // look for the argument value of 'challenges_ids'
    $args_challenges=(isset($execution_data['arguments']['challenges_ids'])? 
    $execution_data['arguments']['challenges_ids'] : array());

    // convert from challenge-name (GO, EC, SwissTrees...) to challenge_id -> TODO
    $challenges_ids = $args_challenges;
    return $challenges_ids;
}

///////////////////////////////////////////////////////////////////
// REGISTRY OF OEB DATASET PUBLICATIONS
///////////////////////////////////////////////////////////////////

/**
 * Lists users files elegible for being  publicated according to the 
 * OEB publication rules
 * @param datatype (array). Filter out files by this list of 
 * accepted type/s of dataset.
 * @return string (json) Document with an array of file entries 
 */
function getPublishableFiles(array $datatype) {
    //initiallize variables
    $block_json="{}";
    $files = array();
	// Find user files with the given allowed datatype/s

	//get path of the user workspace
	$proj_name_active = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], 
                                                                        "path"));

	// get list of user files with the allowed datatypes
	if (is_array($datatype)){
        $file_filter = array(
            "data_type" => array('$in' => $datatype),
            "project"   => $proj_name_active);
	}else {
        $file_filter = array(
            "data_type" => $datatype,
            "project"   => $proj_name_active);
	}
    $filteredFiles = getGSFiles_filteredBy($file_filter);

	// apply further filters to the files based on OEB metadata
	foreach ($filteredFiles as $key => $value) {
        //check if file is already requested 
        $filters = array("filesIds" => array('$in'=> array($value['_id'])));
        $found = OEBDataPetition::selectAllOEBPetitions($filters);

        if(count($found) !== 0 || !is_null($found)) {
            $value['current_status'] = $found[0]['current_status'];
            $value['req_id'] = $found[0]['_id'];
            if (isset($found[0]['dataset_OEBid'])){
                $value['oeb_id'] = getAttr_fromGSFileId($value['_id'], 
                                                            "OEB_dataset_id");
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
                    $value['benchmarking_event']['be_id'] = 
                        $tool['community-specific_metadata']['benchmarking_event_id'];
                    $value['benchmarking_event']['be_name'] = 
                        getBenchmarkingEvents($value['benchmarking_event']['be_id'],"name");
                }else{
                    $value['benchmarking_event'] = $value['tool'];
                }
                $value['benchmarking_event']['workflow_id'] = 
                    $tool['community-specific_metadata']['workflow_id'];
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
    $fileData += array("fileSource_path" =>$GLOBALS['dataDir']
                    .getAttr_fromGSFileId($fileData['input_files'][0] ,'path'));
    
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
        $regData = OEBDataPetition::selectAllOEBPetitions($filters);
        
        foreach ($regData as $v) {
            $r['id'] = $v['_id'];
            $r['view'] = array("dir" => getAttr_fromGSFileId($v['filesIds'][0], "parentDir"), 
                    "vre-tool" => getAttr_fromGSFileId($v['filesIds'][0], "tool"));
            $user = UsersDAO::selectUsers(array('id' => $v['requester']))[0];
            $r['requester_name'] = $user['Name'];
            $r['files'] = array();

            foreach ($v['filesIds'] as $value) {
                    $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($value,'path', 1);
                    $file_name  = basename($file_path);
                    $f = ["id" => $value, "name" => $file_name];
                    array_push($r['files'], $f);
            }
            $r['approvers'] = $v['approvers'];
            $r['benchmarking_event']['be_id'] = $v['oeb_metadata']['benchmarking_event_id'];
            $r['benchmarking_event']['be_name'] = 
                getBenchmarkingEvents($r['benchmarking_event']['be_id'],"name");
            $r['community'] = $v['oeb_metadata']['community_id'];
            $r['tool'] = array("tool_id" =>$v['oeb_metadata']['tool_id'], 
                "tool_name" =>  getToolss($v['oeb_metadata']['tool_id'], "name"));
            $r['history_actions'] = $v['history_actions'];
            $r['status'] = $v['current_status'];
            $r['oeb_id'] = $v['dataset_OEBid'];
            array_push($reg, $r) ;
        }

        $block_json = json_encode($reg);

    } catch (MongoCursorException $e) {
        $_SESSION['errorMongo'] = "Error message: ".$e->getMessage()."Error code: ".$e->getCode();
    }
    
    return $block_json;
}

/**
 * Manages user action of a submited request, if approve push data to OEB 
 * @param id the req id 
 * @param action the action to make
 * @return Json response object
 */
function actionRequest($id, $action, $message = null){

    //jsonResponse class (errors or successfully)
	$response_json = new JsonResponse();
    $log = [];
    $hist_act = new historyActions($action, $_SESSION['User']['id'], $log);

    //approve
    if($action === 'approve'){
        $oeb_form = OEBDataPetition::selectAllOEBPetitions(array('_id' => $id))[0]['oeb_metadata'];

        //check if BE is automatic publication or not
        $auto = $GLOBALS['toolsCol']->find(array(
            'community-specific_metadata.benchmarking_event_id' => $oeb_form['benchmarking_event_id']));
        if ($auto->toArray()[0]['community-specific_metadata']['publication_scope'] == "oeb"){
        //PUBLICATION TO OEB AUTOMATIC (LEVEL 2)
            //0. Input files
            //create consolidated json in temp folder
            // build temporal directories
            $wd  = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']
                            ['activeProject']."/".$GLOBALS['tmpUser_dir']."oeb_form";
            if (!is_dir($wd)){
                mkdir($wd);
            }
            
            $r = file_put_contents($wd."/".$id.".json", json_encode($oeb_form));
            if (!$r) {
                array_push($log, "Cannnot access temporary dir or write content.");
                OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                                array('$set' => array("current_status" => "error")));
                
            // return error msg via BlockResponse
            $response_json->setCode(404);
            $response_json->setMessage($log[0]);
                    
            } else {
                $config_json = $wd."/".$id.".json";
                array_push($log, "Temporary user directory: ".$config_json);

                //get token 
                $tk = $_SESSION['User']['Token']['access_token'];

                //1. Execute script to buffer
                $cmd = $GLOBALS['OEB_submission_repository']."/.py3env/bin/python "
                    .$GLOBALS['OEB_submission_repository']."/APP/push_data_to_oeb.py -i '"
                    .$config_json."' -cr ".$GLOBALS['OEB_submission_repository']
                    ."/APP/dev-oeb-token.json -tk '".$tk."'";
                $retvalue = my_exec($cmd);
                $log['cmd'] = $cmd;
                $log['stderr'] = $retvalue['stderr'];

                //2. Execute migration to oeb database: curl
                if ($retvalue['return'] != 0){
                    array_push($log, "Error pushing datasets");
                    OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                            array('$set' => array("current_status" => "error")));
                    
                    $response_json->setCode(400);
                    $response_json->setMessage("<b>ERROR</b> pushing datasets to 
                        OpenEBench for Request ID:<b>".$id."</b>. Cannot upload data to buffer.");

                } else {
                    //array_push($log, "Data correctly pushed to OEB staged-database.");
                        
                    $response = migrateToOEB($tk);

                    if (!$response) {
                        array_push($log, "Error migration to database");
                        OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                            array('$set' => array("current_status" => "error")));
                        
                        $response_json->setCode(400);
                        $response_json->setMessage("<b>ERROR</b> migration to database");
                
                    }else {
                        //get dataset umbrella
                        $OEB_id = null;
                        $participant_OEBid = null;
                        //$partAssess_OEBid = null;
                        foreach ($response as $value) {
                            if ($value['orig_id'] == $id){
                                    $OEB_id = $value['_id'];
                            }elseif (preg_match("/_P/", $value['orig_id'])){
                                    $participant_OEBid = $value['_id'];
                            }
                            /*elseif (preg_match("/ParticipantAssessments/", $value['orig_id'])){
                                    $partAssess_OEBid = $value['_id'];
                            }
                            */
                        }

                        //3. update VRE mongo: hisotry actions, new attr oeb_id, status approved. 
                        //Other registers (same user?, BE and tool), passed to obsolete
                        if (is_null($OEB_id)) {
                                array_push($log, "Error getting umbrella dataset");
                                OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                                    array('$set' => array("current_status" => "error")));
                                $response_json->setCode(400);
                        }else {
                            try {
                                //get all registers with same BE and same tool and set current status obsolete
                                $filters = array( 'oeb_metadata.benchmarking_event_id' => $oeb_form['benchmarking_event_id'], 
                                'oeb_metadata.tool_id' => $oeb_form['tool_id'] );
                                $regData = OEBDataPetition::selectAllOEBPetitions($filters);

                                foreach ($regData as $doc) {
                                    OEBDataPetition::updateOEBPetitions(array('_id' => $doc['_id']), 
                                    array('$set' => array('current_status' => 'obsolete')));
                                }

                                //save OEB id on consolidated file (get its id first, first element on array of files)
                                $part_assessId = OEBDataPetition::selectAllOEBPetitions(array('_id' => $id))[0]['filesIds'][0];
                                addMetadataBNS($part_assessId, array("OEB_dataset_id" => $OEB_id));

                                array_push($log, "Successfully getting umbrella dataset: ".$OEB_id);
                                //update req with datasets OEB ids
                                $result = OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                                array('$set' => array('current_status' => 'approved', 
                                    'dataset_OEBid'=> array($OEB_id, $participant_OEBid ))));
                            
                                //notify requester
                                $n = new Notification(OEBDataPetition::selectAllOEBPetitions(
                                    array('_id' => $id))[0]['requester'], "OEB data publication: Your 
                                    request has been approved: <br><b>".$id."</b>", "oeb_publish/oeb/oeb_manageReq.php#".$id);
                                $n->saveNotification();

                                //email requester
                                $params = array();
                                $params['requester'] = UsersDAO::selectUsers(array(
                                    'id' => OEBDataPetition::selectAllOEBPetitions(array('_id' => $id))[0]['requester']))[0]['Email'];
                                $params['reqId'] = $id;
                                sendUpdateApproveRequester($params);

                                $response_json->setCode(200);
                                $response_json->setMessage("Data successfully approved");
                                        

                            } catch (MongoCursorException $e) {
                                $response_json->setCode(500);
                                $response_json->setMessage("Cannot update data in Mongo. 
                                    Mongo Error(".$e->getCode()."): ".$e->getMessage());
                                return $response_json->getResponse();
                            }
                        }
                    }
                }
            }
        } else {
            //BE not to submit to OEB (not automatic)
            //1. check num_user_requests < == max_req for that BE
                if (!userWithMaxRequests(OEBDataPetition::selectAllOEBPetitions(
                    array('_id' => $id))[0]['requester'], $oeb_form['benchmarking_event_id'])) {

                    //2. Procees req: update VRE request in mongo: hisotry actions, new attr oeb_id, status approved. 
                    $result = OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                        array('$set' => array('current_status' => 'approved')));

                    //3. Notify requester
                    $n = new Notification(OEBDataPetition::selectAllOEBPetitions(
                    array('_id' => $id))[0]['requester'], "OEB data publication: Your 
                    request has been approved: <br><b>".$id."</b>", "oeb_publish/oeb/oeb_manageReq.php#".$id);
                    $n->saveNotification();

                    //email requester
                    $params = array();
                    $params['requester'] = UsersDAO::selectUsers(array(
                    'id' => OEBDataPetition::selectAllOEBPetitions(array('_id' => $id))[0]['requester']))[0]['Email'];
                    $params['reqId'] = $id;
                    sendUpdateApproveRequester($params);

                    $response_json->setCode(200);
                    $response_json->setMessage("Data successfully approved");

                }
        }

    }else{
        try {
            if ($action === 'deny'){
                $log['reason'] = $message;
                array_push($log, "Data successfully denied");
                OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                    array('$set' => array("current_status" => "denied")));
        
                //notify requester['requester']
                $n = new Notification(OEBDataPetition::selectAllOEBPetitions(
                    array('_id' => $id))[0]['requester'], "OEB data publication: 
                    Your request has been denied: <br><b>".$id."</b>", 
                    "oeb_publish/oeb/oeb_manageReq.php#".$id);
                $n->saveNotification();
                    
            } elseif ($action === 'cancel'){
                array_push($log, "Data successfully cancelled");
                OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
                    array('$set' => array('current_status' => 'cancelled')));
            }
                
            $response_json->setCode(200);
            $response_json->setMessage($log[0]);
               
        } catch (MongoCursorException $e) {

            $response_json->setCode(500);
            $response_json->setMessage("Cannot update data in Mongo. 
                Mongo Error(".$e->getCode()."): ".$e->getMessage());
            return $response_json->getResponse();
        }
               
    }
    $hist_act->setLog($log);
    OEBDataPetition::updateOEBPetitions(array('_id' => $id), 
        array('$push' => array("history_actions" => $hist_act->toArray())));

    return $response_json->getResponse();
}


/**
 * MAnages petition: upload files to nextcloud, 
 * registers petitions to mongo, notify approvers.
 * @param fileId - id of the file json output from workflow
 * @param metaForm - form data
 * @return Json response object
 */
function proceedRequest_register_NC($fileId, $metaForm) {
    
	//jsonResponse class (errors or successfully)
	$response_json = new JsonResponse();

    $log = [];

	//GET data: from meta Form
	$form = json_decode($metaForm, true);
	$community = $form['community_id'];
	$benchmarkingEvent_id = $form['benchmarking_event_id'];

    $oeb_metadata_tool = $GLOBALS['toolsCol']->find(array(
        'community-specific_metadata.benchmarking_event_id' => $benchmarkingEvent_id))->toArray();
    $datatype_files_publish = $oeb_metadata_tool[0]['community-specific_metadata']['publishable_files'];

	$executionfolder_id = getAttr_fromGSFileId($fileId, "parentDir");
	$executionfolder_name = str_replace(' ', '', 
        basename(getAttr_fromGSFileId($executionfolder_id, "path")));
    
    $filesToUpload = [];
    foreach ($datatype_files_publish as $datatype) {
        if ($datatype == "participant") {
            $filesToUpload[$datatype] = getAttr_fromGSFileId($fileId, "input_files")[0];

        } elseif ($datatype == "participant_validated") {
            if (getAttr_fromGSFileId($fileId, "data_type") == $datatype){
                $filesToUpload[$datatype] = $fileId;
            }

        }elseif ($datatype == "OEB_data_model") {
            if (getAttr_fromGSFileId($fileId, "data_type") == $datatype){
                $filesToUpload[$datatype] = $fileId;
            }

        }elseif ($datatype == "tool_statistics") {
            $filter =  array("data_type" => $datatype , "parentDir" => $executionfolder_id);
            $tar_id = getGSFiles_filteredBy($filter)[0]['_id'];
            $filesToUpload[$datatype] = $tar_id;
        }
    }

    //2. Gets APPROVERS
    $approversEmails =  array();
    $orcids = getOEBRoles($benchmarkingEvent_id)['manager'];
    foreach ($orcids as $value) {
            array_push($approversEmails, getUserEmailFromORCID($value)['email'][0]);
    }

    //3. CREATE petition object
    //create new obj petition
    $newRequest = new OEBDataPetition (array_values($filesToUpload), $_SESSION['User']['id'], 
                                        $approversEmails, $form);
    $req_id = $newRequest->getId();


    //4. UPLOAD to nextcloud and get url 
    $conn = new nc_Connection();
    $pathNC = $community."/".$benchmarkingEvent_id."/".$_SESSION['User']['id']."/".$executionfolder_name;
    $filesurls = [];
    
    foreach ($filesToUpload as $key => $fileId) {
        if (!$url_file = getAttr_fromGSFileId($fileId, "urls")[0]['url']){
            // UPLOAD file
            $url_file = $conn->ncUploadFile($fileId, $pathNC);
            $filesurls[$fileId] = $url_file;
            //save url nc to mongo 
            addMetadataBNS($fileId, array("urls" => array(array("repository"=> 
                array("type"=>"nc", "server"=>"dev-openebench.bsc.es/nextcloud"),
                "url"=> $url_file))));
        }else {
            $filesurls[$fileId] = $url_file;
        }
    }
    
    //error uploading files
	if (in_array(false, array_values($filesurls))) {
        array_push($log, "Petition created with identifier: ".$req_id);
        array_push($log, "Error uploading files to nextcloud.");
        $newRequest->setCurrentStatus("error");
                
		// return error msg via BlockResponse
		$response_json->setCode(400);
		$response_json->setMessage($log);
               
	} else {
        array_push($log, "Petition created with identifier: ".$req_id);
        array_push($log, "Files successfully uploaded to Nextcloud.");
        //EDIT metadata form
        $form['participant_file']  = $filesurls[$filesToUpload["participant"]];
        $form['consolidated_oeb_data']  = $filesurls[$filesToUpload["OEB_data_model"]];
        $form['dataset_submission_id'] = $req_id;
        
        $newRequest->setOebMetadata($form);
        $newRequest->setVisualitzationUrl($filesurls[$filesToUpload["tool_statistics"]]);

        //6.Notify approvers
        $notified = 0;
        foreach ($approversEmails as $value) {
            //notification
            $user = UsersDAO::selectUsers(array('Email' => $value))[0]['id'];
            if (!is_null($user)) {
                $n = new Notification($user, 'OEB data publication: New request 
                pending approval: </br><b>'.$req_id."</b>", 
                "oeb_publish/oeb/oeb_manageReq.php#".$req_id);
            }
            $n->saveNotification();

            //email
            $params = array();
            $params['approver'] = $value;
            $params['requester'] = $_SESSION['User']['Name'];
            $params['reqId'] = $req_id;
            $params['BE_name'] = getBenchmarkingEvents($benchmarkingEvent_id,"name");
            if (sendRequestToApprover($params)){
                $notified += 1;  
            } 
        }
        if ($notified == count($approversEmails)){
            array_push($log, "All approvers successfully notified.");
            $response_json->setCode(200);
            $response_json->setMessage($log);

        } else if ($notified == 0){
            array_push($log, "Error sending email to approvers.");
            $newRequest->setCurrentStatus("error");
    
            // return error msg via BlockResponse
            $response_json->setCode(400);
            $response_json->setMessage($log);

        }else {
            array_push($log, "Approvers successfully notified.");
            $response_json->setCode(200);
            $response_json->setMessage($log);
        }
        $user = UsersDAO::selectUsers(array('Email' => $approversEmails[0]))[0]['id'];
        if (!is_null($user)) {
            $n = new Notification($user, 'OEB data publication: New request 
            pending approval: </br><b>'.$req_id."</b>", 
            "oeb_publish/oeb/oeb_manageReq.php#".$req_id);
        }
    }
    //7.return JSON RESULT and register in mongo
    $hist_act = $newRequest->getHistoryActions();
    $hist_act[0]->setLog($log);
    $newRequest->saveOEBPetition();

	return $response_json->getResponse();
    
}

/**
 * Executes an external script
 * @param $cmd - entire comand to execute
 * @param $input
 * @return array with standard error, standard output, and return number execution
 */
function my_exec($cmd, $input=''){
    $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 
                                        2=>array('pipe', 'w')), $pipes);
    fwrite($pipes[0], $input);
    fclose($pipes[0]);

    $stdout=stream_get_contents($pipes[1]);
    fclose($pipes[1]);
        
    $stderr=stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $rtn=proc_close($proc);
    return array('stdout'=>$stdout,
            'stderr'=>$stderr,
            'return'=>$rtn);
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
/**
 * Serach for tools in VRE collection which doesnt have automatic publication
 * @return array - benchmarking event id's which are not publish automatically
 */
function getBenchEventidsNotAutomatic () {
    $toolsNoAuto = [];
    foreach ($GLOBALS['toolsCol']->find(array("community-specific_metadata.publication_scope" => "manager")) as $value) {
        array_push($toolsNoAuto,array("id" => $value['community-specific_metadata']['benchmarking_event_id'], 
        "max_req" => $value['community-specific_metadata']['max_requests']) );
    }
    return $toolsNoAuto ;
}

/**
 * Gets the num of petitions with status approved or pending approval
 * @param user id
 * @param benchamarking id 
 * @return associative array, which num approve petitions, num of pending petitons to be approve
 */
function getNumOfPetitionBench ($user, $benchmarkingEvent_id) {
    $petitions = ['Approved' => 0, 'Pending' => 0] ;

    //serach petitions which that user and that BE
    $filters = array("requester" => $user, "oeb_metadata.benchmarking_event_id" => $benchmarkingEvent_id);
    $found = OEBDataPetition::selectAllOEBPetitions($filters);
    
    //check status
    foreach ($found as $value) {
        if($value['current_status'] == 'approved') {
            $petitions['Approved'] += 1;
        }elseif ($value['current_status'] == 'pending approval') {
            $petitions['Pending'] += 1;
        }
    }
    
    return $petitions;
}


function userWithMaxRequests ($user, $benchmarkingEvent_id) {
    $hasMaxReq = false; 

    $petitions = getNumOfPetitionBench ($user, $benchmarkingEvent_id);
    $totalPetitions = $petitions['Approved'] + $petitions['Pending'];
    $max = $GLOBALS['toolsCol']->find(array('community-specific_metadata.benchmarking_event_id' => $benchmarkingEvent_id));
    if ($totalPetitions > $max->toArray()[0]['community-specific_metadata']['max_requests']){
        $hasMaxReq = true;
    }

    return $hasMaxReq;
}

/**
 * Check if a tool is already submitted (pending or approved) for a benchmarking
 * @param tool_id the tool to check
 * @param benchmarkingEvent_id to check 
 * @return false if it is not submitted (<max_req) or true it is already submitted == max_req
 */
function checkToolAlreadySubmitted ($tool_id, $benchmarkingEvent_id) {
    $submitted = false;

    //check max_req for that BE
    $max = $GLOBALS['toolsCol']->find(array(
        'community-specific_metadata.benchmarking_event_id' => $benchmarkingEvent_id))->toArray()[0]
        ['community-specific_metadata']['max_requests'];

    //search for publication requests with BE and this tool, count how many
    $filters = array( 'oeb_metadata.benchmarking_event_id' => $benchmarkingEvent_id, 
    'oeb_metadata.tool_id' => $tool_id, '$or' => [['current_status' => 'approved'],['current_status' => 'pending approval']]);
    $regData = OEBDataPetition::selectAllOEBPetitions($filters);
    $countToolReq = count($regData);

    //if count < max_req --> false
    if ($max <= $countToolReq) {
        $submitted = true;
    }

    return $submitted;
}