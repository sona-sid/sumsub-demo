<?php
namespace App;

require_once __DIR__ . '/vendor/autoload.php'; 

// The description of the authorization method is available here: https://developers.sumsub.com/api-reference/#app-tokens
// define("SUMSUB_SECRET_KEY", "WzgrcqevXSt8RwhXij7WcF6dqy8rfKkE"); // Example: Hej2ch71kG2kTd1iIUDZFNsO5C1lh5Gq
// define("SUMSUB_APP_TOKEN", "sbx:l0AEu5RPo4BwlXq9KjLD3VUd.Zj52FB5w0yj5PcJ4tOF7LdaxwvV7Y24S"); // Example: sbx:uY0CgwELmgUAEyl4hNWxLngb.0WSeQeiYny4WEqmAALEAiK2qTC96fBad


//production values
// define("SUMSUB_SECRET_KEY", "Q5IxpWThnHGIznshl5YjcsOWa26tcVEs"); 
// define("SUMSUB_APP_TOKEN", "prd:CuTMcFa0KtXucJANLGiN7qi2.EbviEYldGrTvLi6Hp7jqCnNWVZFh8j5O"); 

// sns sandbox
define("SUMSUB_SECRET_KEY", "3QOjbHvqoeM8cvGXJEQ4MwZESkmmW7jP"); 
define("SUMSUB_APP_TOKEN", "sbx:9dw4GKfiHpKVLIugxCU5AIvM.TSwpkxeB9ZBqseLJnludAV4ycl5trOMj"); 


define("SUMSUB_TEST_BASE_URL", "https://api.sumsub.com");
//Please don't forget to change token and secret key values to production ones when switching to production

class SumsubCurl {

    function sendHttpRequest($request, $url) {
        $ts = time();

        $headers = array(
            'X-App-Token: '.SUMSUB_APP_TOKEN,
            'X-App-Access-Sig: '.$this->createSignature($ts, $request->getMethod(), $url, $request->getBody()),
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


    public function createApplicant1($externalUserId, $levelName)
        // https://developers.sumsub.com/api-reference/#creating-an-applicant
    {
        $requestBody = array(
            'externalUserId' => $externalUserId
        );

        $url = '/resources/applicants?levelName=' . $levelName;  

        $request = new GuzzleHttp\Psr7\Request('POST', SUMSUB_TEST_BASE_URL . $url);
        $request = $request->withHeader('Content-Type', 'application/json');
        //$request = $request->withBody(GuzzleHttp\Psr7\stream_for(json_encode($requestBody)));
        $request = $request->withBody(GuzzleHttp\Psr7\Utils::streamFor(json_encode($requestBody)));

        $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody)->{'id'};
    }



    function createApplicant($externalUserId, $levelName) {
        $requestBody = array(
            'externalUserId' => $externalUserId
        );

        $url = '/resources/applicants?levelName=' . $levelName;
        $requestUrl = SUMSUB_TEST_BASE_URL . $url;

        // $responseBody = $this->sendHttpRequest($requestBody, $requestUrl);
        // return json_decode($responseBody);

        $ts = time();

        $headers = array(
            'X-App-Token: '.SUMSUB_APP_TOKEN,
            'X-App-Access-Sig: '.$this->createSignature($ts, 'POST', $url,''),
            'X-App-Access-Ts: '.$ts
        );

        // Reset stream offset to read body in `curl_exec` method from the start
        // $body = $request->getBody();
        // $body->rewind();
        // $bodyContent = $body->getContents();

        $ch = curl_init($url);

        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyContent);
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

        /* 
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
        return $responseBody['id']; */
    }
}


echo $externalUserId     = uniqid();
$levelName          = 'basic-kyc-level';

$testObject         = new SumsubCurl();
$applicantCreate    = $testObject->createApplicant($externalUserId, $levelName);

$applicant_array    = json_decode(json_encode($applicantCreate), true);
print_r( $applicant_array );

?>
