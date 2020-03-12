<?php

function getProcesses() {
	//initiallize variables
	$process_json="{}";
	$processes = array();

	//user logged
	$userId = $_SESSION["User"]["id"];

	//MongoDB query
	$allProcesses = $GLOBALS['processCol']->find(array('$or' => array(array("status"=>1), array("owner"=>$userId))));

	//add query to an array
	foreach($allProcesses as $process) {
		array_push($processes, $process);
	}

	//convert array into json 
	$process_json = json_encode($processes, JSON_PRETTY_PRINT);

	return $process_json;
}

//status = 0; private
//status = 1; public
//status = 2; coming soon
function updateStatusProcess($processId, $statusId) {
	//jsonResponse class (errors or successfully)
	$response_json= new JsonResponse();
	
	//variables
	$userId = $_SESSION["User"]["id"];
	$userType = $_SESSION["User"]["Type"];

	//collection processes
	$processCol = $GLOBALS['processCol'];

	// check if user is authorized to update object
	$authorized = false;

	//check what type of user it is
	if ($userType == 0) {
		$authorized = true;
	} else if ($userType == 1) {
		$processesToolDev = $processCol->find(array("owner"=>$userId, "_id"=>$processId));
		if($processesToolDev) {
			$authorized = true;
		} else {
			$authorized = false;
		}
	} else {
		$authorized = false;	
	}

	// return error if unauthorized action
    if (!$authorized){
		// return error msg via ProcessResponse
		$response_json->setCode(401);
		$response_json->setMessage("Not authorized to update the status of the OEB-Process with Identifier='$processId'. Double check its ownership.");
		
		return $response_json->getResponse();
	}
	// update process status in Mongo
	try  {
		$processCol->update(['_id' => $processId], [ '$set' => [ 'status' => 'NumberLong('+$statusId+')']]);
	
	} catch (MongoCursorException $e) {

		$response_json->setCode(199);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}
	
	//redirect('https://dev-openebench.bsc.es/vre/oeb_management/oeb_process/oeb_processes.php');
	
	return $response_json->getResponse();

	//$processesUser_json = json_encode($processesUserLogin, JSON_PRETTY_PRINT);
	
	//return $processesUser_json;
}
