<?php
$root_path = realpath(dirname(dirname(__FILE__)));
require_once $root_path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use alkemann\h2l\{
    Connections, Dispatch, exceptions\InvalidUrl, Request, Response, util\Chain
};
use alkemann\jsonapi\{ Controller, response\Error };
use app\Api;

Connections::add('default', function() {
    return new PDO(['type' => 'mysql', 'host' => 'mysql', 'db' => 'gzzgle', 'user' =>'gzzgle', 'pass' => 'gzzgle']);
});

$api = new Api();
$api->addRoutes();

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();

$dispatch->registerMiddle([Controller::class, 'requestMiddleware']);
$dispatch->registerMiddle(function(Request $request, Chain $chain): Response {
    try {
        $response = $chain->next($request);
    } catch (InvalidUrl $e) {
        return new Error(404, [['status' => 404]]);
    } catch (\Throwable $e) {
        return new Error(500);
    }
    return $response;
});

$response = $dispatch->response();
if ($response) {
    echo $response->render();
}
