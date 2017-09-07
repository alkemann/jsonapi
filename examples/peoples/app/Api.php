<?php

namespace app;

use alkemann\h2l\{ Request, Response, Router, Log };
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\util\Http;
use alkemann\jsonapi\exceptions\InvalidRequestContainer;
use alkemann\jsonapi\response\{Result, Error};
use alkemann\jsonapi\Controller;
use app\People;

class Api extends Controller
{
    static $routes = [
        // Url                              function            request method
        ['%^/api/v1/people/(?<id>\d+)%',    'person',           Http::GET],
        ['%^/api/v1/people/(?<id>\d+)%',    'update_person',    Http::PATCH],
        ['/api/v1/people',                  'new_person',       Http::POST],
        ['/api/v1/people',                  'people',           Http::GET],
        ['/api/v1/version',                 'version',          Http::GET],
    ];

    public function new_person(Request $request): ?Response
    {
        $person = $this->populateModelFromRequest(People::class, $request);
        $person->save();
        return (new Result($person, Http::CODE_CREATED))
            ->withLocation($request->fullUrl('/api/v1/people/' . $person->id))
            ;
    }

    public function update_person(Request $request): ?Response
    {
        $person = People::get($request->param('id'));
        if (!$person) {
            return new Error(Http::CODE_NOT_FOUND);
        }
        $data = $this->getValidatedRequestDataForModel(People::class, $request);
        $person->save($data);
        return new Result($person);
    }

    public function person(Request $r): ?Response
    {
        $person = People::get($r->param('id'));
        return new Result($person);
    }

    public function people(Request $r): ?Response
    {
        $data = People::find();
        return (new Result($data))
            ->withLinks(['self' => $r->fullUrl()])
            ;
    }

    public static function version(Request $r): Response
    {
        return new Result(['v' => '1.0']);
    }
}
