<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



function getToken($username, $password, $json=true) {
    $urls = [
        "PROD" => [
            "PORTAL" => "https://portal.mahalo-app.io/oauth/token",
            "WS" => "https://api.mahalo-app.io/aboweb"
        ],
        "PREPROD" => [
            "PORTAL" => "https://portal-preprod.mahalo-app.io/oauth/token",
            "WS" => "https://api-preprod.mahalo-app.io/aboweb"
        ],
        "RECETTE" => [
            "PORTAL" => "https://portal-recette.mahalo-app.io/oauth/token",
            "WS" => "https://api-recette.mahalo-app.io/aboweb"
        ],
        "LOCAL" => [
            "PORTAL" => "https://localhost:8443/aboweb-portal/oauth/token",
            "WS" => "https://localhost:8443/aboweb-ws"
        ]
    ];
    if($json === true){ // on affiche que dans le cas ou on veut du json
        print "<div><pre>Recuperation du token</pre></div>";
    }

    $params = [
        'grant_type' => 'password',
        'username' => $username,
        'password' => $password,
    ];

    $data_string = http_build_query($params);
    $headers = array(
        'Authorization: Basic YWJvd2ViOg==',
        'Content-Length: ' . strlen($data_string),
        'Content-Type: application/x-www-form-urlencoded'
    );

    $response = callApi($urls["PORTAL"], $data_string, "POST", $headers, $json, true);
    if($json === true){
        print "<div><pre>TOKEN API : ".$response->access_token."</pre></div>";
    }
    return $response->access_token;

    /*$curl = curl_init();

    $params = [
        'grant_type' => 'password',
        'username' => $username,
        'password' => $password,
    ];

    $data_string = http_build_query($params);
    //print_r($data_string);
    //print_r($urls[TARGET]["PORTAL"]);

    $opts = [
        CURLOPT_URL => $urls[TARGET]["PORTAL"],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic YWJvd2ViOg==',
            'Content-Length: ' . strlen($data_string),
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ];

    curl_setopt_array($curl, $opts);
    // curl_setopt($curl, CURLOPT_VERBOSE, 1);
    // curl_setopt($curl, CURLOPT_HEADER, 1);

    $response = curl_exec($curl);
    curl_close($curl);
    // print_r($response);
    $response = json_decode($response);

    print "TOKEN API : ".$response->access_token."<br><br>";
    return $response->access_token;*/
}


Route::get('/', function () {
    return getToken("abonnement@sinemensuel.com","qaCd9Mkep.*B4yK");
});
