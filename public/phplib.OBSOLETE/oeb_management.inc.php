<?php

//Get all the workflows to show into the datatable -> depending on the user show ones or others. (LIST PROCESSES)
function getBlocks($type) {
	//initiallize variables
	$block_json="{}";
	$blocks = array();

	//user logged
	$userId = $_SESSION["User"]["id"];
	//information about the user logged
	$user = getUser("current");
	//converted into JSON
	$userJSON = json_decode($user, true);

	//get the community of the user
	$community = $userJSON["oeb_community"];

	//if the user is the administrator show all the blocks
	if($userJSON["Type"] == 0) {
		$allBlocks = $GLOBALS['blocksCol']->find(array("data.type" => $type));

	//if the user is not the administrator (=community manager)
	} elseif($userJSON["Type"] == 1) {
		//see the community. If user has community
		if ($community && $community != '') {
			$allBlocks = $GLOBALS['blocksCol']->find(array('$or' => array(array("data.owner.user" => $userId, "data.type" => $type), array("data.publication_status" => 1, "data.type" => $type), array("data.owner.oeb_community" => $community, "data.publication_status"=>4, "data.type" => $type))));
		//see the community. If user has not any community
		} else {
			$allBlocks = $GLOBALS['blocksCol']->find(array('$or' => array(array("data.owner.user" => $userId, "data.type" => $type), array("data.publication_status" => 1, "data.type" => $type))));
		}

	}

	//add query to an array
	foreach($allBlocks as $block) {
		array_push($blocks, $block);
	}

	//convert array into json 
	$block_json = json_encode($blocks, JSON_PRETTY_PRINT);

	return $block_json;
}

//Get all the block that have to be in the selector of NEW WORKFLOW (NEW WORKFLOW)
function getBlockSelect($type) {
	//initiallize variables
	$block_json="{}";
	$blocks = array();
	//user logged
	$userId = $_SESSION["User"]["id"];
	$user = getUser($userId);
	
	//type of user
	$userType = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("Type"=>1));
	$userType = $userType["Type"];

	$userJSON = json_decode($user, true);
	$community = $userJSON["oeb_community"];

	if ($userType == 0) {
		$allBlocks = $GLOBALS['blocksCol']->find(array('$or' => array(array("data.type" => $type, "data.publication_status" => 4, "validation_status"=>"registered"), array("data.type" => $type, "data.publication_status" => 1, "validation_status"=>"registered"),  array("data.type" => $type, "data.owner.user" => $userId, "validation_status"=>"registered"))));
	} else {
		if ($community && $community != '') {
			$allBlocks = $GLOBALS['blocksCol']->find(array('$or' => array(array("data.owner.user" => $userId, "data.type" => $type, "validation_status"=>"registered"), array("data.publication_status" => 1, "data.type" => $type, "validation_status"=>"registered"), array("data.type" => $type, "data.owner.user" => $userId, "validation_status"=>"registered"), array("data.owner.oeb_community" => $community, "data.publication_status"=>4, "data.type" => $type, "validation_status"=>"registered"))));
		} else {
			$allBlocks = $GLOBALS['blocksCol']->find(array('$or' => array(array("data.type" => $type, "data.owner.user" => $userId, "validation_status"=>"registered"), array("data.type" => $type, "data.publication_status" => 1, "validation_status"=>"registered"))));
		}
	}

	//add query to an array
	foreach($allBlocks as $block) {
		array_push($blocks, $block);
	}

	//convert array into json 
	$block_json = json_encode($blocks, JSON_PRETTY_PRINT);
	return $block_json;
}

//get all the workflows that the user has to see (LIST WORKFLOWS)
function getWorkflows() {
	//initiallize variables
	$workflow_json="{}";
	$workflows = array();

	//user logged
	$userId = $_SESSION["User"]["id"];

	//type of user
	$user = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("Type"=>1));

	//if the user is the administrator
	if($user["Type"] == 0) {
		$allWorkflows = $GLOBALS['workflowsCol']->find();
		//if the user is not the administrator (=community manager)
	} elseif($user["Type"] == 1) {
		//the workflows has to be registered to see 
		$allWorkflows = $GLOBALS['workflowsCol']->find(array("data.owner.user" => $userId));
	}

	//add query to an array
	foreach($allWorkflows as $workflow) {
		array_push($workflows, $workflow);
	}


	//convert array into json 
	$workflow_json = json_encode($workflows, JSON_PRETTY_PRINT);

	return $workflow_json;
}

//status = 0; coming soon
//status = 1; public
//status = 2; private
//for update the publication_status of workflows (LIST PROCESSES)
function updateStatusBlock($blockId, $statusId) {
	//jsonResponse class (errors or successfully)
	$response_json = new JsonResponse();

	$blocks = array();

	//variables
	$userId = $_SESSION["User"]["id"];
	$typeUser = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("Type"=>1));

	//collection blocks
	$blocksCol = $GLOBALS['blocksCol'];

	// check if user is authorized to update object
	$authorized = false;

	//check what type of user it is
	//if admin
	if($typeUser["Type"] == 0) {
		$authorized = true;
	//if community manager
	} else if ($typeUser["Type"] == 1) {
		$blockToolDev = $blocksCol->findOne(array("data.owner.user" => $userId, "_id" => $blockId));
	
		if(!$blockToolDev) {
			$authorized = false;
		} else {
			$authorized = true;
		}
	} else {
		$authorized = false;
	}

	// return error if unauthorized action
    if (!$authorized){
		// return error msg via BlockResponse
		$response_json->setCode(401);
		$response_json->setMessage("Not authorized to update the status of the OEB-Block with Identifier='$blockId'. Double check its ownership.");
		
		return $response_json->getResponse();
	}
	// update block status in Mongo
	try  {
		$blocksCol->update(['_id' => $blockId], [ '$set' => [ 'data.publication_status' => 'NumberLong('+$statusId+')']]);
		$blockFound = $blocksCol->find(array("data.publication_status"=>'NumberLong('+$statusId+')', "_id"=>$blockId));

		if($blockFound != "") {
			$type = $blocksCol->findOne(array("_id"=>$blockId));
			$response_json->setCode(200);
			$response_json->setMessage($type["data"]["type"]);
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

//function to obtain the plain list of the ontologies. An enum for the JSON Schema (NEW PROCESS)
function loadOntologyToPlainList($ontologyOwl, $ancestors) {
	//variables
	$resource = "";
	$graph = "";
	$classArray = array();
	$subClassArray = array();
	$array_gen;
	$block_json="{}";
	$label;

	if (in_array($ontologyOwl ,array_values($GLOBALS['oeb_dataModels']))) {

		//get the effective url of the ontology => the last url used
		$ch = curl_init($ontologyOwl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		$curl_data = curl_exec($ch);
		$url_effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		//create a hash 
		$hash_ontologyDir = md5($url_effective . '_' . $ancestors);

		//directory in which ontology lists are saved
		$ontologyDir = $GLOBALS['oeb_tmp'] . $hash_ontologyDir;
		$ontologyFile = $ontologyDir . "/plain_list.txt";
		
		if (!is_dir($ontologyDir) || filesize($ontologyFile) == 0) {
			//ontology-general
			//we use the link of .owl and not the pURLs because this function does not accepted it

			$graph = EasyRdf_Graph::newAndLoad($GLOBALS['oeb_general_ontology_reasoner'],"rdfxml");
			
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
				array_push($classArray, $ClassPair);

				//if there are not any format inherited in the first classes do not do it
				$subClassArray = array();
				if ($classesInherited != null) {
					//get the classes inherited (the childrens)
					foreach($classesInherited as $classInherited) {
						//get the label of the classes inherited (the childrens)
						$labelClassInherited = (string)$classInherited->getLiteral('rdfs:label');
						$URIClassInherited = (string)$classInherited->getUri();
						
						$subClassPair = array("label" => $labelClassInherited, "URI" => $URIClassInherited);
						if (!in_array($subClassPair, $classArray)) {
							array_push($classArray, $subClassPair);
						}
					}
				}
			}
			$block_json = json_encode($classArray, JSON_PRETTY_PRINT);
			mkdir($ontologyDir, 0777, true);
			file_put_contents($ontologyFile, $block_json);
		} else {
			$block_json = file_get_contents($ontologyFile, FILE_USE_INCLUDE_PATH);
		}
		return $block_json;
	} else {
		return $block_json;
	}
}

//put the default values into the JSON Schema to inserted it in MongoDB (NEW PROCESS)
function getDefaultValues() {
	$user_json = "{}";

	//user logged
	$userId = $_SESSION["User"]["id"];
	
	$userInfo = getUser($userId);
	//$user_json = json_encode($userInfo, JSON_PRETTY_PRINT);

	return $userInfo;
}

//Get the actual user (the user logged in) (LIST WORKFLOWS AND IN OEB_MANAGEMENT INTERNALLY)
function getUser($id) {

	if ($id == "current") {
		//initiallize variables
		$block_json="{}";

		//user logged
		$userId = $_SESSION["User"]["id"];

		//type of user
		$user = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("Type"=>1, "oeb_community"=>1, "id"=>1));

		$block_json = json_encode($user, JSON_PRETTY_PRINT);

		return $block_json;
	} else {
		//initiallize variables
		$block_json="{}";

		//type of user
		$user = $GLOBALS['usersCol']->findOne(array("id"=>$id), array("Inst"=>1, "Name"=>1, "Email"=>1, "id"=>1, "oeb_community"=>1));

		$block_json = json_encode($user, JSON_PRETTY_PRINT);

		return $block_json;
	}
}

//Get the workflow information from the form, validate all the data and inserted in MongoDB (NEW PROCESS)
function setBlock($blockStringForm, $typeBlock, $buttonAction) {

	$response_json= new JsonResponse();
	$response_json->setCode("405");
	$response_json->setMessage("ERROR");

	$blockForm = json_decode($blockStringForm, true);

	//GIT VALIDATION

	//get the url of git
	$gitURL = $blockForm["nextflow_files"]["files"]["gitURL"];
	$gitTag = $blockForm["nextflow_files"]["files"]["gitTag"];
	$privateToken = $blockForm["nextflow_files"]["files"]["privateToken"];

	//get errors (or not) workflow git - the four step
	$validationGit = _validationGit($gitURL, $gitTag, $privateToken);

	//errors nextflow files
	if ($validationGit != "OK" && $validationGit != "SUCCESS"){
		$response_json->setCode(422);
		$response_json->setMessage($validationGit);

		return $response_json->getResponse();
	} 

	//user logged
	$userId = $_SESSION["User"]["id"];

	//MongoDB query
	$data = array();

	//is a function that is not done by me that create a fake ID
	$data['_id'] = createLabel($GLOBALS['AppPrefix']."_block",'blocksCol');
	$data['_schema'] = "https://openebench.bsc.es/vre/block-schema";
	$data['data'] = $blockForm;
	$data['data']['type'] = $typeBlock;

	//gitlab or github without bsc
	if ($validationGit == "OK") {
		$data['validation_status'] = "under_validation";
		//gitlab bsc is automatically checked
	} elseif ($validationGit == "SUCCESS") {
		$data['validation_status'] = "registered";
	}

	$validator = _validateObject("block.json", $data, $GLOBALS['oeb_block_validator']);

	if($validator[0] != 200) {
		$response_json->setCode($validator[0]);
		$response_json->setMessage($validator[1]);

		return $response_json->getResponse();
	}

	try {
		if ($buttonAction == "submit") {
			//insert the data in mongo
			$GLOBALS['blocksCol']->insert($data);
		} elseif ($buttonAction == "edit") {
			//insert the data in mongo but updated
			$GLOBALS['blocksCol']->update(array("_id" => $blockForm["_id"]), $blockForm);
		}

	} catch (Exception $e) {
		$response_json->setCode(501);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());

		return $response_json->getResponse();
	}

	$response_json->setCode(200);
	$response_json->setMessage("OK");

	return $response_json->getResponse();
}

//validate the Git URL 
function _validationGit($gitURL, $gitTag, $privateToken) {
	$resultValidation = "";

	//clone the git 
	$tempDir = _cloneGit($gitURL, $gitTag);
	
	//validate the git url
	$gitValidation = _validateGitFiles($tempDir);

	switch($gitValidation) {
		case 0: 
			$resultValidation = "Nextflow files -> Git Files. The git URL cannot be cloned.";
			break;
		case 1:
			$resultValidation = "Nextflow files -> Git Files. The git repo is empty.";
			break;
		case 2:
			$filesValidation = _validateFileNames($tempDir, $gitURL);
			switch($filesValidation) {
				case 0: 
					$resultValidation = "Nextflow files -> main.nf. The 'workflow-block/main.nf' file is not found.";
					break;
				case 1: 
					$resultValidation = "Nextflow file -> nextflow.config. The 'workflow-block/nextflow.config' file is not found.";
					break;
				case 2: 
					$resultValidation = "Nextflow file -> test-data. The 'test-data/' repository is not found.";
					break;
				case 3: 
					$resultValidation = "Nextflow file -> gitlab-ci.yml. The '.gitlab-ci.yml' file is not found.";
					break;
				case 4:
					$resultValidation = "OK";
					if (strpos($gitURL, $GLOBALS['gitlab_server']) !== false) {
						$nextflowValidation = _validateNextflow($gitURL, $privateToken);
						if($nextflowValidation == 1) {
							$resultValidation = "SUCCESS";
						} else {
							$resultValidation = $nextflowValidation;
						}
					}
					break;
				default:
					$resultValidation = "Some error ocurred";
			}
			break;
		default: 
			$resultValidation = "Some error ocurred";
			break;
	}

	//remove temperal directory
	$r = shell_exec("rm -rf $tempDir");
	return $resultValidation;	
}

//only clone the git url given
function _cloneGit($gitURL, $gitTag) {
	$response_json= new JsonResponse();

	//create temporal file to check the git url
	$tempDir = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir']."gitDir/";

	$r = shell_exec("rm -r $tempDir");
	
	//clone the git if exist (taking into account the tag)
	$cmnd = "git clone -n $gitURL $tempDir; cd $tempDir; git checkout $gitTag;";

	//execute the command
	shell_exec($cmnd);

	return $tempDir;
}

function _validateNextflow($gitURL, $privateToken) {

	$gitlabPath = str_replace($GLOBALS['gitlab_server'], "", $gitURL);
	$gitlabPath = str_replace(".git", "", $gitlabPath);

	$cmd = 'curl --header "PRIVATE-TOKEN: ' . $privateToken . '" "'. $GLOBALS['gitlab_server'] .'api/v4/projects?search=' . $gitlabPath . '"';
	$output = shell_exec($cmd);

	$reposSelected = json_decode($output, true);

	$repoFound = false;

	foreach ($reposSelected as $repo) {
		if ($repo["path_with_namespace"] == $gitlabPath) {
			$repoFound = true;
			$repoId = $repo["id"];
			$cmdPipeline = 'curl --header "PRIVATE-TOKEN: ' . $privateToken . '" "'.$GLOBALS['gitlab_server'].'api/v4/projects/'. $repoId .'/pipelines"';
			$outputPipeline = shell_exec($cmdPipeline);
			$pipeline = json_decode($outputPipeline, true);
			if ($pipeline[0]["status"] == "success") {
				return 1;
			} else {
				return 0;
			}
		} 
		if ($reposSelected["message"]) {
			return "Gitlab Pipeline -> " . $reposSelected["message"];
		}
	}

	if (!$repoFound) {
		return "error";
	}
}

//validate the git url
function _validateGitFiles($tempDir) {
	//check if the git URL and Tag exist
	if (!is_dir($tempDir)) {
		return 0;
	}
	
	//check if the git link is empty
	$files = count(glob($tempDir . '*', GLOB_MARK));
	if ($files == 0) {
		return 1;
	}

	return 2;
}

//validate the nextflow files inside the git url
function _validateFileNames($tempDir, $gitURL) {

	//get the git directory file paths (clone it in the tempDir)
	$files = glob($tempDir . '{,.}*', GLOB_BRACE);

	$mainExist = 0;
	$nextflowExist = 0;
	$testdataExist = 0;
	$ymlExist = 0;

	foreach ($files as $file) {
		
		if (strtoupper($file) == strtoupper($tempDir. "workflow-block")) {
			$filesWorkflow_block = glob($tempDir . "workflow-block/" . '{,.}*', GLOB_BRACE);
			foreach ($filesWorkflow_block as $fileWorkflow) {
				//check if exist the main.nf file
				if (strtoupper($fileWorkflow) == strtoupper($tempDir. "workflow-block/main.nf")) {
					$mainExist++;
				}
				//check if exist the nextflow.config file
				if (strtoupper($fileWorkflow) == strtoupper($tempDir. "workflow-block/nextflow.config")) {
					$nextflowExist++;
				}
			}
		} 

		//check if exist the test-data repository
		if (strtoupper($file) == strtoupper($tempDir. "test-data")) {
			$filesContainer = glob($tempDir . "container/" . '{,.}*', GLOB_BRACE);
			//check if test data is empty
			if ($filesContainer) {
				$testdataExist++;
			}
		} 
		
		//check if exist the .gitlab-ci.yml file
		if(strtoupper($file) == strtoupper($tempDir . ".gitlab-ci.yml")) {
			$ymlExist++;
		}
	}
	
	//check only there are a main.nf
	if($mainExist != 1) {
		return 0;	
	} 

	//check only there are a nextflow.config
	if($nextflowExist != 1) {
		return 1;	
	}
	
	//check only there are a main.nf
	if($testdataExist != 1) {
		return 2;	
	} 

	//check only there are a nextflow.config
	if($ymlExist != 1) {
		if (strpos($gitURL, $GLOBALS['gitlab_server']) == false) {
			return 4;
		} else {
			return 3;
		}	
	} 	
	return 4;
}

//return a workflow from the id
function _getWorkflow($id) {
	//initiallize variables
	$workflow_json="{}";

	$workflow = $GLOBALS['workflowsCol']->findOne(array('_id' => $id));

	//convert array into json 
	$workflow_json = json_encode($workflow, JSON_PRETTY_PRINT);

	return $workflow_json;
}

//return a block from the id
function _getBlock($id) {

	//initiallize variables
	$block_json="{}";
	$block = "";

	$block = $GLOBALS['blocksCol']->findOne(array('_id' => $id));

	//convert array into json 
	$block_json = json_encode($block, JSON_PRETTY_PRINT);

	return $block_json;
}

//general function of create the VRE tool (LIST WORKFLOWS - ADMIN ROLE)
function createTool_fromWFs($id) {

	$response_json = new JsonResponse();

	$errors = array();

	$workflow_json="{}";

	//get the information about the selected workflow
	$workflow_data = _getWorkflow($id);

	//create the tool with the workflow information
	$tool_data = _createToolSpecification_fromWF($workflow_data);
	
	//if are not any tool
	if(!$tool_data) {
		$response_json->setCode(422);
		$response_json->setMessage("NO_EXIST");
		return $response_json->getResponse();
	}

	$validator = _validateObject("tool.json", $tool_data, $GLOBALS['oeb_workflow_validator']);

	
	$response_json->setCode($validator[0]);
	$response_json->setMessage($validator[1]);
	//return $response_json->getResponse();

	//var_dump($response_json);
	exit;

	//THIS PART IS NOT MINE - SAVE THE TOOL AND RUN IT IN THE VM
	//	_createTool_fromToolSpecification($tool_data);
	//	_insertToolMongo($tool_data);

	//if all works good register the tool and change the status of workflow to registered
	//$registration = _register_workflow($id);

	//if the status do not change
	if (!$registration) {
		$response_json->setCode(500);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error");
		return $response_json->getResponse();
	}

	$block_json = json_encode($tool_data, JSON_PRETTY_PRINT);
	
	//ALL GOOD
	$response_json->setCode(200);
	$response_json->setMessage("OK");

	return $response_json->getResponse();
}

//createFile
function _validateObject($nameFile, $data, $schema_validator) {
	//create the temporal directory and file
	$tempDir = $GLOBALS['dataDir'].$_SESSION['User']['id']."/".$_SESSION['User']['activeProject']."/".$GLOBALS['tmpUser_dir'];
	$tempFile = $tempDir . $nameFile;

	//if not exist the folder tmp create it because file_put_contents only create the file if not exist
	if (!is_dir($tempDir)) {
		mkdir($tempDir);
	}

	//move the content to that file: public_dataset
	//return the size
	unlink($tempFile);
	$r = file_put_contents($tempFile, json_encode($data, JSON_PRETTY_PRINT));

	if (!$r) {
		return [500, "Validator -> The data cannot be upload"];
	}

	//calls to the script to run the validator
	$cmd = "bash ./oeb_validatorScript.sh $tempFile $schema_validator";

	//execute the comand script
	$output = shell_exec($cmd);
	$errors = array();

	//if the line start with path and has the word Message: push into the errors array
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $output) as $line){
		if (strpos($line, "Path") !== false) {
			$error = trim(explode( 'Message:', $line )[1]);
			array_push($errors, $error);
		}
	} 

	//if there are the word skipped in the validator it means that the schema do not works propertly
	$skipped = strpos($output, "skipped");

	//skipped error
	if ($skipped) {
		unlink($tempFile);
		return [422, "Sorry... There is an error with JSON Schema and the tool cannot be validated."];
	} 

	//errors of sintaxis
	if($errors) {
		unlink($tempFile);
		return [422, $errors];
	}

	return [200, $tempFile];
}

//if the administrator click the reject button
function reject_workflow($id) {
	
	$workflowCol = $GLOBALS['workflowsCol'];

	$response_json = new JsonResponse();

	try  {
		$workflowCol->update(['_id' => $id], [ '$set' => [ 'request_status' => 'rejected']]);
		$workflowFound = $workflowCol->findOne(array("request_status"=>'rejected', "_id"=>$id));

		if(!$workflowFound) {
			$response_json->setCode(500);
			$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
			return $response_json->getResponse();
		}
		
	} catch (MongoCursorException $e) {

		$response_json->setCode(500);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}
	
	$response_json->setCode(200);
	$response_json->setMessage("OK");

	return $response_json->getResponse();
}

//if the administrator click the create tool vre button
function _register_workflow($id) {
	
	$workflowCol = $GLOBALS['workflowsCol'];

	$response_json = new JsonResponse();

	try  {
		//change the request status to registered instead of submitted
		$workflowCol->update(['_id' => $id], [ '$set' => [ 'request_status' => 'registered']]);
		//check if the update has work propertly
		$workflowFound = $workflowCol->findOne(array("request_status"=>'registered', "_id"=>$id));

		if(!$workflowFound) {
			return false;
		} 
	} catch (MongoCursorException $e) {
		return false;
	}

	return true;
}

//when the administrator click the create tool vre button generates the json tool to insert it in MongoDB 
function _createToolSpecification_fromWF($workflow) {
	//what the function will return
	$stringTool = '{}';

	//the tool in format json
	$jsonTool = array();
	
	//descriptions of the challenges
	$descriptions = array();
	//names of the challenges
	$names = array();

	$workflow_json = json_decode($workflow, true);

	$validation_id = $workflow_json["validation_id"];


	$validationBlock = _getBlock($validation_id);

	if ($validationBlock == "null") {
		return false;
	}
	$block_json = json_decode($validationBlock, true);

	//the ontology of data type and file type - get only once because is slow the block of getting ontologies
	$fileOntology = loadOntologyToPlainList($GLOBALS['oeb_dataModels']["oeb_formats"], $GLOBALS['oeb_ancestorModels']["oeb_ancestor_formats"]);
	$dataOntology = loadOntologyToPlainList($GLOBALS['oeb_dataModels']["oeb_datasets"], $GLOBALS['oeb_ancestorModels']["oeb_ancestor_datasets"]);

	$ontology_file_type = json_decode($fileOntology, true);
	$ontology_data_type = json_decode($dataOntology, true);

	

	$jsonTool = array();

	//ALL THE TOOL
	$jsonTool["_id"] =  $workflow_json["_id"];
	$jsonTool["_schema"] =  $workflow_json["_schema"];
	$jsonTool["name"] =  $block_json["data"]["name"];
	$jsonTool["title"] =  $block_json["data"]["title"];
	$jsonTool["short_description"] = $block_json["data"]["description"];
	$jsonTool["long_description"] = $block_json["data"]["description_long"];
		$jsonTool["owner"]["institution"] = $block_json["data"]["owner"]["institution"];
		$jsonTool["owner"]["author"] =  $block_json["data"]["owner"]["author"];
		$jsonTool["owner"]["contact"] = $block_json["data"]["owner"]["contact"];
		$jsonTool["owner"]["user"] = $block_json["data"]["owner"]["user"];
	//external boolean
	if($block_json["data"]["external"] == 1) {
		$jsonTool["external"] = true;
	} elseif ($block_json["data"]["external"] == 0) {
		$jsonTool["external"] = false;
	};
	$jsonTool["keywords"] = $block_json["data"]["keywords"];
	$jsonTool["keywords_tool"] = $block_json["data"]["keywords_tool"];
	$jsonTool["status"] = $block_json["data"]["publication_status"];
	//infrastructure array
		$jsonTool["infrastructure"]["memory"] = $block_json["data"]["infrastructure"]["memory"];
		$jsonTool["infrastructure"]["cpus"] = $block_json["data"]["infrastructure"]["cpus"];
		$jsonTool["infrastructure"]["executable"] = $GLOBALS["oeb_tool_wrapper"];
		$jsonTool["infrastructure"]["wallTime"] = $block_json["data"]["infrastructure"]["wallTime"];
		for($i = 0; $i < sizeof($block_json["data"]["infrastructure"]["clouds"]); $i++) {
			if ($block_json["data"]["infrastructure"]["clouds"][$i] == "life-bsc") {
				$jsonTool["infrastructure"]["clouds"]["life-bsc"]["launcher"] = "SGE";
				$jsonTool["infrastructure"]["clouds"]["life-bsc"]["queue"] = "default.q";
			}
		}
	$jsonTool["has_custom_viewer"] = true;
		
	//Initialize variables
	$number_input_files = 0;
	$number_input_files_public_dir = 0;
	$number_arguments = 0;
	$nextflow_repo_uri = array();
	$nextflow_repo_tag = array();

	//get the number of all the keys
	$keys = array_keys($block_json["data"]["inputs_meta"]);
	$keysChallenge = array_keys($block_json["data"]["inputs_meta"]["challenges_ids"]["challenges"]);
	
	//define variables that contain the different arrays inside
	$jsonTool["input_files"] = [];
	$jsonTool["input_files_public_dir"] = [];
	$jsonTool["arguments"] = [];

	//input_files_combination
	$jsonTool["input_files_combinations"] = [array(
		"description" => "Run benchmarking workflow",
		"input_files" =>["input"]
	)];

	//input_files_combination_internal
	$jsonTool["input_files_combinations_internal"] = [[array(
		"participant" => 1
	)]];

	//arguments always in the same way
	//arguments - nextflow_repo_uri
	$nextflow_repo_uri = array(
		"name" => "nextflow_repo_uri",
		"description" => "Nextflow Repository URI",
		"help" => "Nextflow Repository (i.e https:\/\/github.com\/prj\/reponame)",
		"type" => "hidden",
		"value" => $block_json["data"]["nextflow_files"]["workflow_file"]["workflow_gitURL"],
		"required" => true
	);

	//arguments - nextflow_repo_tag
	$nextflow_repo_tag = array(
		"name" => "nextflow_repo_tag",
		"description" => "Nextflow Repository tag",
		"help" => "Nextflow Repository Tag version",
		"type" => "hidden",
		"value" => $block_json["data"]["nextflow_files"]["workflow_file"]["workflow_gitTag"],
		"required" => true
	);

	$jsonTool["arguments"] = [$nextflow_repo_tag, $nextflow_repo_uri];

	//do the functions knowing how many of each type there are
	for($i = 0; $i < sizeof($keys); $i++) {
		$type = $block_json["data"]["inputs_meta"][$keys[$i]]["type"];
		
		//structure of file user
		if ($type == "file_user") {

			//creating file_type array
			$file_types = array();
			for($x = 0; $x < sizeof($ontology_file_type); $x++) {
				for($j = 0; $j < sizeof($block_json["data"]["inputs_meta"][$keys[$i]]["file_type"]); $j++) {
					if ($block_json["data"]["inputs_meta"][$keys[$i]]["file_type"][$j] == $ontology_file_type[$x]["URI"]) {
						array_push($file_types, $ontology_file_type[$x]["label"]);
					}
				}
			}
			//creating data_type array
			$data_types = array();
			for($x = 0; $x < sizeof($ontology_data_type); $x++) {
				for($j = 0; $j < sizeof($block_json["data"]["inputs_meta"][$keys[$i]]["data_type"]); $j++) {
					if ($block_json["data"]["inputs_meta"][$keys[$i]]["data_type"][$j] == $ontology_data_type[$x]["URI"]) {
						array_push($data_types, $ontology_data_type[$x]["label"]);
					}
				}
			}

			//input_files
			array_push($jsonTool["input_files"], array(
				"name" => $block_json["data"]["inputs_meta"][$keys[$i]]["name"],
				"description" => $block_json["data"]["inputs_meta"][$keys[$i]]["label"],
				"help" => $block_json["data"]["inputs_meta"][$keys[$i]]["help"],
				"file_type" => $file_types,
				"data_type" => $data_types,
				"required" => true,
				"allow_multiple" => false
			));
		} 
		
		if ($type == "file_community" || $type == "dir_community") {

			//creating file_type array
			$file_types = array();
			for($x = 0; $x < sizeof($ontology_file_type); $x++) {
				for($j = 0; $j < sizeof($block_json["data"]["inputs_meta"][$keys[$i]]["file_type"]); $j++) {
					if ($block_json["data"]["inputs_meta"][$keys[$i]]["file_type"][$j] == $ontology_file_type[$x]["URI"]) {
						array_push($file_types, $ontology_file_type[$x]["label"]);
					}
				}
			}

			//creating data_type array
			$data_types = array();
			for($x = 0; $x < sizeof($ontology_data_type); $x++) {
				for($j = 0; $j < sizeof($block_json["data"]["inputs_meta"][$keys[$i]]["data_type"]); $j++) {
					if ($block_json["data"]["inputs_meta"][$keys[$i]]["data_type"][$j] == $ontology_data_type[$x]["URI"]) {
						array_push($data_types, $ontology_data_type[$x]["label"]);
					}
				}
			}

			//input_files_public_dir
			array_push($jsonTool["input_files_public_dir"], array(
				"name" => $block_json["data"]["inputs_meta"][$keys[$i]]["name"],
				"description" => $block_json["data"]["inputs_meta"][$keys[$i]]["label"],
				"help" => $block_json["data"]["inputs_meta"][$keys[$i]]["help"],
				"type" => "hidden",
				"value" => $block_json["data"]["inputs_meta"][$keys[$i]]["value"] . "/",
				"file_type" => $file_types,
				"data_type" => $data_types,
				"required" => true,
				"allow_multiple" => false
			));
		}

		//structure of the different arguments (string, integer, number, boolean, enum, enum_mult and hidden)
		if($type == "string" || $type == "integer" || $type == "number" || $type == "boolean" || $type == "enum" || $type == "enum_mult" || $type == "hidden") {
  			if ($block_json["data"]["inputs_meta"][$keys[$i]]["name"] == "challenges_ids") {
				for ($x = 0; $x < sizeof($keysChallenge); $x++) {
					array_push($descriptions, $block_json["data"]["inputs_meta"]["challenges_ids"]["challenges"][$keysChallenge[$x]]["description"]);
					array_push($names, $block_json["data"]["inputs_meta"]["challenges_ids"]["challenges"][$keysChallenge[$x]]["value"]);
				}
				array_push($jsonTool["arguments"], array(
					"name" => $block_json["data"]["inputs_meta"][$keys[$i]]["name"],
					"description" => $block_json["data"]["inputs_meta"][$keys[$i]]["label"],
					"help" => $block_json["data"]["inputs_meta"][$keys[$i]]["help"],
					"type" => $block_json["data"]["inputs_meta"][$keys[$i]]["type"],
					"default" => [],
					"required" => true,
					"enum_items" => array(
						"description" => $descriptions,
						"name" => $names
					)
				));
			} 
			if ($block_json["data"]["inputs_meta"][$keys[$i]]["name"] != "challenges_ids") {
				array_push($jsonTool["arguments"], array(
					"name" => $block_json["data"]["inputs_meta"][$keys[$i]]["name"],
					"description" => $block_json["data"]["inputs_meta"][$keys[$i]]["label"],
					"help" => $block_json["data"]["inputs_meta"][$keys[$i]]["help"],
					"type" => $block_json["data"]["inputs_meta"][$keys[$i]]["type"],
					"required" => true
				));
			} 
		}
	}

 	$keysOutput = array_keys($block_json["data"]["outputs_meta"]);
	
	$jsonTool["output_files"] = [];

	for($i = 0; $i < sizeof($keysOutput); $i++) {
		//creating file_type array
		$file_type = "";
		for($x = 0; $x < sizeof($ontology_file_type); $x++) {
			for($j = 0; $j < sizeof($block_json["data"]["outputs_meta"][$keysOutput[$i]]["file_type"]); $j++) {
				if ($block_json["data"]["outputs_meta"][$keysOutput[$i]]["file_type"][$j] == $ontology_file_type[$x]["URI"]) {
					$file_type = $ontology_file_type[$x]["label"];
				}
			}
		}

		//creating data_type array
		$data_type = "";
		for($x = 0; $x < sizeof($ontology_data_type); $x++) {
			for($j = 0; $j < sizeof($block_json["data"]["outputs_meta"][$keysOutput[$i]]["data_type"]); $j++) {
				if ($block_json["data"]["outputs_meta"][$keysOutput[$i]]["data_type"][$j] == $ontology_data_type[$x]["URI"]) {
					$data_type = $ontology_data_type[$x]["label"];
				}
			}
		}

		if ($block_json["data"]["outputs_meta"][$keysOutput[$i]]["name"] == "validated_participant" || $block_json["data"]["outputs_meta"][$keysOutput[$i]]["name"] == "assessment_results") {
			array_push($jsonTool["output_files"], array(
				"name" => $block_json["data"]["outputs_meta"][$keysOutput[$i]]["name"],
				"required" => true,
				"allow_multiple" => false,
				"file" => array(
					"file_type" => $file_type,
					"file_path" => "assessment_datasets.json",
					"data_type" => $data_type,
					"compressed" => null,
					"meta_data" => array(
						"description" => "Metrics derivated from the given input data",
						"tool" => $block_json["_id"],
						"visible" => true
					)
				)
			));
		}

		if ($block_json["data"]["outputs_meta"][$keysOutput[$i]]["name"] == "tar_nf_stats") {
			array_push($jsonTool["output_files"], array(
				"name" => $block_json["data"]["outputs_meta"][$keysOutput[$i]]["name"],
				"required" => true,
				"allow_multiple" => false,
				"file" => array(
					"file_type" => $file_type,
					"data_type" => $data_type,
					"compressed" => "gzip",
					"meta_data" => array(
						"description" => "Other execution associated data",
						"tool" => $block_json["_id"],
						"visible" => true
					)
				)
			));
		}
	}

	$stringTool = json_encode($jsonTool, JSON_PRETTY_PRINT);

	return $stringTool;
}

//Function to detele the block (LIST PROCESSES)
function deleteBlock($id) {
	
	$response_json = new JsonResponse();

	$userId = $_SESSION["User"]["id"];

	$workflows = array();
	$blocksCol = $GLOBALS['blocksCol'];

	//get the current user
	$currentUser = getUser("current");
	$typeUserLogged = json_decode($currentUser, true);

	//find the owner of the block
	$block = $blocksCol->findOne(array('_id' => $id));

	//if the current user is not the same that the user owner of the block AND if the user is not admin
	if ($userId != $block["data"]["owner"]["user"] && $typeUserLogged["Type"] != 0) {
		$response_json->setCode(422);
		$response_json->setMessage("You are not allowed to remove this block.");
		return $response_json->getResponse();
	}
	
	//Get if there are any workflow using this validation block
	$workflowsInUse = $GLOBALS['workflowsCol']->find(array('validation_id' => $id));
	
	//add query to an array
	foreach($workflowsInUse as $workflow) {
		array_push($workflows, $workflow);
	}

	//if are workflows using that block
	if (!empty($workflows)) {
		$response_json->setCode(422);
		$response_json->setMessage("The block is being used in a workflow.");
		return $response_json->getResponse();
	}

	//if all is correct remove the block
	try  {
		$blocksCol->remove(array('_id' => $id));

		$response_json->setCode(200);
		$response_json->setMessage("OK");

		return $response_json->getResponse();
	} catch (MongoCursorException $e) {
		$response_json->setCode(500);
		$response_json->setMessage("Cannot delete data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
		return $response_json->getResponse();
	}
}

//construct the workflow and inserted into MongoDB
function setWorkflow($json, $validation, $metrics, $consolidation) {
	
	$response_json= new JsonResponse();

	//user logged
	$userId = $_SESSION["User"]["id"];

	$tmpInfoUser = getUser($userId);
	$infoUser = json_decode($tmpInfoUser, true);

	$data_workflow = json_decode($json, true);

	//Validate form data

	// TODO: materialize reference datasets
	// take as reference old validation function : https://github.com/inab/openEBench_vre/blob/e5f85e45c65cb1fe103504e037ee1b9f71cabf73/public/phplib/oeb_management.inc.php#L406	

	// TODO: use JM validator to check form data
	// take as references setBlock()
	/*
	$validator = _validateObject("block.json", $data, $GLOBALS['oeb_block_validator']);
        if($validator[0] != 200) {
                $response_json->setCode($validator[0]);
                $response_json->setMessage($validator[1]);

                return $response_json->getResponse();
        } */ 


	//Compose WF data from 3 blocks + form data

	$data = array();
	//is a function that is not done by me that create a fake ID
	$data['_id'] = createLabel($GLOBALS['AppPrefix']."_workflow",'workflowsCol');
	//the schema has to be always the same because if not are this the validator do not works propertly
	$data['data'] = $data_workflow;
	$data['validation_id'] = $validation;
	$data['metrics_id'] = $metrics;
	$data['consolidation_id'] = $consolidation;
	//current date
	$data['date'] = date('l jS \of F Y h:i:s A');
	//by default the status is submitted
	$data['request_status'] = "submitted";
	
	//Mongo query insert into Workflows collection
	try {
		//insert the workflow
		$GLOBALS['workflowsCol']->insert($data);
	} catch(Exception $e) {
		$response_json->setCode(501);
		$response_json->setMessage("Cannot update data in Mongo. Mongo Error(".$e->getCode()."): ".$e->getMessage());
	
		return $response_json->getResponse();
	}

	//send the email and check it
/* 	if(_reportMailNewTool($data['_id'])) {
		$response_json->setCode(200);
		$response_json->setMessage("OK");
	
		return $response_json->getResponse();
	//if the email is not sended
	} else {
		$response_json->setCode(422);
		$response_json->setMessage("Error sending an email to the administrator");
	
		return $response_json->getResponse();
	} */	

	$response_json->setCode(200);
	$response_json->setMessage("OK");

	return $response_json->getResponse();
}

//send an email to the admins mail telling them that there are a new workflow submitted to converted into a tool
function _reportMailNewTool($idWF) {

 	$ticketnumber = 'VRE-'.rand(1000, 9999);
	$subject = 'New tool';
	
	$message = '
		Ticket ID: '.$ticketnumber.'<br>
		User name: '.$_SESSION["User"]["Name"].' '.$_SESSION["User"]["Surname"].'<br>
		User email: '.$_SESSION["User"]["Email"].'<br>
		Request type: '.$subject.'<br>
		Request subject: Creation of new tool <strong>'.$idWF.'</strong><br>
		Comments: '.$_REQUEST['comments'];
	
	$messageUser = '
		Copy of the message sent to our technical team:<br><br>
		Ticket ID: '.$ticketnumber.'<br>
		User name: '.$_SESSION["User"]["Name"].' '.$_SESSION["User"]["Surname"].'<br>
		User email: '.$_SESSION["User"]["Email"].'<br>
		Request type: '.$subject.'<br>
		Request subject: Creation of new tool <strong>'.$idWF.'</strong><br>
		Comments: '.$_REQUEST['comments'].'<br><br>
		VRE Technical Team';
	
	if(sendEmail($GLOBALS['ADMINMAIL'], "[".$ticketnumber."]: ".$subject, $message, $_SESSION["User"]["Email"])) {
		sendEmail($_SESSION["User"]["Email"], "[".$ticketnumber."]: ".$subject, $messageUser, $_SESSION["User"]["Email"]);
	
	} else {
		return false;
	} 

	return true;
}

//function to show the VIEW JSON of the workflow (LIST WORKFLOWS)
function showWorkflowJSON($idWorkflow) {

	$workflow  = $GLOBALS['workflowsCol']->findOne(array('_id' => $idWorkflow));

	if (empty($workflow)){
		echo "<p>The workflow '$idWorkflow' is not defined or is not registered in the database. Sorry, cannot show the details for the selected execution</p>";
		die(0);
	}

	$json = json_encode($workflow, JSON_PRETTY_PRINT);
	
	return "<h2>Workflow configuration file</h2><pre style='max-height: calc(100vh - 300px);white-space: pre-wrap;'>$json</pre>";
}
