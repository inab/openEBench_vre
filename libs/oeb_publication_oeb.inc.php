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

	// convert from challenge-name to challenge_id
	// TODO
	$challenges_ids = $args_challenges;	
	 
	return $challenges_ids;
}

/**
 * Get list of benchmarking challenges associated to an uploaded  file (i.e. OEB-metrics) or run folder
 * @param file_id  Identifier of file or folder in VRE
 * @return array of OEB benchmarking challenge identifiers
 */
function getChallenges_associated_to_infile($file_id){

	$challenges_ids=array();

	// fetch all results generated from the given infile
	$r = $GLOBALS['filesMetaCol']->find(array( "input_files" => array( '$eq' => $file_id)), array("_id"=>1) );
	$outfile_ids=array_keys(iterator_to_array($r));
	//var_dump($outfile_ids);
	// fetch all executions that used as input the given file


	#TODO
	# $exections = getExecutionList_fromInputId($file_id);
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

	//
	// Find user files with the given allowed datatype/s

	//get path of the user workspace
	$proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));

	// TODO 
	// validate given parameter is a valid datatype 
	//  -- Accepted datatpes: getDataTypesList()
	//  -- Exemples of JSON HTTP standard responses: libs/oeb_management.inc.php

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
                $found = $GLOBALS['pubRegistersCol']->findOne(array("fileId" => $value['_id']));
                if(count($found) !== 0 || !is_null($found)) {
                        $value['current_status'] = $found['current_status'];
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
		}else{
			// if "participant", fetch all associated runs¿?
			$value['oeb_challenges_AA'] = getChallenges_associated_to_infile($value['_id']);
		}
		// TODO

		//get benchmarking event id from tool
		if (isset($value['tool'])){
			$tool = getTool_fromId($value['tool'],1);
			if (isset($tool['community-specific_metadata']['benchmarking_event_id'])){
				$value['benchmarking_event']['be_id'] = $tool['community-specific_metadata']['benchmarking_event_id'];
				$value['benchmarking_event']['be_name'] = getBenchmarkingEvents($value['benchmarking_event']['be_id'],"name");
			}else{
				$value['benchmarking_event']=$value['tool'];
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
        if (!empty($challenges)) {
           //get benchmarking event id
           
        }
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
                                $file_path  = $GLOBALS['dataDir'].getAttr_fromGSFileId($value,'path');
                                $file_name  = basename($file_path);
                                $f = ["id" => $value, "name" => $file_name, "nc_url" => getAttr_fromGSFileId($value, "nc_url")];
                                array_push($r['files'], $f);
                               
                        }
                        $r['approvers'] = $v['approvers'];
                        $r['history_actions'] = $v['history_actions'];
                        $r['status'] = $v['current_status'];
                        //$r['exec_folder'] =
                        array_push($reg, $r) ;
                }

                $block_json = json_encode($reg);

        } catch (MongoCursorException $e) {
                $_SESSION['errorMongo'] = "Error message: ".$e->getMessage()."Error code: ".$e->getCode();
        }
        
        return $block_json;

}

/**
 * Manages user action of a submited request
 * @param id the req id 
 * @param action the action to make
 * @return 1 if correctly updated in mongo, 0 otherwise. (updateReqRegister)
 */
function actionRequest($id, $action, $msg){
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
                $_SESSION['errorData']['Error'][]="Cannot write data form.";
                //redirect($GLOBALS['BASEURL'].'workspace/'); 
           }
           var_dump( $wd);
           //get token 
           $tk = $_SESSION['User']['Token']['access_token'];
           var_dump($tk);
           //1. Execute script to buffer
           //system("python ../../scripts/populate/publish.py)
           //2. Execute migration to oeb database
           //3. Get file from migration
           //4. update VRE mongo: oeb-publication-registers and metadata files


/*

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
                */

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

