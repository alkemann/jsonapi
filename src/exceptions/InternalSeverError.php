<?php

namespace alkemann\jsonapi\exceptions;

use alkemann\h2l\util\Http;
use Throwable;

/**
 * Class InternalSeverError
 *
 * @package alkemann\jsonapi\exceptions
 */
class InternalSeverError extends JsonApiError
{
    public $http_code = Http::CODE_INTERNAL_SERVER_ERROR;

    public function __construct(string $message, string $code = 'SERVER_ERROR', Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
