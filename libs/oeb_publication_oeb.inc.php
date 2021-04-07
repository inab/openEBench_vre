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

        //get data from DB
	$proj_name_active   = basename(getAttr_fromGSFileId($_SESSION['User']['dataDir'], "path"));

	// validate given datatype. 
	//  -- Accepted datatpes: getDataTypesList()
	//  -- Exemples of JSON HTTP standard responses: libs/oeb_management.inc.php
	// TODO 

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

        foreach ($filteredFiles as $key => $value) {
                //get data type name for each file
                $value['datatype_name'] = getDataTypeName($value['data_type']);

                //get if file is already request to publish
                $found = $GLOBALS['pubRegistersCol']->findOne(array("fileId" => $value['_id']));
                if(count($found) !== 0 || !is_null($found)) {
                        $value['current_status'] = $found['current_status'];
                }

                //get challenge status
		if ($value['data_type'] != "participant"){
			$value['oeb_challenges'] = getChallenges_associated_to_outfile($value['_id']);
		}else{
			$value['oeb_challenges_AA'] = getChallenges_associated_to_infile($value['_id']);
		}
		// TODO

                //get benchmarking event name from tool 
                $value['oeb_event'] = $value['tool'];
		// TODO

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

