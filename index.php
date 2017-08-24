<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: clovis
 * Date: 22/01/17
 * Time: 16:12
 */

$config = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

session_start();
$app = new \Slim\App($config);
$app->any('/', function (Request $request, Response $response) {

    // Réglage headers
    $headers = array();
    foreach ($request->getHeaders() as $key => $header)
    {
        $name = strtolower(str_replace("_", "-",str_replace("HTTP_", "", $key)));
        /*$names = explode("-", $name);
        $name = "";
        foreach ($names as $n)
        {
            $name.= ucfirst($n)."-";
        }
        $name = substr($name, 0, -1);
        */
        if($name == "host" || $name == "connection" || $name == "accept-encoding")
            continue;
        if(count($header) > 0)
            $headers[$name] = $header[0];
    }
    // Réglage cookies
    if(isset($_SESSION["cookies"]))
    {
        $str = "";
        foreach ($_SESSION["cookies"] as $key => $value)
        {
            $str .= $value.";";
        }
        $headers["cookie"] =  $str;
    }
    var_dump($headers);
    $target = Requests::request($_GET["url"], $headers, $request->getParsedBody(), $request->getMethod());
    //var_dump($target->headers->getValues("set-cookie"));

    // Récupération des cookies
    if($target->headers->offsetExists("set-cookie"))
    {
        if(isset($_SESSION["cookies"]) == false)
            $_SESSION["cookies"] = array();
        foreach($target->headers->getValues("set-cookie") as $cookie)
        {
            $name = explode("=", $cookie)[0];
            $_SESSION["cookies"][$name] = explode(";", $cookie)[0];
        }
    }

    // Réglage headers
    foreach ($target->headers->getAll() as $key => $value)
    {
        $response = $response->withAddedHeader($key, $value);
    }
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');

    // Réglage corps
    $response = $response->getBody()->write($target->body);

    return $response;
});
$app->run();

