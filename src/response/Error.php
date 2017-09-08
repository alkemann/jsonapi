<?php

namespace alkemann\jsonapi\response;

use alkemann\h2l\Message;
use alkemann\h2l\util\Http;

/**
 * Class Error
 *
 * @package alkemann\jsonapi\response
 */
class Error extends Result
{
    public function __construct(array $errors = [], int $http_code = Http::CODE_BAD_REQUEST, array $config = [])
    {
        parent::__construct($errors, $http_code, $config);
    }


    protected function setPayloadInContainer(): array
    {
        return ['errors' => $this->data];
    }
}
