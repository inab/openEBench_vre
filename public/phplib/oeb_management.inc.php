<?php
function getUserProcesses(){
	$userId = $_SESSION["User"]["id"];
	$processesUser = array();

	$allProcesses = $GLOBALS['processCol']->find(array("owner"=>$userId));
	foreach ($allProcesses as $processUser) {
		array_push($processesUser, $processUser);
	}
	
	$processesUser_json = json_encode($processesUser, JSON_PRETTY_PRINT);

	return $processesUser_json;
}