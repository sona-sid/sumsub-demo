<?php
namespace App;

require_once __DIR__ . '/vendor/autoload.php'; 

// The description of the authorization method is available here: https://developers.sumsub.com/api-reference/#app-tokens
define("SUMSUB_SECRET_KEY", "WzgrcqevXSt8RwhXij7WcF6dqy8rfKkE"); // Example: Hej2ch71kG2kTd1iIUDZFNsO5C1lh5Gq
define("SUMSUB_APP_TOKEN", "sbx:l0AEu5RPo4BwlXq9KjLD3VUd.Zj52FB5w0yj5PcJ4tOF7LdaxwvV7Y24S"); // Example: sbx:uY0CgwELmgUAEyl4hNWxLngb.0WSeQeiYny4WEqmAALEAiK2qTC96fBad

//production values
// define("SUMSUB_SECRET_KEY", "Q5IxpWThnHGIznshl5YjcsOWa26tcVEs"); 
// define("SUMSUB_APP_TOKEN", "prd:CuTMcFa0KtXucJANLGiN7qi2.EbviEYldGrTvLi6Hp7jqCnNWVZFh8j5O"); 

define("SUMSUB_TEST_BASE_URL", "https://api.sumsub.com");
//Please don't forget to change token and secret key values to production ones when switching to production


function sendHttpRequest($request, $url) {
    $ts = time();

    $headers = array(
        'X-App-Token: '.SUMSUB_APP_TOKEN,
        'X-App-Access-Sig: '.createSignature($ts, $request->getMethod(), $url, $request->getBody()),
        'X-App-Access-Ts: '.$ts
    );

    // Reset stream offset to read body in `curl_exec` method from the start
    $body = $request->getBody();
    $body->rewind();
    $bodyContent = $body->getContents();

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyContent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    try {
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200 && $http_code != 201) {
            // https://developers.sumsub.com/api-reference/#errors
            // If an unsuccessful answer is received, please log the value of the "correlationId" parameter.
            // Then perhaps you should throw the exception. (depends on the logic of your code)
        }
    } catch (Exception $e) {
        error_log($e);
    }

    curl_close($ch);

    return $response;
}

function createSignature($ts, $httpMethod, $url, $httpBody) {
    $data = $ts . strtoupper($httpMethod) . $url . $httpBody;
    $signature = hash_hmac('sha256', $data, SUMSUB_SECRET_KEY);
    return $signature;
}

function createApplicant($externalUserId, $levelName) {
    $requestBody = array(
        'externalUserId' => $externalUserId
    );

    $url = '/resources/applicants?levelName=' . $levelName;
    $requestUrl = SUMSUB_TEST_BASE_URL . $url;

    $headers = array(
        'Content-Type: application/json'
    );

    $ch = curl_init($requestUrl);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    try {
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200 && $http_code != 201) {
            // https://developers.sumsub.com/api-reference/#errors
            // If an unsuccessful answer is received, please log the value of the "correlationId" parameter.
            // Then perhaps you should throw the exception. (depends on the logic of your code)
        }
    } catch (Exception $e) {
        error_log($e);
    }

    curl_close($ch);

    $responseBody = json_decode($response, true);
    return $responseBody['id'];
}


?>
