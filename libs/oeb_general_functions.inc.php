<?php
////////////////////////////////////////////////////////////////////////////////
////////////////////////FUNCTIONS TO OPENEBENCH APIs////////////////////////////
////////////////////////////////////////////////////////////////////////////////


#------------------------- REST API FUNCTIONS ----------------------------------/

/**
 * Gets all communitites with their info or an specific community filtered or not
 * @param $community_id, the id of the community to find
 * @param $filter_field, the attribute of the given community
 * @return community/ies (json format). If an error ocurs it return false.
 */
function getCommunities($community_id = null, $filter_field = null ){

  if ($community_id == null) {
      $url = $GLOBALS['OEB_scirestapi']."/Community";
  } else {
    if ($filter_field == null) {
      $url = $GLOBALS['OEB_scirestapi']."/Community/".$community_id;
    } else {
      $url = $GLOBALS['OEB_scirestapi']."/Community/".$community_id."/".$filter_field;
    }
  }

  $headers= array('Accept: aplication/json');

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting datasets. Http code= ".$status;
    return false;
  } else {
    return json_decode($r[0], true);
  }
}


/**
 * Gets all datasets with their info or an specific dataset filtered or not
 * @param $dataset_id, the id of the dataset to find
 * @param $filter_field, the attribute of the given dataset
 * @return dataset/s (json format). If an error ocurs it return false.
 */
//var_dump(getDatasets("OEBD0010000003"));
function getDatasets($dataset_id = null, $filter_field = null ){
  if ($dataset_id == null) {
    $url = $GLOBALS['OEB_scirestapi']."/Dataset";

  } else {
    if ($filter_field == null) {
      $url = $GLOBALS['OEB_scirestapi']."/Dataset/".$dataset_id;
    } else {
      $url = $GLOBALS['OEB_scirestapi']."/Dataset/".$dataset_id."/".$filter_field;
    } 
  }

  $headers= array('Accept: aplication/json');

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting datasets. Http code= ".$status;
    return false;
  } else {
    return json_decode($r[0], true);
  }
}
/** 
 * Gets all challenges with their info or an specific challenge filtered or not
 * @param $challenge_id, the id of the challenge to find
 * @param $filter_field, the attribute of the given challenge
 * @return challenge/s (json format). If an error ocurs it return false.
 */
//var_dump(getChallenges("OEBX0010000001", "benchmarking_event_id"));
function getChallenges ($challenge_id = null, $filter_field = null ){
  if ($challenge_id == null) {
    $url = $GLOBALS['OEB_scirestapi']."/Challenge";

  } else {
    if ($filter_field == null) {
      $url = $GLOBALS['OEB_scirestapi']."/Challenge/".$challenge_id;
    } else {
      $url = $GLOBALS['OEB_scirestapi']."/Challenge/".$challenge_id."/".$filter_field;
    } 
  }
  $headers= array('Accept: aplication/json');

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting challenges. Http code= ".$status;
    return false;
  } else {
    return json_decode($r[0], true);
  }

}
/** 
 * Gets all benchmarking events with their info or an specific BE filtered or not
 * @param $benchmarkingEvent_id, the id of the benchmark to find
 * @param $filter_field, the attribute of the given benchmarking event
 * @return benchmark/s (json format). If an error ocurs it return false.
 */
//var_dump(getBenchmarkingEvents("OEBE0010000000", "community_id"));
function getBenchmarkingEvents ($benchmarkingEvent_id = null, $filter_field = null ){

  if ($benchmarkingEvent_id == null) {
    $url = $GLOBALS['OEB_scirestapi']."/BenchmarkingEvent";

  } else {
    if ($filter_field == null) {
      $url = $GLOBALS['OEB_scirestapi']."/BenchmarkingEvent/".$benchmarkingEvent_id;
    } else {
      $url = $GLOBALS['OEB_scirestapi']."/BenchmarkingEvent/".$benchmarkingEvent_id."/".$filter_field;
    } 
  }
  $headers= array('Accept: aplication/json');

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting benchmarkings events. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    return json_decode($r[0], true);
  }

}
/** 
 * Gets all tools with their info or an specific tool filtered or not
 * @param $tool_id, the id of the tool to find
 * @param $filter_field, the attribute of the given tool
 * @return tool/s (json format). If an error ocurs it return false.
 */
//var_dump(getToolss("OEBT001000000A", "name"));
function getToolss($tool_id = null, $filter_field = null ){

  if ($tool_id == null) {
    $url = $GLOBALS['OEB_scirestapi']."/Tool";

  } else {
    if ($filter_field == null) {
      $url = $GLOBALS['OEB_scirestapi']."/Tool/".$tool_id;
    } else {
      $url = $GLOBALS['OEB_scirestapi']."/Tool/".$tool_id."/".$filter_field;
    } 
  }
  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch API credentials
  $credentials = array();
  if (($F = fopen($confFile, "r")) !== FALSE) {
      while (($d = fgetcsv($F, 1000, ";")) !== FALSE) {
          foreach ($d as $a){
              //$r = explode(":",$a);
              $r = preg_replace('/^.:/', "", $a);
              if (isset($r)){array_push($credentials,$r);}
          }
      }
      fclose($F);
  }
  $username = $credentials[0];
  $password = $credentials[1];

  $auth_basic["user"] = $username;
  $auth_basic["pass"] = $password;

  $headers= array('Accept: aplication/json');

  $r = get($url, $headers, $auth_basic);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting tools. Http code= ".$r[1]['http_code'];
    return false;
  } else {
    return json_decode($r[0], true);
  }

}

/**
 * Get community id
 * @param challenge_id
 * @return the community id from the given challenge or false if an error occur.
 */
//var_dump(getCommunityFromChallenge("OEBX0010000001"));
function getCommunityFromChallenge($challenge_id){

  //1. Get benchmarking event id from challenge collection
  $be = getChallenges($challenge_id, "benchmarking_event_id");

  //2. Get community id from the benchmarking event id
  return getBenchmarkingEvents($be,"community_id");
}

/**
 * Get the communities the user have permisions to submit files
 * @param roles array of user roles
 * @return array of communitites id's
 */
function getCommunitiesFromRoles ($roles) {
	$communitites_ids = array();
  
	foreach ($roles as $elem) {
	  $r = explode(":", $elem);
	  if($r[0] == "owner") {
		  array_push($communitites_ids, $r[1]);
	  }else {
		  if($r[0] == "manager" ||$r[0] == "contributor") {
        if(str_starts_with($r[1], 'OEBE')) {
          array_push($communitites_ids, getBenchmarkingEvents($r[1], "community_id") );
        } elseif(str_starts_with($r[1], 'OEBX')) {
          array_push($communitites_ids, getCommunityFromChallenge($r[1]) );
        }
		    
		  }
	  }
	}
	return $communitites_ids;
  
}

function getBEFromRoles (array $roles) {
	$BE_ids = array();
  
	foreach ($roles as $elem) {
	  $r = explode(":", $elem);
    if($r[0] == "owner") {
      $BE_array = json_decode(getBenchmarkingEventsQL($r[1]), true);

      foreach ($BE_array as $key => $value) {
        array_push($BE_ids, $value['_id']);
      }
      
	  } else if($r[0] == "manager") {
      array_push($BE_ids, $r[1] );

	  }else if($r[0] == "contributor") {
        if(str_starts_with($r[1], 'OEBE')) {
          array_push($BE_ids, $r[1]);
		    } 
      
	  }
	}
	return $BE_ids;
  
}

/**
 * Gets the challenges list given a community id
 * @param community id to search
 * @return array of challege/s obj. If an error ocurs it return false.
 */
//var_dump(getChallengesFromACommunity("OEBC002"));
function getChallengesFromACommunity ($community_id) {
  //1. Get benchmarking event collection
  $response = getBenchmarkingEvents();
  $benchmarkId = array();
  foreach ($response as $e) {
    if ($e["community_id"] == $community_id){
      array_push($benchmarkId, $e['_id']);
    }
  }

  //2. Get challenge collection
  $r = getChallenges();
  $challengeList = array();
  foreach ($r as $c) {
    for ($i=0; $i < count($benchmarkId) ; $i++) { 
      if ($c["benchmarking_event_id"] == $benchmarkId[$i]){
        array_push($challengeList, $c);
      }
    }
  }
  return json_encode($challengeList);

}


/**
 * Gets the email of the contacts (NEED authentification!!!)
 * @param array of contacts ids
 * @return associative array of each contacts id and their emails. 
 * If an error ocurs it return false.
 */
//var_dump(getContactEmail(array("Meritxell.Ferret")));
function getContactEmail ($contacts_ids) {
  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch API credentials
  $credentials = array();
  if (($F = fopen($confFile, "r")) !== FALSE) {
      while (($d = fgetcsv($F, 1000, ";")) !== FALSE) {
          foreach ($d as $a){
              $r = preg_replace('/^.:/', "", $a);
              if (isset($r)){array_push($credentials,$r);}
          }
      }
      fclose($F);
  }
  $username = $credentials[0];
  $password = $credentials[1];

  $auth_basic["user"] = $username;
  $auth_basic["pass"] = $password;
  $headers= array('Accept: aplication/json');

  $contacts_emails = array();

  foreach ($contacts_ids as $value) {
    $url = $GLOBALS['OEB_scirestapi']."/Contact/".$value.'/email';

    $r = get($url, $headers, $auth_basic);

    if ($r[1]['http_code'] != 200){
      $_SESSION['errorData']['Warning'][]="Error getting contacts emails. 
        Http code= ".$status;
      $contacts_emails[$value] = 0;
    } else {
      $contacts_emails[$value] = json_decode($r[0], true);
    }
  }
  return $contacts_emails;
   

}

#-------------------------------------------------------------------------------
#------------------------ GRAPHQL API FUNCTIONS -------------------------------/
#-------------------------------------------------------------------------------

/**
 * Gets all contacts id's given a community
 * @param community to search
 * @return json with contacts ids
 */
//var_dump(getAllContactsOfCommunity("OEBC002"));
function getAllContactsOfCommunity ($community_id){

  $data_query =
  '{"query":"query getContacts($community_id: String!){getContacts(contactFilters: {community_id: $community_id}) {_id}}","variables":{"community_id": "'.$community_id.'"}}';
  $url = $GLOBALS['OEB_sciapi'];
  $headers= array('Content-Type: application/json');
  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch API credentials
  $credentials = array();
  if (($F = fopen($confFile, "r")) !== false) {
      while (($d = fgetcsv($F, 1000, ";")) !== false) {
          foreach ($d as $a){
              //$r = explode(":",$a);
              $r = preg_replace('/^.:/', "", $a);
              if (isset($r)){array_push($credentials,$r);}
          }
      }
      fclose($F);
  }
  $username = $credentials[0];
  $password = $credentials[1];

  $auth_basic["user"] = $username;
  $auth_basic["pass"] = $password;

  $r = post($data_query, $url, $headers, $auth_basic);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting contacts. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getContacts;
    return json_encode($items);
  }
}


function getBenchmarkingEventsQL ($community_id){

  $data_query =
  '{"query":"query getBE($community_id: String!){getBenchmarkingEvents(benchmarkingEventFilters: {community_id: $community_id}) {_id\\n          name}}","variables":{"community_id": "'.$community_id.'"}}';

  $url = $GLOBALS['OEB_sciapi'];
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting BenchmarkingEvents. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getBenchmarkingEvents;
    return json_encode($items);
  }
}


function getChallengesQL ($BE_id){

  $data_query =
  '{"query":"query getCH($be: String!){getChallenges(challengeFilters: {benchmarking_event_id: $be}) {_id\\n          name}}","variables":{"be": "'.$BE_id.'"}}';
  $url = $GLOBALS['OEB_sciapi']; 
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting BenchmarkingEvents. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getChallenges;
    return json_encode($items);
  }
}



/**
 * Gets participant tools 
 * @return json with tools ids and names
 */
//var_dump(getTools());
function getTools () {
  $data_query = '{"query":"{ \\n    getTools {\\n      _id\\n      name\\n    }\\n}","variables":{}}';
  $url = $GLOBALS['OEB_sciapi'];
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting tools. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getTools;
    return json_encode($items);
  }

}


/**
* Migrates datasets to final database
* @param token - to take provenance from token
* @return json response or false if error ocurred
*/
function migrateToOEB ($token, $dryrun=true) {

  $url= $GLOBALS['OEB_migrate'].'?dryrun=false';
  if ($dryrun) {
    $url= $GLOBALS['OEB_migrate'].'?dryrun=true';
  }

  $headers= array('Accept: aplication/json', 'Authorization: Bearer '.$token);

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error uploading files. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    return json_decode($r[0], true);
  }
}

//var_dump(getContactsIds("meritxell.ferret@bsc.es"));
/**
 * Gets the contact id from OEB
 * @param email - from vRE
 * @return string - contact.id or false otherwise
 */
function getContactsIds($email){
  $data_query =
  '{"query":"query getContacts($email: String!){getContacts (contactFilters: {email: $email}){_id\\n    }\\n}\\n","variables":{"email":"'.$email.'"}}';
  $url = $GLOBALS['OEB_sciapi'];

  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch API credentials
  $credentials = array();
  if (($F = fopen($confFile, "r")) !== FALSE) {
      while (($d = fgetcsv($F, 1000, ";")) !== FALSE) {
          foreach ($d as $a){
              //$r = explode(":",$a);
              $r = preg_replace('/^.:/', "", $a);
              if (isset($r)){array_push($credentials,$r);}
          }
      }
      fclose($F);
  }
  $username = $credentials[0];
  $password = $credentials[1];

  $auth_basic["user"] = $username;
  $auth_basic["pass"] = $password;

  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers, $auth_basic);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting contacts. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getContacts[0]->_id;
    return $items;
  }
}



/**
 * Gets datasets
 * @return json with datasets objs
 */
//var_dump(getDatasetsQL());
function getDatasetsQL () {
  $data_query = '{"query":"{getDatasets(datasetFilters: {community_id: \\"OEBC001\\"}){id\\n        name\\n        version\\n        description\\n        community_ids\\n        visibility        \\n        type\\n        datalink{\\n            uri\\n        }\\n        \\n\\n\\n    }\\n}","variables":{}}';
  $url = $GLOBALS['OEB_sciapi'];
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting tools. 
      Http code= ".$r[1]['http_code'];
    return $r;
  } else {
    $items = json_decode($r[0])->data->getDatasets;
    return json_encode($items);
  }

}

/**
 * Returns owners or managers of the spiciefied community or be
 * @param Community id (to get owners), or bench event (to get managers)
 * @return 
 */
function getOEBRoles($BE){
  $url =  explode("/staged",$GLOBALS['OEB_scirestapi'])[0];
  $url = $url."/query/contacts/".$BE;

  $headers= array('Accept: aplication/json');

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting benchmarkings events. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    return json_decode($r[0], true);
  }

}

function getUserEmailFromORCID ($orcid){

  $data_query =
  '{"query":"query getContacts($orcid: String!){getContacts(contactFilters:{links: {label: \\"ORCID\\", uri: $orcid}}) {email}}","variables":{"orcid":"'.$orcid.'"}}';
  $url = $GLOBALS['OEB_sciapi'];
  
  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch API credentials
  $credentials = array();
  if (($F = fopen($confFile, "r")) !== FALSE) {
      while (($d = fgetcsv($F, 1000, ";")) !== FALSE) {
          foreach ($d as $a){
              //$r = explode(":",$a);
              $r = preg_replace('/^.:/', "", $a);
              if (isset($r)){array_push($credentials,$r);}
          }
      }
      fclose($F);
  }
  $username = $credentials[0];
  $password = $credentials[1];

  $auth_basic["user"] = $username;
  $auth_basic["pass"] = $password;

  $r = post($data_query, $url, $headers, $auth_basic);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting contacts. 
      Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getContacts;
    return (array) $items[0];
  }
}
