<?php

namespace alkemann\jsonapi\exceptions;

use alkemann\h2l\util\Http;

/**
 * Class JsonApiError
 *
 * @package alkemann\jsonapi\exceptions
 */
class JsonApiError extends \Error
{
    public $http_code = Http::CODE_BAD_REQUEST;
}
