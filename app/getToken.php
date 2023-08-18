<?php
include_once('AppTokenGuzzlePhpExample.php');

$testObject = new AppTokenGuzzlePhpExample();

$externalUserId = uniqid();
$levelName = 'basic-kyc-level';

$accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
$response_token = json_decode( $accessTokenStr, true );
print_r( $response_token );
?>
