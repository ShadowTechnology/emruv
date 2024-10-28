<?php
$mobile = '7200670068'; $otp = '1234';

function send_textlocal_otp($mobile,$otp){

    // Account details
	$apiKey = urlencode('ZDZkMGEyN2NiN2EzNDQ4ZDBkMjFiNzUxN2RmODFkN2M=');
	$template_id = "755953";
	// Message details
	$sender = urlencode('KBPSMS');
    $message = $otp." is your OTP to access your application. 
Powered by Smarther.";
 
	// Prepare data for POST request
	$data = array('apikey' => $apiKey, 'numbers' => $mobile, "sender" => $sender, "message" => $message,  "templates" => array(
      "id"=> "755953",
      "message" => $otp." is your OTP to access your application. 
Powered by Smarther.",
      "title" => "sendotp",
      "senderName" => "KBPSMS",
      "isMyDND" => "n"
    )
            );
 
	// Send the POST request with cURL
	$ch = curl_init('https://api.textlocal.in/send/');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	
	// Process your response here
	//echo $response;
}

send_textlocal_otp($mobile,$otp);
