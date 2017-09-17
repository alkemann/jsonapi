<?php

namespace alkemann\jsonapi\response;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;
use alkemann\jsonapi\Controller;

/**
 * Class Result
 *
 * @package alkemann\jsonapi\response
 */
class Result extends Response
{
    /**
     * @var array|null
     */
    protected $data;
    /**
     * @var array
     */
    protected $meta;
    /**
     * @var array
     */
    protected $links;
    /**
     * @var array
     */
    protected $included;
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var Message
     */
    protected $message;

    public function __construct($data = [], int $http_code = Http::CODE_OK, array $config = [])
    {
        $this->config = $config;
        $headers['Content-Type'] = 'application/vnd.api+json';
        $this->message = (new Message)
            ->withCode($http_code)
            ->withHeaders($headers)
        ;
        $this->data = $data;
    }

    /**
     * Add Location header to response, as per http://jsonapi.org/format/#crud-creating-responses-201
     *
     * @param string $location
     * @return Result
     */
    public function withLocation(string $location): Result
    {
        $this->message = $this->message->withHeader('Location', $location);
        return $this;
    }

    /**
     * Add links meta data to response container, as per http://jsonapi.org/format/#document-links
     * @param array $links
     * @return Result
     */
    public function withLinks(array $links): Result
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Add meta data to response container, as per http://jsonapi.org/format/#document-meta
     *
     * @param array $meta
     * @return Result
     */
    public function withMeta(array $meta): Result
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Add included data to response container, as per http://jsonapi.org/format/#document-meta
     *
     * @param array $included
     * @return Result
     */
    public function withIncluded(array $included): Result
    {
        $this->included = $included;
        return $this;
    }

    /**
     * Enforce JsonAPI container, as per http://jsonapi.org/format/#document-structure
     */
    protected function setContainer()
    {
        $container = $this->setPayloadInContainer();
        if ($this->meta) {
            $container['meta'] = $this->meta;
        }
        if ($this->links) {
            $container['links'] = $this->links;
        }
        if ($this->included) {
            $container['included'] = $this->included;
        }

        $container['jsonapi'] = ['version' => Controller::VERSION];
        $this->message = $this->message->withBody(json_encode($container));
    }

    protected function setPayloadInContainer(): array
    {
        if ($this->data instanceof \Generator) {
            $data = array_values(iterator_to_array($this->data));
            $this->meta['count'] = sizeof($data);
        } else {
            $data = $this->data;
        }
        return compact('data');
    }

    /**
     * Set all headers and returns the output string
     *
     * @return string
     */
    public function render(): string
    {
        $this->setHeaders();
        $this->setContainer();
        return $this->message()->body() ?? '';
    }
}
