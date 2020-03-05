<?php
/* function getUserProcesses(){
	$userId = $_SESSION["User"]["id"];
	$processesUser = array();

	$allProcesses = $GLOBALS['processCol']->find(array("owner"=>$userId));
	foreach ($allProcesses as $processUser) {
		array_push($processesUser, $processUser);
	}
	
	$processesUser_json = json_encode($processesUser, JSON_PRETTY_PRINT);

	return $processesUser_json;
} */

function getUserProcesses() {
	$userId = $_SESSION["User"]["id"];
	
	$processesUser = array();
	$publicProcesses = array();

	$allProcessesUser = $GLOBALS['processCol']->find(array("owner"=>$userId));
	$allProcessesStatus = $GLOBALS['processCol']->find(array("status"=>1));

	foreach($allProcessesStatus as $processStatus) {
		array_push($publicProcesses, $processStatus);
	}
	
	foreach ($allProcessesUser as $processUser) {
		array_push($processesUser, $processUser);
	}

	if ($processesPublic && $processesUser) {
		foreach($publicProcesses as $publicProcess) {
			if (!in_array($processesUser, $publicProcess)) {
				array_push($processesUser, $publicProcess);
			}
		}
	} else if ($publicProcesses && !$processesUser) {
		foreach($publicProcesses as $publicProcess) {
			array_push($processesUser, $publicProcess);
		}
	}

	$processesUser_json = json_encode($processesUser, JSON_PRETTY_PRINT);

	return $processesUser_json;
}