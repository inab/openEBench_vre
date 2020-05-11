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

	$_id = createLabel($GLOBALS['AppPrefix']."_process",'processCol');
	$_schema = "https://openebench.bsc.es/vre/process-schema";
	
	$processWithVars = array("owner" => $userId, "_id" =>$_id, "_schema" => $_schema);

	$process_json = json_encode($processWithVars, JSON_PRETTY_PRINT);

	return $process_json;
}

function setProcess($processStringForm) {
	$response_json= new JsonResponse();

	$processJSONForm = json_decode($processStringForm);

	//user logged
	$userId = $_SESSION["User"]["id"];

	//MongoDB query
	try {
		$GLOBALS['processCol']->insert($processJSONForm);
	} catch (Exception $e) {
		$response_json->setCode(501);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}

	return $response_json->getResponse();
}