<?php

# /highfive Slash Command

$slashCommand = "/highfive";
$slashToken = "SLASH COMMAND TOKEN";
$slackWebhook = "INCOMING SLACK WEBHOOK";
$highfiveAccount = "HIGHFIVE ACCOUNT/COMPANY NAME";

// Optional if reusing an existing webhook
# $botUsername = "Highfive";
# $botIcon = ":highfive:";


if($_POST['token'] == $slashToken && $_POST['command'] == $slashCommand){


	function callName($string) {
	    # lowercase
	    $string = strtolower($string);
	    # alphanumeric
	    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
	    # remove extra dashes and whitespace
	    $string = preg_replace("/[\s-]+/", " ", $string);
	    # whitespaces and underscores to dashes
	    $string = preg_replace("/[\s_]/", "-", $string);
	    return $string;
	}

	$urlBase = "https://".$highfiveAccount.".highfive.com/";

	$callName = callName($_POST['text']);

	# if call name is blank, use channel name
	if($callName == ""){
		$callName = $_POST['channel_name'];
	}

	# use a generated string for blank call names from a direct message
	if($callName == "directmessage"){
		$callName = "d-".substr(uniqid('', true), -5);	
	}

	# assemble the link
	$callLink = $urlBase.$callName;
	
	# use channel_id to allow for direct messages as well
	$channel = $_POST['channel_id'];

	# put it all together
	$message = "*Join @".$_POST['user_name']." on a Highfive Call*\n>".$callLink;
	
	# post message to appropriate channel or dm via incoming webhook
	function slack($message, $room, $webhook, $username, $icon) {
        $room = ($room) ? $room : "general";
        $data = "payload=" . json_encode(array(
        		"username"		=>  "{$username}",
        		"icon_emoji"	=>  "{$icon}",
                "channel"       =>  "{$room}",
                "text"          =>  $message
            ));
		
        $ch = curl_init($webhook);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }

	slack($message,$channel, $slackWebhook, $botUsername, $botIcon);

	return;

} else {
	header("HTTP/1.0 400 Bad Request");
}