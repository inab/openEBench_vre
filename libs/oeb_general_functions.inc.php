<?php

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
  //$GLOBALS['OEB_scirestapi'] = 'https://openebench.bsc.es/api/scientific/access';
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
 * Gets all benchmarking events with their info or an specific benchmark filtered or not
 * @param $benchmarkingEvent_id, the id of the benchmark to find
 * @param $filter_field, the attribute of the given challenge
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
    $_SESSION['errorData']['Warning'][]="Error getting benchmarkings events. Http code= ".$r[1]['http_code'];
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

/**
 * Get the communities the user have permisions to submit files
 * @param roles array of user roles
 * @return array of communitites id's
 */
function getCommunitiesFromRoles (array $roles) {
	$communitites_ids = array();
  
	foreach ($roles as $elem) {
	  $r = explode(":", $elem);
	  if($r[0] == "owner") {
		array_push($communitites_ids, $r[1] );
	  }else {
		if($r[0] == "manager" || $r[0] == "contributor") {
		  array_push($communitites_ids, getCommunityFromChallenge($r[1]) );
		}
	  }
  
	}
	return $communitites_ids;
  
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
 * @return associative array of each contacts id and their emails. If an error ocurs it return false.
 */
//var_dump(getContactEmail(array("Meritxell.Ferret")));
function getContactEmail ($contacts_ids) {
  //get credentials
  $confFile = $GLOBALS['OEBapi_credentials'];

  // fetch nextcloud API credentials
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

  $contacts_emails = array();

  foreach ($contacts_ids as $value) {
    $url = $GLOBALS['OEB_scirestapi']."/Contact/".$value.'/email';

    $r = get($url, $headers, $auth_basic);

    if ($r[1]['http_code'] != 200){
      $_SESSION['errorData']['Warning'][]="Error getting contacts emails. Http code= ".$status;
      $contacts_emails[$value] = 0;
    } else {
      $contacts_emails[$value] = json_decode($r[0], true);
    }
  }
  return $contacts_emails;
   

}



/**
 * Gets all contacts id's given a community
 * @param community to search
 * @return json with contacts ids
 */
//var_dump(getAllContactsOfCommunity("OEBC002"));
function getAllContactsOfCommunity ($community_id){

  $data_query =
  '{"query":" 
      query getContacts($community_id: String!){
        getContacts(contactFilters: {community_id: $community_id}) {
          _id    
        } 
      }",
      "variables":{"community_id": "'.$community_id.'"}}';
  $url ='https://dev-openebench.bsc.es/sciapi/graphql/'; //dev, in prod is closed
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting contacts. Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getContacts;
    return json_encode($items);
  }
}


/**
 * Gets participant tools ids given a community_id
 * @return json with tools ids and names
 */
//var_dump(getTools());
function getTools () {
  $data_query = 
  '{"query":"{ 
    getTools {
      _id
      name
    }
  }"}';
  $url = $GLOBALS['OEB_sciapi'];
  $headers= array('Content-Type: application/json');

  $r = post($data_query, $url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error getting tools. Http code= ".$r[1]['http_code'];
    return false;
  } else {
    $items = json_decode($r[0])->data->getTools;
    return json_encode($items);
  }

}
//var_dump(migrateToOEB("eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJCakliSkM2WlpBQ2Qxb2VmTC1uMXhoVjJQdDhMWmNZSGM4d2FnZWNiMk40In0.eyJleHAiOjE2MjA3NDU0NDksImlhdCI6MTYyMDc0MTg0OSwiYXV0aF90aW1lIjoxNjIwNzQxODQ5LCJqdGkiOiI0ZmU5MzBkMi0wZjA4LTQ3NTEtODRmMC1lN2E4NWQ1ZWRjMzkiLCJpc3MiOiJodHRwczovL2luYi5ic2MuZXMvYXV0aC9yZWFsbXMvb3BlbmViZW5jaCIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiJiMjU5MWZkOS01OTRjLTRjMDctOGQ5Yi1kOGQxNDEwYTE5MjkiLCJ0eXAiOiJCZWFyZXIiLCJhenAiOiJvZWItdnJlLWRldiIsInNlc3Npb25fc3RhdGUiOiIzY2YwNTJiZC0xNzhiLTRkMjAtYTMyMC00YTY1OWIwNTc4MDgiLCJhY3IiOiIxIiwiYWxsb3dlZC1vcmlnaW5zIjpbImh0dHBzOi8vZGV2LW9wZW5lYmVuY2guYnNjLmVzIl0sInJlYWxtX2FjY2VzcyI6eyJyb2xlcyI6WyJvZmZsaW5lX2FjY2VzcyIsInVtYV9hdXRob3JpemF0aW9uIl19LCJyZXNvdXJjZV9hY2Nlc3MiOnsiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJlbWFpbCBwcm9maWxlIiwiY29tbXVuaXR5X2lkIjpbIk9FQkMwMDIiXSwiZW1haWxfdmVyaWZpZWQiOnRydWUsIm9lYjpyb2xlcyI6WyJvd25lcjpPRUJDMDAyIl0sIm5hbWUiOiJ0ZXN0IHRlc3QiLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJ0ZXN0IiwiZ2l2ZW5fbmFtZSI6InRlc3QiLCJmYW1pbHlfbmFtZSI6InRlc3QiLCJlbWFpbCI6InRlc3RAYnNjLmVzIn0.WycSnWoG4WenCotf27nHQY08Jt_l7HwuBleRviU8DIukBiK_uNuO_O1tLNS5cpJFTQk4mcyoBycokYhhp0Ira388-OYaIrYSQ63LDM0DdxM2lqNd5kH7x5d5EDlNzXfhi4u6PEodXpoHekJoCnjjjfjjkHhsgh6dCeh5yg8-b-LlAa2C4pCgzBILLIMS5NfOPkG5Bsztk-2PZ4_P8gUpGss_IbtRUhCYbCCvnnqOoQ-UPhQ5Ymim2W-_uV75A-12fcPaNq0t3Tjwye0A0_xF4_4fddQ5GJLy5kiVP9AOlA_iQhWRN9lK8eUZRnwHq3zYzaIgHoM89070hwDf7NPB_g", false));
function migrateToOEB ($token, $dryrun=false) {

  $url= $GLOBALS['OEB_migrate'].'?dryrun=false';
  if ($dryrun) {
    $url= $GLOBALS['OEB_migrate'].'?dryrun=true';
  }

  $headers= array('Accept: aplication/json', 'Authorization: Bearer '.$token);

  $r = get($url, $headers);
  if ($r[1]['http_code'] != 200){
    $_SESSION['errorData']['Warning'][]="Error uploading files. Http code= ".$r[1]['http_code'];
    return false;
  } else {
    return json_decode($r[0], true);
  }
}
