<?php
if ($json = json_decode(file_get_contents("php://input"), true)) {
    print_r($json);
    $data = $json;
    exit;
} else {
    if( !empty ($_POST )) {
        print_r($_POST);
        $data = $_POST;
    }
    exit;
}
echo 'No data available yet';