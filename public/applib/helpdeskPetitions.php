
<?php

//Manages the backend request of helpdesk tab
require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) {
    //https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getActors
    if(isset($_REQUEST['getActors'])) {
        echo actorsInfo();
		exit;
    //https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getApprovers&communityId=OEBC004
    }elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "getApprovers") {
        if (isset($_REQUEST['communityId'])) {
            $community_id = $_REQUEST['communityId'];
            //associative array of all approvers (id - email):
            $approversContacts = getContactEmail (getCommunities('OEBC004', 'community_contact_ids'));
            foreach ($approversContacts as $key => $value) {
                //sendRequestToApprover("meritxell.ferret@bsc.es", $_SESSION['User']['id'], $fn);
            }
        }   exit;
        //not finished
        
    }  else {
        echo '{}';
        exit;
    }
}

/**
 * Function to know the approver from 
 */


function actorsInfo() {
    //user role
    $role = $_SESSION['User']['TokenInfo']['oeb:roles'];
    //initiallize variables
    $block_json="{}";
    
    //user logged
	$userId = $_SESSION["User"]["id"];

	//user info
    $user = $GLOBALS['usersCol']->findOne(array("id"=>$userId), array("oeb_community"=>1, "id"=>1, "Name"=>1, "Surname"=>1, "Email" =>1));
    $community_id = $user['oeb_community'];
    $user['community_name'] = getCommunities($community_id, "name");
    $block_json = json_encode($user, JSON_PRETTY_PRINT);

    return $block_json;
 }
