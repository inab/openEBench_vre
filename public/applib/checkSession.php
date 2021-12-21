<?php

require "../../config/globals.inc.php";

// resume session without restarting timeout
session_start();

// timeout in seconds for the ending of sessions
$timeout = $GLOBALS['TIMEOUT']; 

// Check if the timeout field exists.
if(isset($_SESSION['VREtimeout'])) {
    // See if the number of seconds since the last visit is larger than the timeout period.
    $duration = time() - (int)$_SESSION['VREtimeout'];

    if($duration > $timeout) {
	$dtF = new DateTime();
	$dtT = new DateTime();
	$dtF->setTimestamp(0);
	$dtT->setTimestamp($duration);
	$duration_time = $dtF->diff($dtT)->format('%H:%I:%S'); 
	echo '{"hasSession":false, "duration":"'.$duration_time.'"}';

    } else {
	// restarting sessions
	$remaining = $timeout - $duration;
	echo '{"hasSession":true, "remaining":"'.sprintf('%02d', $remaining).'"}';
   }
}
 
