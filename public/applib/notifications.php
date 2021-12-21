<?php

//Manages the backend of notifications

require __DIR__."/../../config/bootstrap.php";
redirectOutside();

if($_REQUEST) { 
    //https://dev-openebench.bsc.es/vre/applib/notifications.php?action=getNotifications&view=
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getNotifications") {
		if (isset($_REQUEST['view'])) {
			if($_REQUEST["view"] != "") {
				Notification::updateNotifications(array('receiver' => $_SESSION['User']['id'], "is_seen" => 0), array('$set' => array("is_seen" => 1)));
			}
			$options = ['sort' => ['created_at' => -1], 'limit' => 10];
			$allUserNotifications = Notification::selectAllNotifications (array('receiver' => $_SESSION['User']['id']), $options);
			$userNotificationsFound = count($allUserNotifications);

			$output = "";
			$userNotificationsUnseen = 0;
			if ($userNotificationsFound >0){
				foreach ($allUserNotifications as $n) {
					$output .= sprintf("<li><a href='%s'>%s</br></a></li>",$n["redirectOnClick"], $n["content"]);
				}
			}else {
				$output .= '<li><a href="#" class="text-bold text-italic">No notifications found</a></li>';
			}
			
			$userNotificationsUnseen = count(Notification::selectAllNotifications (array('receiver' => $_SESSION['User']['id'], 'is_seen' => 0)));
			$data = array(
				'notification' => $output,
				'unseen_notification'  => $userNotificationsUnseen
			);
			echo json_encode($data);
			exit;
		}
    }

}else {
    echo '{}';
    exit;
}