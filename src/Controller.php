<?php

namespace alkemann\jsonapi;

use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\Router;
use alkemann\h2l\util\Chain;
use alkemann\h2l\util\Http;
use alkemann\jsonapi\exceptions\InvalidRequestContainer;
use alkemann\jsonapi\exceptions\JsonApiError;
use alkemann\jsonapi\response\Error;

/**
 * Class Controller
 *
 * Extend this class to define your API routes. See example folder for usage examples
 *
 * Implements http://jsonapi.org/format/1.0/ - MUSTs only
 *
 * @package alkemann\jsonapi
 */
class Controller
{
    const CONTENT_JSON_API = 'application/vnd.api+json';
    const VERSION = '1.0';

    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var string
     */
    private $delimiter = '%';
    /**
     * @var array
     */
    protected static $routes = [];

    public function __construct(array $config = [])
    {
        foreach (['delimiter'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }
        $this->config = $config;
    }

    /**
     * @param string $class name of class that uses the Model and Entity traits
     * @param Request $request
     * @return object instance of $class
     * @throws InvalidRequestContainer if the request content is not following JsonAPI spec
     */
    public function populateModelFromRequest(string $class, Request $request)
    {
        $data = $this->getValidatedRequestDataForModel($class, $request);
        return new $class($data);
    }

    /**
     * @param string $class
     * @param Request $request
     * @return array
     * @throws InvalidRequestContainer if the request content is not following JsonAPI spec
     */
    public function getValidatedRequestDataForModel(string $class, Request $request): array
    {
        $data = json_decode($request->body(), true);
        if (!isset($data['data'])
            || !isset($data['data']['type']) || !is_string($data['data']['type'])
            || !isset($data['data']['attributes']) || !is_array($data['data']['attributes'])
        ) {
            throw new InvalidRequestContainer("Invalid container request body", 'INVALID_CONTAINER');
        }
        $type = strtolower((new \ReflectionClass($class))->getShortName());
        if ($data['data']['type'] !== $type) {
            throw new InvalidRequestContainer("Invalid type for $class in request body", 'INVALID_CONTAINER');
        }
        return $data['data']['attributes'];
    }

    /**
     * Call this to add all routes specified in static::$routes to Router
     */
    public function addRoutes(): void
    {
        $router = $this->config['router'] ?? Router::class;
        $router::$DELIMITER = $this->delimiter;
        foreach (static::$routes as [$url, $func, $method]) {
            $router::add($url, [$this, $func], $method);
        }
    }

    /**
     * This middleware should be added to the alkemann\h2l\Dispatch to enforce JsonAPI spec requests
     *
     * It will also do a try/catch around the Routed action to catch all JsonAPI spec errors and
     * send an appropriate error response.
     *
     * You should add a similar middleware for any other exception your app may throw, to catch them
     * and return an error response
     *
     * @param Request $request
     * @param Chain $chain
     * @return Response|null
     */
    public static function requestMiddleware(Request $request, Chain $chain): ?Response
    {
        $url = $request->url();

        if (static::isUrlAPIRequest($url)) {
            if ($request->header('Accept') !== self::CONTENT_JSON_API) {
                return new Error(Http::CODE_NOT_ACCEPTABLE);
            }

            if (($request->method() === Http::PATCH || $request->method() === Http::POST) &&
                $request->header('Content-Type') !== self::CONTENT_JSON_API) {
                return new Error(Http::CODE_NOT_ACCEPTABLE);
            }

            if ($request->method() === Http::PATCH && $request->header('X-HTTP-Method-Override') === 'PATCH') {
                $request = $request->withMethod(Http::POST);
            }
        }

        try {
            $response = $chain->next($request);
        } catch (JsonApiError $e) {
            return new Error($e->http_code, [[
                'status' => $e->http_code,
                'code' => $e->getCode(),
                'detail' => $e->getMessage()
            ]]);
        }
        return $response;
    }

    /**
     * Override to have a different rule about which urls are API requests that must follow JsonAPI spec
     *
     * @param string $url
     * @return bool
     */
    public static function isUrlAPIRequest(string $url): bool
    {
        return substr($url, 0, 4) === '/api';
    }
}
