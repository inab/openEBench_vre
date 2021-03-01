<?php

///////////////////////////////////////////////////////////////////
// OEB METADATA MANAGEMENT
///////////////////////////////////////////////////////////////////

/**
 * Get list of benchmarking challenges associated to a given results file (i.e. OEB-metrics) or run folder
 * @param file_id  Identifier of file or folder in VRE
 * @return array of OEB benchmarking challenge identifiers
 */
function getChallengeIDs_fromRun($file_id){

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
	 
	return $challenges_ids;
}

/**
 * Get list of benchmarking challenges associated to an uploaded  file (i.e. OEB-metrics) or run folder
 * @param file_id  Identifier of file or folder in VRE
 * @return array of OEB benchmarking challenge identifiers
 */
function getChallengeIDs_fromInputId($file_id){

	$challenges_ids=array();

	// fetch all executions that used as input the given file
	#TODO
	# $exections = getExecutionList_fromInputId($file_id);
}


///////////////////////////////////////////////////////////////////
// REGISTRY OF OEB DATASET PUBLICATIONS
///////////////////////////////////////////////////////////////////


#TODO : Migrate functions from applib/oeb_publishAPI.php

