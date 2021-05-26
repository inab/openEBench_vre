<?php

require __DIR__."/../../config/bootstrap.php";

redirectOutside();

// Check query
if(!$_REQUEST){
	redirect($GLOBALS['URL']);

}elseif (!isset($_REQUEST['account'])) {
	redirect($_SERVER['HTTP_REFERER']);
}

//
// Process actions for the linked account

switch ($_REQUEST['account']) {
	case "euBI":
		// Process according to 'action'
		switch ($_REQUEST['action']) {

		    // Validate and Save/Update Alias Token
		    case "update":
		    case "new":

		    	// Check compulsory fields
		    	if (!isset($_POST['alias_token']) || !isset($_POST['secret'])) {
				$_SESSION['errorData']['Error'][]="Not receiving expected fields. Please, submit the data again.";
				$_SESSION['formData'] = $_POST;
				redirect($_SERVER['HTTP_REFERER']);
		    	}

		    	// Add/Update eurobioimanging Token
			$r = addUserLinkedAccount_euBI($_POST['alias_token'],$_POST['secret']);
			if(!$r){
				$_SESSION['errorData']['Error'][]="Failed to link euroBioImaging account";
				$_SESSION['formData'] = $_POST;
				redirect($_SERVER['HTTP_REFERER']);
			}

			$_SESSION['errorData']['Info'][]="Account successfully linked";
			redirect($GLOBALS['BASEURL']."user/usrProfile.php#tab_1_4");
			break;

		    // Delete Alias Token
		    case "delete":

			$r = deleteUserLinkedAccount($_SESSION['User']['_id'],$_REQUEST['account']);
			if(!$r){
				$_SESSION['errorData']['Error'][]="Failed to unlink euroBioImaging account";
				redirect($_SERVER['HTTP_REFERER']);
			}
			$_SESSION['errorData']['Info'][]="Account successfully unlinked";
			redirect($GLOBALS['BASEURL']."user/usrProfile.php#tab_1_4");
			break;
		}
		break;

	case "EGA":

		break;
	case "b2share":
		// Process according to 'action'
		switch ($_REQUEST['action']) {

		    // Validate and Save/Update Alias Token
		    case "update":
		    case "new":

		    	// Check compulsory fields
		    	if (!isset($_POST['access_token']) ) {
				$_SESSION['errorData']['Error'][]= "Not receiving expected fields. Please, submit the data again.";
				$_SESSION['formData'] = $_POST;
				redirect($_SERVER['HTTP_REFERER']);
			}


                	// TODO Validate given Token against b2share. Query EUDAT API using given token


			$GLOBALS['b2share_host'] = rtrim($GLOBALS['b2share_host'], '/');
			list($r,$info) = get($GLOBALS['b2share_host'].'/api/records/?drafts&access_token='.$_POST['access_token']);


		        if ($info['http_code'] !== 200){
		                $_SESSION['errorData']['Error'][]= "Cannot validate given access token against ".$GLOBALS['b2share_host']. ". Please, go to EUDAT web site and make sure the token is active.";

				$_SESSION['formData'] = $_POST;
				redirect($_SERVER['HTTP_REFERER']);
		        }


			// Add/update Token

			$data = array( 
					"access_token"   => $_REQUEST['access_token'],
					"server"         => $GLOBALS['b2share_host'],
					"last_validated" => new MongoDB\BSON\UTCDateTime()
					);

			$r = addUserLinkedAccount($_SESSION['User']['_id'],$_REQUEST['account'],$data);

			if(!$r){
				$_SESSION['errorData']['Error'][]="Failed to link new account";
				$_SESSION['formData'] = $_POST;
				redirect($_SERVER['HTTP_REFERER']);
			}

			$_SESSION['errorData']['Info'][]="Account successfully linked";
			redirect($GLOBALS['BASEURL']."user/usrProfile.php#tab_1_4");
			break;

		    // Delete Access Token
		    case "delete":

			$r = deleteUserLinkedAccount($_SESSION['User']['_id'],$_REQUEST['account']);
			if(!$r){
				$_SESSION['errorData']['Error'][]="Failed to unlink  account";
				redirect($_SERVER['HTTP_REFERER']);
			}
			$_SESSION['errorData']['Info'][]="Account successfully unlinked";
			redirect($GLOBALS['BASEURL']."user/usrProfile.php#tab_1_4");
			break;
		}
		break;
	default:
		$_SESSION['errorData']['Error'][]= "Account of type '".$_REQUEST['account']."' is not yet supported.";
		redirect($_SERVER['HTTP_REFERER']);

}

redirect($_SERVER['HTTP_REFERER']);
?>
