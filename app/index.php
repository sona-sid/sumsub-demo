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

    public function addDocument($applicantId)
        // https://developers.sumsub.com/api-reference/#adding-an-id-document
    {
        $metadata = ['idDocType' => 'PASSPORT', 'country' => 'ARE'];
        $file = __DIR__ . '/resources/images/sample-pp1.png';

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
$imageId = $testObject->addDocument($applicantId);
echo "Identifier of the added document: " . $imageId . PHP_EOL;
echo '<br/>';

$applicantStatusStr = $testObject->getApplicantStatus($applicantId);
echo "Applicant status (json string): " . $applicantStatusStr;

echo '<br/>';
$accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
echo "Access token (json string): " . $accessTokenStr;
echo '<br/>';

*/

$accessTokenStr = $testObject->getAccessToken($externalUserId, $levelName);
$response_token = json_decode( $accessTokenStr, true );
// print_r( $response_token );
$token_to_demo  = $response_token['token'];
$userId_ext     = $response_token['userId'];

// $userId_ext     = '64403f0e742bc';  // sandbox
// $userId_ext     = '6440052bd3c19';  //prod
/*
// $applicantId = '642fdc1b307aa221ca63aa5e';
$applicantId = '643fe57ae7d2a84d471f0e46';

$applicantStatusStr = $testObject->getApplicantStatus($applicantId);
echo "Applicant status (json string): " . $applicantStatusStr;
 
// curl -X GET \
//   'https://api.sumsub.com/resources/applicants/5bb8cca10a975a624903cf65/requiredIdDocsStatus'
*/
?>

<html>
 <head>
  <title>Sumsub Demo</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
        box-sizing: border-box;
        }
        /* Create two equal columns that floats next to each other */
        .column {
        float: left; 
        padding: 10px;
        height: 300px; /* Should be removed. Only for demonstration */
        }

        /* Clear floats after the columns */
        .row:after {
        content: "";
        display: table;
        clear: both;
        } 
        .header .message { margin-top: 20px}
        .response-block { padding: 30px;}
        .response-body { margin-bottom: 20px}
        .field {  padding: 7px 3px; }
        .field .val {  
            padding: 7px 3px; 
            color: green;
        }
        a.btn {
            cursor: pointer;
            background-image: none;
            border: 1px solid transparent;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            border-radius: 4px;
            background-color: #e9e9e9;
            color: #323232;
        }
    </style>
 </head>
 <body>
	<script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>

    <div class="container-fluid">
        <div class="header ">
            <h2 class="title">Sumsub Document Verification Demo</h2>
            <div class="message alert alert-success">
                <strong> <?php echo "Generated Access token.  " . $accessTokenStr; ?> </strong> 
            </div> 
        </div>
        <div class="row">
            <div class="column col-md-4 response-block">
                <div class="response-body" style="display:none;">
                    <div class="api_error"></div>
                    <div class="field firstName">First Name: <span class="val"></span> </div>
                    <div class="field lastName">Last Name: <span class="val"></span></div>
                    <div class="field country">Country: <span class="val"></span></div>
                    <div class="field dob">DOB: <span class="val"></span></div>
                    <div class="field idDocType">Document Type: <span class="val"></span></div>  
                    <div class="field number">Number: <span class="val"></span></div>  
                    <div class="field validUntil">Valid Until: <span class="val"></span></div>
                </div> 

                <button type="button" class="btn-group-lg btn" id="getReviewInfo" onclick="getDocdata('<?php echo $userId_ext;?>');">View Information</button> 
                <a href="printData.php?UserId=<?php echo $userId_ext;?>" target="_blank" class="btn" id="getReviewInfoFull" style="display:none;" >View Full Report</a>
                <!-- <button type="button" class="btn-group-lg btn" onclick="location.href = 'printData.php?UserId=<?php echo $userId_ext;?>';">View Full Report</button>  -->

            </div>

            <div class="column col-md-8">     
                <div id="sumsub-websdk-container"></div>
            </div>
        </div> 
    </div>

</body>
</html>
<script>

    function launchWebSdk(accessToken, applicantEmail, applicantPhone) {
        let snsWebSdkInstance = snsWebSdk.init(
                accessToken,
                () => this.getNewAccessToken()
            )
            .withConf({
                lang: 'en',
                email: applicantEmail,
                phone: applicantPhone,
                i18n: {"document":{"subTitles":{"IDENTITY": "Upload a document that proves your identity"}}},
                onMessage: (type, payload) => {
                    console.log('WebSDK onMessage 5', type, payload)
                },
                uiConf: {
                    customCssStr: ":root {\n  --black: #000000;\n   --grey: #F5F5F5;\n  --grey-darker: #B2B2B2;\n  --border-color: #DBDBDB;\n}\n\np {\n  color: var(--black);\n  font-size: 16px;\n  line-height: 24px;\n}\n\nsection {\n  margin: 40px auto;\n}\n\ninput {\n  color: var(--black);\n  font-weight: 600;\n  outline: none;\n}\n\nsection.content {\n  background-color: var(--grey);\n  color: var(--black);\n  padding: 40px 40px 16px;\n  box-shadow: none;\n  border-radius: 6px;\n}\n\nbutton.submit,\nbutton.back {\n  text-transform: capitalize;\n  border-radius: 6px;\n  height: 48px;\n  padding: 0 30px;\n  font-size: 16px;\n  background-image: none !important;\n  transform: none !important;\n  box-shadow: none !important;\n  transition: all 0.2s linear;\n}\n\nbutton.submit {\n  min-width: 132px;\n  background: none;\n  background-color: var(--black);\n}\n\n.round-icon {\n  background-color: var(--black) !important;\n  background-image: none !important;\n}"
                },
                onError: (error) => {
                    console.error('WebSDK onError', error)
                },
            })
            .withOptions({ addViewportTag: false, adaptIframeHeight: true})
            .on('idCheck.stepCompleted', (payload) => {
                console.log('stepCompleted', payload)
            })
            .on('idCheck.onError', (error) => {
                console.log('onError', payload)
            })
            .onMessage((type, payload) => {
                console.log('onMessage', type, payload)
            })
            .build();
        snsWebSdkInstance.launch('#sumsub-websdk-container')
    }

    function getNewAccessToken() {
    return Promise.resolve($NEW_ACCESS_TOKEN)
    }

    var delayInMilliseconds = 1000; //5 second
    setTimeout(function() {
        const php_access_token = '<?php echo $token_to_demo; ?>';
        // alert(php_access_token);
        //your code to be executed after 1 second
        launchWebSdk(php_access_token)  
        // launchWebSdk('_act-sbx-6c47ec8b-0f1d-4970-964d-e911848ca0a7')
    }, delayInMilliseconds);

    setTimeout(function() {
        $(".response-body").show();
        getDocdata();
        // setInterval(function(){getDocdata();}, 5);
    }, 20000);


    function getDocdata(userId) {
        $.ajax({
            url: 'extractDoc.php',
            method: 'POST',
            data:{ExtuserId:userId },
            // data:{applicantId:userId },
            // data:{applicantId:'6440052c19c56550e338c090' },
            dataType: 'JSON',
            success: function(response){
                var len = response.length;
                console.log( response);
                var result_html = ''

                /* var _jsonString = "";
                var obj = jQuery.parseJSON(response);
                $.each(obj, function(key,value) {
                    alert(key);
                });  */

                // var obj = jQuery.parseJSON(response);
                $.each(response , function(index, val) { 
                    if ( $('.'+index+' .val').text() )
                    $('.'+index+' .val').append( val );
                });

                    if(typeof response['idDocs'] === 'undefined') {
                        // does not exist
                    } else {
                        // exists 
                        console.log( 'exists');
                        console.log( response['idDocs'][0] );
                        $.each( response['idDocs'][0] , function( docindex, docval) { 
                            if ( $('.'+docindex+' .val').text() == docval ) { 

                            } else {
                                $('.'+docindex+' .val').text( docval );
                            }

                        });
                    }

                $(".response-body").show();
                $("#getReviewInfoFull").show();

               /*  var _jsonString = "";
                for(var key in response){
                    _jsonString +="key "+key+" value "+response[key]+ '</br>';
                }
                alert(_jsonString);
                $(".response-body").append(_jsonString)
 */
                // $('.response-body').html( response );
               /*  for(var i=0; i<len; i++){
                    var id = response[i].id;
                    var username = response[i].username;
                    var name = response[i].name;
                    var email = response[i].email;

                    var tr_str = "<tr>" +
                        "<td align='center'>" + (i+1) + "</td>" +
                        "<td align='center'>" + username + "</td>" +
                        "<td align='center'>" + name + "</td>" +
                        "<td align='center'>" + email + "</td>" +
                        "</tr>";

                    $("#userTable tbody").append(tr_str);
                } */

            } 
        });
        

        if ( $('iframe').contents().find('.review-complete').length ) {
            console.log('get results');
        } else {
            console.log('clicked button');
        }
    }
    // $('#frame').contents().find('a[href=check]').length
</script>