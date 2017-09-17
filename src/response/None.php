<?php

namespace alkemann\jsonapi\response;

use alkemann\h2l\Message;
use alkemann\h2l\util\Http;
use alkemann\h2l\Response;

/**
 * Class Error
 *
 * @package alkemann\jsonapi\response
 */
class None extends Response
{
    /**
     * @var array
     */
    protected $config;
    /**
     * @var Message
     */
    protected $message;

    public function __construct(int $http_code = Http::CODE_NO_CONTENT, array $config = [])
    {
        $this->config = $config;
        $headers['Content-Type'] = 'application/vnd.api+json';
        $this->message = (new Message)
            ->withCode($http_code)
            ->withHeaders($headers)
        ;
    }

    public function render(): string
    {
        $this->setHeaders();
        return '';
    }
}
