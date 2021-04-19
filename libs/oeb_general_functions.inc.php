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

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Accept: aplication/json'
    ),
  ));

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting datasets. Http code= ".$status;
    return false;
  } else {
     return json_decode($response, true);
  }

  curl_close($curl);

}


/**
 * Gets all datasets with their info or an specific dataset filtered or not
 * @param $dataset_id, the id of the dataset to find
 * @param $filter_field, the attribute of the given dataset
 * @return dataset/s (json format). If an error ocurs it return false.
 */
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

  
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Accept: application/json'
    ),
  ));

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting datasets. Http code= ".$status;
    return false;
  } else {
     return json_decode($response, true);
     
  }

  curl_close($curl);

}



/**
 * Get community id
 * @param challenge_id
 * @return the community id from the given challenge or false if an error occur.
 */
function getCommunityFromChallenge($challenge_id){

  //1. Get benchmarking event id from challenge collection

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $GLOBALS['OEB_scirestapi'].'/Challenge/'.$challenge_id.'/benchmarking_event_id',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET'
  ));

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting benchmnarking id. Http code= ".$status;
    return false;
  } else {
    $response = substr($response,1,-1);
    
    //2. Get community id from the benchmarking event id
    $c = curl_init();

    curl_setopt_array($c, array(
      CURLOPT_URL => $GLOBALS['OEB_scirestapi'].'/BenchmarkingEvent/'.$response.'/community_id',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
      ),
    ));
    
    $r = curl_exec($c);
    $s = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($s!= 200) {
      $_SESSION['errorData']['Warning'][]="Error getting community id. Http code= ".$status;
      return false;
    } else {
      $r= substr($r,1,-1);
      return $r;
    }
  }

  curl_close($curl);
  curl_close($c);
}

/**
 * Get benchmarking event from ID
 * @param benchmarking_event_id, OEB Identifier
 * @param $filter_field, a single attribute of benchmarking_event to be returned
 * @return the full benchmarking event document, or the particular attribute specified by 'filter_field'
 */

function getBenchmarkingEventFromId($benchmarking_event_id,$filter_field = null){

    // build URL	
    $URL = $GLOBALS['OEB_scirestapi'].'/BenchmarkingEvent/'.$benchmarking_event_id;
    if (isset($filter_field) && is_string($filter_field)){
	    $URL.="/$filter_field";
    }
    // do HTTP Request
    $c = curl_init();
    curl_setopt_array($c, array(
      CURLOPT_URL => $URL,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
      ),
    ));
    
    $r = curl_exec($c);
    $s = curl_getinfo($c, CURLINFO_HTTP_CODE);

    // convert from JSON response to PHP HASH/ARRAY/STRING
    $r = json_decode($r,true);

    if ($s!= 200) {
      $_SESSION['errorData']['Warning'][]="Error retrieving benchmarking event for id='$benchmarking_event_id' from OEB. Http code= ".$status;
      return false;
    } else {
      return $r;
    }
    curl_close($c);
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
function getChallengesFromACommunity ($community_id) {
  //1. Get benchmarking event collection

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $GLOBALS['OEB_scirestapi'].'/BenchmarkingEvent',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Accept: application/json'
    ),
  ));
  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting benchmnarking event collection. Http code= ".$status;
    return false;
  } else {
    $response = json_decode($response, true);
    $benchmarkId = array();
    foreach ($response as $e) {
      if ($e["community_id"] == $community_id){
        array_push($benchmarkId, $e['_id']);
      }
    }

    //2. Get challenge collection
    $c = curl_init();

    curl_setopt_array($c, array(
      CURLOPT_URL => $GLOBALS['OEB_scirestapi'].'/Challenge',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
      ),
    ));

    $r = curl_exec($c);
    $s = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($s!= 200) {
      $_SESSION['errorData']['Warning'][]="Error getting challenge collection. Http code= ".$status;
      return false;
    } else {
      $r= json_decode($r, true);
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
  }
  curl_close($curl);
  curl_close($c);

}


/**
 * Gets benchmarking events manager contacts ids
 * @param community_id to look for contacts
 * @return array of contacts ids. If an error ocurs it return false.
 */
function getBenchmarkingContactsIds ($community_id) {
  //1. Get benchmarking contacts ids of a community

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://openebench.bsc.es/api/scientific/access/Community/'.$community_id.'/community_contact_ids',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Accept: application/json'
    ),
  ));

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting benchmnarking contacts. Http code= ".$status;
    return false;
  } else {
    return json_decode($response, true);
  }

  curl_close($curl);

}

/**
 * Gets the email of the contacts
 * @param array of contacts ids
 * @return associative array of each contacts id and their emails. If an error ocurs it return false.
 */
function getContactEmail ($contacts_ids) {

  $contacts_emails = array();

  foreach ($contacts_ids as $value) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://openebench.bsc.es/api/scientific/access/Contact/'.$value.'/email',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
      ),
    ));

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($status!= 200) {
      $_SESSION['errorData']['Warning'][]="Error getting email of ".$value.". Http code= ".$status;
      $contacts_emails[$value] = 0;

    } else {
      $contacts_emails[$value] = $response;

    }
    curl_close($curl);
  }
  return $contacts_emails;

}



/**
 * Gets all contacts id's given a community
 * @param community to search
 * @return array with contacts ids
 */
function getAllContactsOfCommunity ($community_id){

  $curl = curl_init();
  $data_query =
    '{"query":" 
        query getContacts($community_id: String!){
          getContacts(contactFilters: {community_id: $community_id}) {
            _id    
          } 
        }",
        "variables":{"community_id": "'.$community_id.'"}}';

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://dev-openebench.bsc.es/sciapi/graphql/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$data_query,
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));
  

  $response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ($status!= 200) {
    $_SESSION['errorData']['Warning'][]="Error getting contacts. Http code= ".$status;
    return false;
  } else {
     return json_decode($response)->data->getContacts;
     
  }

  curl_close($curl);


}


/**
 * Gets participant tools ids given a community_id
 * @return json with tools ids and names
 */
function getTools () {
 
    $curl = curl_init();
    $data_query = 
      '{"query":"{ 
        getTools {
          _id
          name
        }
      }"}';
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://dev-openebench.bsc.es/sciapi/graphql/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>$data_query,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
  
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  
    if ($status!= 200) {
      $_SESSION['errorData']['Warning'][]="Error getting contacts. Http code= ".$status;
      return false;
    } else {
      $items = json_decode($response)->data->getTools;
      return json_encode($items);
       
    }
  
    curl_close($curl);
  
    
}