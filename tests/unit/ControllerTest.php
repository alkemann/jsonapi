<?php

namespace alkemann\jsonapi\tests\unit;

use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\util\Chain;
use alkemann\h2l\util\Http;
use alkemann\jsonapi\Controller;
use alkemann\jsonapi\response\Error;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testMiddlewareCorrectGet()
    {
        $response = $this->getMockForAbstractClass(Response::class);

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['method', 'header', 'url'])
            ->getMock();
        $request->expects($this->once())
            ->method('url')
            ->willReturn('/api/v1/people');
        $request->expects($this->exactly(3))
            ->method('method')
            ->willReturn(Http::GET);
        $request->expects($this->once())
            ->method('header')
            ->with('Accept')
            ->willReturn(Controller::CONTENT_JSON_API);

        $chain = $this->getMockBuilder(Chain::class)
            ->setMethods(['next'])
            ->getMock();
        $chain->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response);

        /**
         * @var Request $request
         * @var Chain $chain
         * @var Response $response
         */

        $result = Controller::requestMiddleware($request, $chain);
        $this->assertSame($response, $result);
    }

    public function testMiddlewarePatchOverride()
    {
        $response = $this->getMockForAbstractClass(Response::class);

        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['method', 'header', 'url', 'withMethod'])
            ->getMock();
        $request->expects($this->once())
            ->method('url')
            ->willReturn('/api/v1/people');
        $request->expects($this->exactly(3))
            ->method('method')
            ->willReturn(Http::POST);
        $request->expects($this->exactly(2))
            ->method('header')
            ->withConsecutive(['Accept'], ['Content-Type'])
            ->willReturnOnConsecutiveCalls(Controller::CONTENT_JSON_API, Controller::CONTENT_JSON_API);

        $chain = $this->getMockBuilder(Chain::class)
            ->setMethods(['next'])
            ->getMock();
        $chain->expects($this->once())
            ->method('next')
            ->with($request)
            ->willReturn($response);

        /**
         * @var Request $request
         * @var Chain $chain
         * @var Response $response
         */

        $result = Controller::requestMiddleware($request, $chain);
        $this->assertEquals($response, $result);
    }

    public function testMiddlewareMissingAccept()
    {
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['method', 'header', 'url'])
            ->getMock();
        $request->expects($this->once())
            ->method('url')
            ->willReturn('/api/v1/people');
        $request->expects($this->never())->method('method');
        $request->expects($this->once())
            ->method('header')
            ->with('Accept')
            ->willReturn(Http::CONTENT_JSON);

        $chain = $this->getMockBuilder(Chain::class)
            ->setMethods(['next'])
            ->getMock();
        $chain->expects($this->never())
            ->method('next');

        /**
         * @var Request $request
         * @var Chain $chain
         * @var Response $response
         */

        $result = Controller::requestMiddleware($request, $chain);
        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(Http::CODE_NOT_ACCEPTABLE, $result->code());
    }

    public function testMiddlewareBadPostContent()
    {
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['method', 'header', 'url'])
            ->getMock();
        $request->expects($this->once())
            ->method('url')
            ->willReturn('/api/v1/people');
        $request->expects($this->exactly(2))
            ->method('method')
            ->willReturn(Http::POST);
        $request->expects($this->exactly(2))
            ->method('header')
            ->withConsecutive(['Accept'], ['Content-Type'])
            ->willReturnOnConsecutiveCalls(Controller::CONTENT_JSON_API, Http::CONTENT_JSON);

        $chain = $this->getMockBuilder(Chain::class)
            ->setMethods(['next'])
            ->getMock();
        $chain->expects($this->never())
            ->method('next');

        /**
         * @var Request $request
         * @var Chain $chain
         * @var Response $response
         */

        $result = Controller::requestMiddleware($request, $chain);
        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(Http::CODE_NOT_ACCEPTABLE, $result->code());
    }
}
