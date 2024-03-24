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



function callApi($url, $data_string, $verb="GET", $headers, $json=true, $token=false) {

    $curl = curl_init();

    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_POST => false,
        CURLOPT_CUSTOMREQUEST => $verb,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ];

    if($verb !== "GET") {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $data_string;
    }
    if($json === false && $token === false){// cas ou on veut afficher un pdf (mais pas pour l'appel WS Token)
        $opts[CURLOPT_HEADER] = true;
    }

    curl_setopt_array($curl, $opts);
    $executionStartTime = microtime(true);
    $response = curl_exec($curl);
    $executionEndTime = microtime(true);
    $seconds = $executionEndTime - $executionStartTime;
    if($json === true || $token === true){
        $response = json_decode($response);
    } else {
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        transferHeader($headers);
        $body = substr($response, $header_size);
        /*
        ## Alternative 1 : contenu transformé en data-url encodé en base64, accessible depuis un lien.

        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        echo '<a href="data:'.$contentType.';base64,'.base64_encode($response).'" download="download.pdf">Download</a>';

        ## Alternative 2 : enregistrement du contenu dans un répertoire du serveur

        file_put_contents('download.pdf', $response);

        */
    }
    curl_close($curl);
    return $response;
}


function callApiGet($url, $token, $datas = null) {
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
    $headers = array(
        'Authorization: BEARER '.$token,
        'Content-type: text/html; charset=utf-8'
    );

    $url_with_datas = $url;

    if($datas !== null){
        $url_with_datas .= '?'.http_build_query($datas);
    }

    return callApi($urls['PROD']["WS"].$url_with_datas, "", "GET", $headers);
}


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

    $response = callApi($urls['PROD']["PORTAL"], $data_string, "POST", $headers, $json, true);



    $params = [
        "maxResults" => 1, // champs obligatoire compris entre 1 et 100
        "sortOrder" => 1, // permet de trier par ordre croissant (<=> 1) ou d�croissant (<=> -1) sur le sortField
    ];

    $email = "gregory.vassal1@gmail.com";
    $filters = [ "email" => [
        "value" => $email,
        "matchMode" => "equals"
    ]
    ];

    $params["filters"] = json_encode($filters);

    print "Recherche du client ayant l'email = ".$email."<br>";
    $response = callApiGet("/editeur/187/client", $response->access_token, $params);
    return $response;


    //TRAITEMENT DES CALL API
    $client = callApiGet("/editeur/187/client/11574/", $response->access_token, $params);

    $response = callApiGet("/editeur/187/client/11574/", $response->access_token, $params);


    $response = \Illuminate\Support\Facades\Http::post('https://www.sinemensuel.com/wp-json/uap/v2/uap-185379-185380', [
        'email' => $response->value->email,
        'aboweb_num' => $response->value->codeClient,
    ]);



    return $response;

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

    $base_url = 'https://www.sinemensuel.com/wp-json/wp/v2/users';
    $per_page = 100; // Nombre maximal d'utilisateurs par page
    $page = 1;
    $all_users = [];
    $continue = true;

    while ($continue) {
        $response = \Illuminate\Support\Facades\Http::get($base_url, [
            'per_page' => $per_page,
            'page' => $page,
        ]);

        if ($response->successful()) {
            $users = $response->json();

            // Ajoute les utilisateurs récupérés à notre tableau total
            $all_users = array_merge($all_users, $users);

            // Vérifie si on a récupéré le nombre maximal d'utilisateurs, sinon c'est la dernière page
            $continue = count($users) === $per_page;
        } else {
            // En cas d'échec de la requête (par exemple, une erreur 404 ou 403), arrête la boucle
            $continue = false;
            // Vous pouvez gérer les erreurs plus finement ici en fonction de vos besoins
            return "Échec de la récupération des utilisateurs à la page $page. Code d'état HTTP: " . $response->status();
        }

        $page++;
    }

    // Ici, vous avez $all_users contenant tous les utilisateurs récupérés
    // Vous pouvez maintenant traiter ou retourner ces données comme nécessaire
    return $all_users;


    $per_page = 100; // WordPress limite généralement les résultats à 100 par page au maximum

// Initialisation
    $page = 1;
    $all_users = [];

    do {
        $url = $base_url . '?per_page=' . $per_page . '&page=' . $page;

        // Initialise la session cURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        // Exécute la requête
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        return $status;
        if ($status == 200) {
            $users = json_decode($body);
            $all_users = array_merge($all_users, $users);
        } else {
            // Gérer les erreurs, par exemple en affichant un message d'erreur
            break; // Sort de la boucle si une erreur survient
        }

        // Ferme la session cURL
        curl_close($curl);

        // Incrémente la page pour la prochaine itération
        $page++;

        // Vérifie si la réponse contient moins d'utilisateurs que le maximum par page, ce qui signifie que c'est la dernière page
    } while (!empty($users) && sizeof($users) == $per_page);

// À ce stade, $all_users contient tous les utilisateurs récupérés
    foreach ($all_users as $user) {
        return 'Email: ' . $user->email . "<br>\n";
    }
    return "o";
    return getToken("abonnement@sinemensuel.com","qaCd9Mkep.*B4yK");
});
