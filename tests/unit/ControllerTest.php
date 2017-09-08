<?php

namespace alkemann\jsonapi\tests\unit;

use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\response\Json;
use alkemann\jsonapi\exceptions\InvalidRequestContainer;
use alkemann\jsonapi\tests\mocks\Posts;
use alkemann\jsonapi\tests\mocks\Router;
use alkemann\jsonapi\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndRoutes()
    {
        $config = [
            'delimiter' => ':',
            'router' => Router::class
        ];
        $c = new class($config) extends Controller {
            static $routes = [
                ['/v1/posts', 'posts', 'GET'],
                ['%/v1/posts/(?<id>\d+)%', 'post', 'GET']
            ];
            public function posts(Request $r): Response {
                return new Json();
            }
            public function post(Request $r): Response {
                return new Json();
            }
        };

        $c->addRoutes();

        $this->assertEquals(':', Router::$DELIMITER);
        $this->assertEquals([
            ['/v1/posts', 'posts', 'GET'],
            ['%/v1/posts/(?<id>\d+)%', 'post', 'GET']
        ], $c::$routes);
    }

    public function testGetAndValidateRequestData()
    {
        $data = [
            'title' => 'Testing',
            'status' => 'NEW'
        ];
        $request_body = json_encode([
            'data' => [
                'type' => 'posts',
                'attributes' => $data
            ],
            'jsonapi' => ['version' => '1.0']
        ]);

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['body', 'method', 'url'])
            ->getMock();
        $request->expects($this->once())->method('body')
            ->willReturn($request_body);
        $c = new class extends Controller {};

        $result = $c->getValidatedRequestDataForModel(Posts::class, $request);
        $this->assertEquals($data, $result);
    }

    public function testPopulateModelFromRequest()
    {
        $data = [
            'title' => 'Testing',
            'status' => 'NEW'
        ];
        $request_body = json_encode([
            'data' => [
                'type' => 'posts',
                'attributes' => $data
            ],
            'jsonapi' => ['version' => '1.0']
        ]);

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['body', 'method', 'url'])
            ->getMock();
        $request->expects($this->once())->method('body')
            ->willReturn($request_body);
        $c = new class extends Controller {};

        $result = $c->populateModelFromRequest(Posts::class, $request);
        $this->assertInstanceOf(Posts::class, $result);
        $this->assertEquals($data, $result->data());
    }

    public function testBadPostRequestContainer()
    {
        $request_body = json_encode(['data' => ['title' => 'bad']]);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['body', 'method', 'url'])
            ->getMock();
        $request->expects($this->once())
            ->method('body')
            ->willReturn($request_body);

        $c = new class extends Controller {};

        $this->expectException(InvalidRequestContainer::class);
        $this->expectExceptionCode('INVALID_CONTAINER');
        $c->getValidatedRequestDataForModel(Posts::class, $request);
    }

    public function testPostBodyHasBadType()
    {
        $request_body = json_encode([
            'data' => [
                'type' => 'author',
                'attributes' => ['name' => 'John']
            ],
            'jsonapi' => ['version' => '1.0']
        ]);

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['body', 'method', 'url'])
            ->getMock();
        $request->expects($this->once())
            ->method('body')
            ->willReturn($request_body);

        $c = new class extends Controller {};

        $this->expectException(InvalidRequestContainer::class);
        $this->expectExceptionCode('INVALID_TYPE');
        $c->getValidatedRequestDataForModel(Posts::class, $request);
    }
}
