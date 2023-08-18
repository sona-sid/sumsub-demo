<?php

$SUMSUB_SECRET_KEY = "vatLA26TIFaN72ZIv10ZqglS41GPs2LZ";  // Example: Hej2ch71kG2kTd1iIUDZFNsO5C1lh5Gq
$SUMSUB_APP_TOKEN = "sbx:g1gA8iKfsHbOtyhbyQ27NyZW.fzVz9h8mLteKgfq82OGJHvmiDGpWieyI";  // Example: sbx:uY0CgwELmgUAEyl4hNWxLngb.0WSeQeiYny4WEqmAALEAiK2qTC96fBad
$SUMSUB_TEST_BASE_URL = "https://api.sumsub.com";
$REQUEST_TIMEOUT = 60;

function create_applicant($external_user_id, $level_name) {
    global $SUMSUB_SECRET_KEY, $SUMSUB_APP_TOKEN, $SUMSUB_TEST_BASE_URL, $REQUEST_TIMEOUT;

    // Construct request data
    $body = array('externalUserId' => $external_user_id);
    $params = array('levelName' => $level_name);
    $headers = array(
        'Content-Type: application/json',
        'Content-Encoding: utf-8'
    );

    // Sign request
    $request = array(
        'http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => json_encode($body),
            'timeout' => $REQUEST_TIMEOUT
        )
    );
    $context = stream_context_create($request);
    $url = $SUMSUB_TEST_BASE_URL . '/resources/applicants?levelName=' . $level_name;
    $resp = file_get_contents($url, false, $context);
    $response = json_decode($resp, true);

    $applicant_id = $response['id'];
    return $applicant_id;
}

function add_document($applicant_id) {
    global $SUMSUB_SECRET_KEY, $SUMSUB_APP_TOKEN, $SUMSUB_TEST_BASE_URL, $REQUEST_TIMEOUT;

    // Construct request data
    $headers = array(
        'Content-Type: multipart/form-data',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($SUMSUB_APP_TOKEN . ':' . $SUMSUB_SECRET_KEY)
    );

    $curl = curl_init();

    // Set curl options
    curl_setopt($curl, CURLOPT_URL, $SUMSUB_TEST_BASE_URL . '/resources/applicants/' . $applicant_id . '/info/idDoc');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, $REQUEST_TIMEOUT);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        echo "Error: " . $error;
    } else {
        $response_data = json_decode($response, true);
        // Process response data
        // ...
    }
}

