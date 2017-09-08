<?php
$root_path = realpath(dirname(dirname(__FILE__)));
require_once $root_path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use alkemann\h2l\{ Environment, Connections, Dispatch, exceptions\InvalidUrl, Request, Response, util\Chain, data\PDO };
use alkemann\jsonapi\{ Controller, response\Error };
use app\Api;

Environment::set(['content_path' => $root_path]); // Pending fix of #26 of alkemann/h2l

Connections::add('default', function() {
    return new PDO(['type' => 'mysql', 'host' => '127.0.0.1', 'db' => 'test', 'user' =>'test', 'pass' => 'test']);
});

$api = new Api();
$api->addRoutes();

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$routed = $dispatch->setRouteFromRouter();

$dispatch->registerMiddle([Controller::class, 'requestMiddleware']);
$dispatch->registerMiddle(function(Request $request, Chain $chain): Response {
    try {
        $response = $chain->next($request);
    } catch (InvalidUrl $e) {
        return new Error([
            ['status' => 404, 'code' => $e->getCode(), 'detail' => $e->getMessage()]
        ], 404);
    } catch (\Throwable $e) {
        return new Error([
            ['status' => 500, 'code' => $e->getCode(), 'detail' => $e->getMessage()]
        ], 500);
    }
    return $response;
});

$response = $dispatch->response();
if ($response) {
    echo $response->render();
}
