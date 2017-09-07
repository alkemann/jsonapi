<?php

namespace alkemann\jsonapi\response;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;

/**
 * Class Error
 *
 * @package alkemann\jsonapi\response
 */
class Error extends Response
{
    public function __construct(int $http_code, array $errors = [], array $headers = [])
    {
        $headers['Content-Type'] = 'application/vnd.api+json';
        $this->message = (new Message)
            ->withCode($http_code)
            ->withHeaders($headers)
        ;

        if ($errors) {
            $this->message = $this->message->withBody(json_encode(['errors' => $errors]));
        }
    }

    public function render(): string
    {
        $this->setHeaders();
        return $this->message->body() ?? '';
    }
}
