<?php

namespace alkemann\jsonapi\exceptions;

use alkemann\h2l\util\Http;
use Throwable;

/**
 * Class InvalidRequestContainer
 *
 * @package alkemann\jsonapi\exceptions
 */
class InvalidRequestContainer extends JsonApiError
{
    public $http_code = Http::CODE_BAD_REQUEST;

    public function __construct(string $message, string $code = 'INVALID_CONTAINER', Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
