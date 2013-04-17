
<h3> This is a test</h3>
<?php

include_once 'Krumo/class.krumo.php';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
$postFields = array();

// $url = 'https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing';
$url = 'http://facebook.com'; 
$referer = 'http://webdev01.devmags.com/~nbhacom/zietlow_test/curl%20test/referer.php';

foreach ($_POST as $key => $value) {
	$postFields[$key] = $value;
}


krumo($_POST);



$ch = curl_init();

curl_setopt_array($ch, array(
	CURLOPT_URL => $url,
	CURLOPT_POST => TRUE,
	CURLOPT_POSTFIELDS => $postFields,
	CURLOPT_REFERER => $referer,
	// 	CURLOPT_VERBOSE => TRUE, //debug
	// 	CURLINFO_HEADER_OUT => TRUE, //debug
	CURLOPT_SSL_VERIFYPEER => FALSE,
	CURLOPT_FOLLOWLOCATION => TRUE,
));

curl_exec($ch);
curl_close($ch);
