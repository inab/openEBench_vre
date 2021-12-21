<?php

//require_once('classes/class.smtp.php');
//require_once('classes/class.phpmailer.php');
//require_once('classes/Email.php');

function sendEmail($recipient, $subject, $body, $reply = null, $bcc = null, $debug =0){

	$confFile = $GLOBALS['mail_credentials'];
	$conf = array();
	if (($F = fopen($confFile, "r")) !== FALSE) {
	    while (($data = fgetcsv($F, 1000, ";")) !== FALSE) {
    		foreach ($data as $a){
               	    $r = explode(":",$a);
                    if (isset($r[1])){array_push($conf,$r[1]);}
	        }
            }
            fclose($F);
    	}   
	
	$mail = new PHPMailer(); // create a new object
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = $debug; // debugging: 0 = no messages, 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true; // authentication enabled
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
	$mail->Host = $conf[2];
	$mail->Port = 465; // or 587
	$mail->IsHTML(true);
	$mail->Username = $conf[0];
	$mail->Password = $conf[1];

	if(!isset($reply)) $reply = $GLOBALS['ADMINMAIL'];

	$mail->AddReplyTo($reply, $GLOBALS['FROMNAME']);
	$mail->SetFrom($reply, $GLOBALS['FROMNAME']);
	$mail->Subject = $subject;
	$mail->Body = $body;
	// ******************
	$mail->AddAddress($recipient);
	// ******************

	if(isset($bcc)) {
		$mail->addBCC($bcc);
	}

	if(!$mail->Send()) {
		return false;
	} else {
		$f = array("Email" => $recipient);
		$objMail = new Email($f, True);
		$mailObj = (array)$objMail;
		$GLOBALS['logMailCol']->insertOne($mailObj);
		return true;
	}

}

function requestPremiumUser_DEPRECATED($login, $name, $surname){
	
	$subject = $GLOBALS['NAME']." Request Premium User";
	$message = ' 
	Hello '.utf8_decode($name).' '.utf8_decode($surname).',<br><br>
		
	Your request for a premium user account is being processed. In the meantime, you can use the platform as a '.$GLOBALS['ROLES']['2'].' user.'.'<br><br>

	Thanks for using '.$GLOBALS['NAME'].'.';
	
	sendEmail($login,$subject,$message);

}

function requestNewPassword_DEPRECATED($login, $name, $surname, $hash){
	
	$subject = $GLOBALS['NAME']." Request new Password";
	$message = ' 
	Hello '.utf8_decode($name).' '.utf8_decode($surname).',<br><br>
		
	To reset your password please follow the link below:'.'<br>

	<a href="'.$GLOBALS['URL'].'user/resetPassword.php?q='.$hash.'">'.$GLOBALS['URL'].'user/resetPassword.php?q='.$hash.'</a><br><br>
			
	Thanks for using '.$GLOBALS['NAME'].'.';

	if(sendEmail($login,$subject,$message)) {
		return "1";
	}else{
		return "2";
	}

}

function answerPremium_DEPRECATED($login, $name, $surname, $type){

	$subject = $GLOBALS['NAME']." Request Premium User";
	
	if($type == 1){
		$message = ' 
		Hello '.utf8_decode($name).' '.utf8_decode($surname).',<br><br>	
		Your request to be a premium user on the platform has been accepted'.'<br><br>
		Thanks for using BioActive Compounds.';
	}else if($type == 101){
		$message = ' 
		Hello '.utf8_decode($name).' '.utf8_decode($surname).',<br><br>	
		Your request to be a premium user on the platform has been rejected'.'<br><br>
		Thanks for using '.$GLOBALS['NAME'].'.';
	}

	sendEmail($login,$subject,$message);

}

function sendWelcomeToNewUser($variables){
	$variables['platform_name'] = $GLOBALS['NAME'];
	$variables['VRE_url'] = $GLOBALS['URL'];
	$subject = "Welcome to ".$GLOBALS['NAME']." platform";
	$message = fillContentEmail($GLOBALS['htmlib'].'/EmailsTemplates/welcomeNewUserEmail.php', $variables);

	sendEmail($variables['login'],$subject,$message);

}


function sendPasswordToNewUser($variables){
		
	$variables['platform_name'] = $GLOBALS['NAME'];
	$variables['VRE_url'] = $GLOBALS['URL'];
	$subject = $GLOBALS['NAME']." New Account";
	$message = fillContentEmail($GLOBALS['htmlib'].'/EmailsTemplates/passwordNewUserEmail.php', $variables);

	sendEmail($variables['user_email'],$subject,$message);

}

function fillContentEmail($template, $params) {
	$template = file_get_contents($template, FILE_USE_INCLUDE_PATH);

	foreach($params as $key => $value){
		$template = str_replace('{{ '.$key.' }}', $value, $template);
	}

	return $template;
}

/**
 * Sends an email to the person who can approve petitions
 * @param variables - associative array
 */

function sendRequestToApprover ($variables){
	$variables['url_login'] = $GLOBALS['URL_login'];
	$variables['oeb_doc'] = $GLOBALS['OEB_doc'];
	$variables['oeb_support'] = $GLOBALS['MAIL_SUPPORT_OEB'];
	//$approver_attr = $GLOBALS['usersCol']->findOne(array('Email' => $variables['approver']));
	$subject = $GLOBALS['NAME']." New request for OpenEBench data publication. Action required";
	$message = fillContentEmail($GLOBALS['htmlib'].'/EmailsTemplates/OEB_requestEmail.php', $variables);
	
	//$bcc = $GLOBALS['ADMINMAIL'];
	
	return sendEmail($variables['approver'],$subject,$message, null, $bcc);

}

/**
 * Sends an email to the requester that its petitions has been approved
 * @param variables - associative array
 */

function sendUpdateApproveRequester ($variables){
	$variables['url_login'] = $GLOBALS['URL_login'];
	$variables['oeb_doc'] = $GLOBALS['OEB_doc'];
	$variables['oeb_support'] = $GLOBALS['MAIL_SUPPORT_OEB'];
	$subject = $GLOBALS['NAME']." Your request has been approved";
	$message = fillContentEmail($GLOBALS['htmlib'].'/EmailsTemplates/OEB_approveEmail.php', $variables);
	
	//$bcc = $GLOBALS['ADMINMAIL'];
	
	return sendEmail($variables['requester'],$subject,$message, null, $bcc);

}
