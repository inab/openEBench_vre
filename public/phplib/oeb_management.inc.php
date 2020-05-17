<?php

function getProcesses() {
	//initiallize variables
	$process_json="{}";
	$processes = array();
	$users = array();

	//user logged
	$userId = $_SESSION["User"]["id"];

	//type of user
	$typeUser = $GLOBALS['usersCol']->find(array("id"=>$userId), array("Type"=>1));

	foreach($typeUser as $user) {
		array_push($users, $user);
	}

	foreach($users as $user) {
		if($user["Type"] == 0) {
			$allProcesses = $GLOBALS['processCol']->find();
		} else {
			$allProcesses = $GLOBALS['processCol']->find(array('$or' => array(array("data.owner" => $userId), array("request_status" => 1))));
		}
	}

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
	$response_json = new JsonResponse();
	$users = array();
	$processes = array();

	//variables
	$userId = $_SESSION["User"]["id"];
	$typeUser = $GLOBALS['usersCol']->find(array("id"=>$userId), array("Type"=>1));

	foreach($typeUser as $user) {
		array_push($users, $user);
	}

	//collection processes
	$processCol = $GLOBALS['processCol'];

	// check if user is authorized to update object
	$authorized = false;

	//check what type of user it is
	foreach($users as $user) {
		//if admin
		if($user["Type"] == 0) {
			$authorized = true;
		} else if ($user["Type"] == 1) {
			$processesToolDev = $processCol->find(array("data.owner" => $userId, "_id" => $processId));

			foreach($processesToolDev as $process) {
				array_push($processes, $process);
			}
		
			if(empty($processes)) {
				$authorized = false;
			} else {
				$authorized = true;
			}
		} else {
			$authorized = false;
		}
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
		$processCol->update(['_id' => $processId], [ '$set' => [ 'request_status' => 'NumberLong('+$statusId+')']]);
		$processFound = $processCol->find(array("request_status"=>'NumberLong('+$statusId+')', "_id"=>$processId));

		if($processFound != "") {
			$response_json->setCode(200);
			$response_json->setMessage("OK");
		} else {
			$response_json->setCode(500);
			$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		}
		return $response_json->getResponse();
	} catch (MongoCursorException $e) {

		$response_json->setCode(500);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}

	return $response_json->getResponse();
}

function getListOntologyForForm($formOntology, $ancestors) {
	//variables
	$resource = "";
	$graph = "";
	$classArray = array();
	$subClassArray = array();
	$array_gen;
	$process_json="{}";
	$label;

	if (isset($GLOBALS['oeb_dataModels'][$formOntology])) {
		//is only to validate that the ontology exists in DB. 
		$nameUrlOntology = $GLOBALS['oeb_dataModels'][$formOntology];
		//ontology-general
		//we use the link of .owl and not the pURLs because this function does not accepted it
		$graph = EasyRdf_Graph::newAndLoad("https://raw.githubusercontent.com/inab/OEB-ontologies/master/oebDatasets-complete.owl","rdfxml");
		
		$resource = $graph->resource($ancestors);
		
 		//get all the classes that are subclass of the uri 'https://w3id.org/oebDataFormats/FormatDatasets'
		$classes = $graph->resourcesMatching("rdfs:subClassOf",$resource);

		//get the first classes (without showing the childrens)
		foreach ($classes as $class) {
			//get the label of first classes (without showing the childrens)
            $label = $class->getLiteral('rdfs:label');
            $label = (string)$label; 

			//get the uri of classes that extends from the previous class find
			$resourceClassesInherited = $graph->resource($class);

			//get all the classes that are subclass of the uri found in the previous step (all the uris of classes that extends from the first classes found)
			$classesInherited = $graph->resourcesMatching("rdfs:subClassOf",$resourceClassesInherited);

			$URILabel = (string)$resourceClassesInherited->getUri();
			$ClassPair = array("label" => $label, "URI" => $URILabel);
			
			//if there are not any format inherited in the first classes do not do it
			$subClassArray = array();
			if ($classesInherited != null) {
				array_push($classArray, $ClassPair);
				//get the classes inherited (the childrens)
				foreach($classesInherited as $classInherited) {
					//get the label of the classes inherited (the childrens)
					$labelClassInherited = (string)$classInherited->getLiteral('rdfs:label');
					$URIClassInherited = (string)$classInherited->getUri();
					$subClassPair = array("label" => $labelClassInherited, "URI" => $URIClassInherited);
					array_push($classArray, $subClassPair);
				}
			} else {
				array_push($classArray, $ClassPair);
			}
		}
		//$array_gen = array("labels" => $classArray);
		$process_json = json_encode($classArray, JSON_PRETTY_PRINT);
		return $process_json;
	} else {
		return $process_json;
	}
}

function getDefaultValues() {
	$process_json = "{}";

	//user logged
	$userId = $_SESSION["User"]["id"];

	$_schema = "https://openebench.bsc.es/vre/process-schema";
	
	$processWithVars = array("owner" => $userId, "_schema" => $_schema);

	$process_json = json_encode($processWithVars, JSON_PRETTY_PRINT);

	return $process_json;
}

function getUser() {
	//initiallize variables
	$process_json="{}";
	$users = array();

	//user logged
	$userId = $_SESSION["User"]["id"];

	//type of user
	$typeUser = $GLOBALS['usersCol']->find(array("id"=>$userId), array("Type"=>1));

	foreach($typeUser as $user) {
		array_push($users, $user);
	}

	$process_json = json_encode($users, JSON_PRETTY_PRINT);

	return $process_json;
}

function setProcess($processStringForm) {
	$response_json= new JsonResponse();

	$processForm = json_decode($processStringForm, true);

	// store public dataset
	$file = $processForm["inputs_meta"]["public_ref_dir"]["value"];

	//check git repositories and parse the workflow file
	$gitURL_workflow = $processForm["nextflow_files"]["workflow_file"]["workflow_gitURL"];
	$gitTag_workflow = $processForm["nextflow_files"]["workflow_file"]["workflow_gitTag"];

	$gitURL_config = $processForm["nextflow_files"]["config_file"]["config_gitURL"];
	$gitTag_config = $processForm["nextflow_files"]["config_file"]["config_gitTag"];

	$tmp_workflow = checkGit($gitURL_workflow, $gitTag_workflow);
	print_r($tmp_workflow);
	//$tmp_config = checkGit($gitURL_config, $gitTag_config);
	
	//$resultWorkflow = json_decode($tmp_workflow, true);
	//$resultConfig = json_decode($tmp_config, true);

	//CHECK NEXTFLOW FILES
	//errors workflow
	/* if ($resultWorkflow["code"] != 200){
		$response_json->setCode($resultWorkflow["code"]);
		$response_json->setMessage($resultWorkflow["message"]);

		return $response_json->getResponse();
	} 

	//errors config
	if ($resultConfig["code"] != 200){
		$response_json->setCode($resultConfig["code"]);
		$response_json->setMessage($resultConfig["message"]);

		return $response_json->getResponse();
	}  */

	exit;
	$tmpR = getPublicData_fromBase64($file);	
	$resultFile = json_decode($tmpR, true);

	//CHECK PUBLIC DATA
	if ($resultFile["code"] != 200) {
		$response_json->setCode($resultFile["code"]);
		$response_json->setMessage($resultFile["message"]);

		return $response_json->getResponse();
	} 

	//user logged
	$userId = $_SESSION["User"]["id"];

	$processForm["inputs_meta"]["public_ref_dir"]["value"] = $resultFile["message"];

	//MongoDB query
	$data = array();
	$data['_id'] = createLabel($GLOBALS['AppPrefix']."_process",'processCol');
	$data['data'] = $processForm;
	$data['request_status'] = "submitted";
	$data['request_date'] = date('l jS \of F Y h:i:s A');
	$data['publication_status'] = 3;
	
	try {
		//$GLOBALS['processCol']->insert($processJSONForm);
		$GLOBALS['processCol']->insert($data);

	} catch (Exception $e) {
		$response_json->setCode(501);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
	
		return $response_json->getResponse();
	}

	$response_json->setCode(200);
	$response_json->setMessage("OK");

	return $response_json->getResponse();
}

function getPublicData_fromBase64($file_base64) {

	// check file mime
	$response_json= new JsonResponse();

 	$tempFile = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."public_dataset";
	
	$r = file_put_contents( $tempFile ,file_get_contents($file_base64));

	if (!$r){
		$response_json->setCode(4);
		$response_json->setMessage("The file cannot be uploaded.");
		
		unlink($tempFile);
		return $response_json->getResponse();
	}
	if (! is_file($tempFile)){
		$response_json->setCode(4);
		$response_json->setMessage("The file cannot be uploaded.");
		
		unlink($tempFile);
		return $response_json->getResponse();
	}
	
	// guess compression
	
	$file_info  = shell_exec("file $tempFile | cut -d: -f2");

	// create target dir

	$target_folder = hash_file('sha256',$tempFile);

	$targetDir = $GLOBALS['pubDir'].$target_folder;

	mkdir($targetDir);
		
	// uncompress data
	
	$uncompress_cmd="";
	if (preg_match('/gzip/i',$file_info) == 1 || preg_match('/x-tar/i',$file_info) == 1){
		$uncompress_cmd = "tar xzvf $tempFile -C $targetDir";
	} else {
		$response_json->setCode(412);
		$response_json->setMessage("The file is not a TAR or a TAR.GZ.");

		unlink($tempFile);
		return $response_json->getResponse();
	}
	
	$r = shell_exec($uncompress_cmd); 

	// Check file size


	$response_json->setCode(200);
	$response_json->setMessage($targetDir);
	
	// clean temporary data
	unlink($tempFile);
	return $response_json->getResponse();
}

function checkGit($gitURL, $gitTag) {
	// check file mime
	$response_json= new JsonResponse();
	
	$gitDir = $GLOBALS['pubDir']."gitURL";
	mkdir($gitDir);
	$git_cmd = "git clone -b $gitTag '$gitURL' $gitDir";
	print_r($git_cmd);
	exit;
	$r = shell_exec("git clone -b $gitTag $gitURL");
	print_r($r);
	exit;
	//$git_cmd = "git clone -b $gitTag $gitURL /gpfs/VRE-dev/userdata/OpEBUSER5e301c714e657/__PROJ5e301c714e6685.80976818/.tmp/";

	//$r = shell_exec($git_cmd); 
	//print_r($r);

	return "hola";
}
