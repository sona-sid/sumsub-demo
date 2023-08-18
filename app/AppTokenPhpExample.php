<?php
namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp;
use GuzzleHttp\Psr7\MultipartStream;

// The description of the authorization method is available here: https://developers.sumsub.com/api-reference/#app-tokens
define("SUMSUB_SECRET_KEY", "WzgrcqevXSt8RwhXij7WcF6dqy8rfKkE"); // Example: Hej2ch71kG2kTd1iIUDZFNsO5C1lh5Gq
define("SUMSUB_APP_TOKEN", "sbx:l0AEu5RPo4BwlXq9KjLD3VUd.Zj52FB5w0yj5PcJ4tOF7LdaxwvV7Y24S"); // Example: sbx:uY0CgwELmgUAEyl4hNWxLngb.0WSeQeiYny4WEqmAALEAiK2qTC96fBad

//production values
// define("SUMSUB_SECRET_KEY", "Q5IxpWThnHGIznshl5YjcsOWa26tcVEs"); 
// define("SUMSUB_APP_TOKEN", "prd:CuTMcFa0KtXucJANLGiN7qi2.EbviEYldGrTvLi6Hp7jqCnNWVZFh8j5O"); 

define("SUMSUB_TEST_BASE_URL", "https://api.sumsub.com");
//Please don't forget to change token and secret key values to production ones when switching to production

class AppTokenGuzzlePhpExample
{
    public function createApplicant($externalUserId, $levelName)
        // https://developers.sumsub.com/api-reference/#creating-an-applicant
    {
        $requestBody = [
            'externalUserId' => $externalUserId
            ];

        $url = '/resources/applicants?levelName=' . $levelName;
        $request = new GuzzleHttp\Psr7\Request('POST', SUMSUB_TEST_BASE_URL . $url);
        $request = $request->withHeader('Content-Type', 'application/json');
        //$request = $request->withBody(GuzzleHttp\Psr7\stream_for(json_encode($requestBody)));
        $request = $request->withBody(GuzzleHttp\Psr7\Utils::streamFor(json_encode($requestBody)));


        $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody)->{'id'};
    }

    public function sendHttpRequest($request, $url)
    {
        $client = new GuzzleHttp\Client();
        $ts = time();

        $request = $request->withHeader('X-App-Token', SUMSUB_APP_TOKEN);
        $request = $request->withHeader('X-App-Access-Sig', $this->createSignature($ts, $request->getMethod(), $url, $request->getBody()));
        $request = $request->withHeader('X-App-Access-Ts', $ts);
        
        // Reset stream offset to read body in `send` method from the start
        $request->getBody()->rewind();

        try {
            $response = $client->send($request);
            if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201) {
                // https://developers.sumsub.com/api-reference/#errors
                // If an unsuccessful answer is received, please log the value of the "correlationId" parameter.
                // Then perhaps you should throw the exception. (depends on the logic of your code)
            }
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            error_log($e);
        }

        return $response;
    }

    private function createSignature($ts, $httpMethod, $url, $httpBody)
    {
        return hash_hmac('sha256', $ts . strtoupper($httpMethod) . $url . $httpBody, SUMSUB_SECRET_KEY);
    }

    public function addDocument($applicantId, $file)
        // https://developers.sumsub.com/api-reference/#adding-an-id-document
    {

        // $metadata = ['idDocType' => 'ID_CARD', 'country' => 'IND'];
        $metadata = ['idDocType' => 'PASSPORT', 'country' => 'IND'];

        $multipart = new MultipartStream([
            [
                "name" => "metadata",
                "contents" => json_encode($metadata)
            ],
            [
                'name' => 'content',
                'contents' => fopen($file, 'r')
            ],
        ]);

        $url = "/resources/applicants/" . $applicantId . "/info/idDoc";
        $request = new GuzzleHttp\Psr7\Request('POST', SUMSUB_TEST_BASE_URL . $url);
        $request = $request->withBody($multipart);

        return $this->sendHttpRequest($request, $url)->getHeader("X-Image-Id")[0];
    }


    public function applicantCheck($applicantId)
        // https://developers.sumsub.com/api-reference/#adding-an-id-document
    {
        $url = "/resources/applicants/" . $applicantId . "/status/pending?reason=webSDKrequest";
        $request = new GuzzleHttp\Psr7\Request('POST', SUMSUB_TEST_BASE_URL . $url);
        $request = $request->withHeader('Content-Type', 'application/json'); 

        $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody);
    }

    public function getApplicantDocStatus($applicantId)
        // https://developers.sumsub.com/api-reference/#getting-applicant-data
    {
        $url = "/resources/applicants/" . $applicantId . "/one";
        $request = new GuzzleHttp\Psr7\Request('GET', SUMSUB_TEST_BASE_URL . $url);

        $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody);
    }


    public function getApplicantStatus($applicantId)
        // https://developers.sumsub.com/api-reference/#getting-applicant-status-api
    {
        $url = "/resources/applicants/" . $applicantId . "/requiredIdDocsStatus";
        $request = new GuzzleHttp\Psr7\Request('GET', SUMSUB_TEST_BASE_URL . $url);

        return $responseBody = $this->sendHttpRequest($request, $url)->getBody();
        return json_decode($responseBody);
    }

    public function getAccessToken($externalUserId, $levelName)
        // https://developers.sumsub.com/api-reference/#access-tokens-for-sdks
    {
        $url = "/resources/accessTokens?userId=" . $externalUserId . "&levelName=" . $levelName;
        $request = new GuzzleHttp\Psr7\Request('POST', SUMSUB_TEST_BASE_URL . $url);

        return $this->sendHttpRequest($request, $url)->getBody();
    }

}

// The description of the flow can be found here: https://developers.sumsub.com/api-flow/#api-integration-phases

// Such actions are presented below:
// 1) Creating an applicant
// 2) Adding a document to the applicant
// 3) Getting applicant status
// 4) Getting access token

$externalUserId = uniqid();
$levelName = 'basic-kyc-level';

$testObject = new AppTokenGuzzlePhpExample();
/*
$applicantId = $testObject->createApplicant($externalUserId, $levelName);
echo "The applicant was successfully created: " . $applicantId . PHP_EOL;
echo '<br/>';

$file1 = __DIR__ . '/resources/images/m_pp1.jpg';
$imageId = $testObject->addDocument($applicantId, $file1);
echo "Identifier of the added document: " . $imageId . PHP_EOL; 

echo '<br/>';
$accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
echo "Access token (json string): " . $accessTokenStr;
echo '<br/>';

$applicantStatusCheck = $testObject->applicantCheck($applicantId);
echo "Applicant checked (json string): " . $applicantStatusCheck;
echo '<br/>';

$applicantStatusStr = $testObject->getApplicantStatus($applicantId);
echo "Applicant status (json string): " . $applicantStatusStr;


$applicantStatusStr = $testObject->getApplicantDocStatus($applicantId);
echo "Applicant document info (json string): " . $applicantStatusStr;

// /resources/applicants/{applicantId}/requiredIdDocsStatus
*/
 
// $applicantId = '642fdc1b307aa221ca63aa5e';
$applicantId = '644011f7fe460d7d09c845b5';

$applicantStatusStr = $testObject->getApplicantStatus($applicantId);
// echo "Applicant status (json string): " . $applicantStatusStr;

// $applicantStatusStr = $testObject->getApplicantDocStatus($applicantId);
// echo "Applicant status (json string): " ;
print_r( $applicantStatusStr );
 
// curl -X GET \
//   'https://api.sumsub.com/resources/applicants/5bb8cca10a975a624903cf65/requiredIdDocsStatus'
  
 
?>