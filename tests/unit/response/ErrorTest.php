<?php

namespace alkemann\jsonapi\tests\unit\response;

use alkemann\h2l\Message;
use alkemann\jsonapi\Controller;
use alkemann\jsonapi\response\Error;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    private function createHeaderMock(array $expectations): \Closure
    {
        $header = $this->getMockBuilder(\stdClass::class)->setMethods(['head'])->getMock();
        $method = $header->expects($this->exactly(2))->method('head');
        call_user_func_array([$method, 'withConsecutive'], $expectations);
        return function($s) use ($header) { $header->head($s); };
    }

    public function testNoContent()
    {
        $h = $this->createHeaderMock([['HTTP/1.1 403 Forbidden'], ['Content-Type: application/vnd.api+json']]);
        $e = new Error([], 403, ['header_func' => $h]);

        $result = $e->message();
        $this->assertInstanceOf(Message::class, $result);
        $this->assertNull($result->body());
        $this->assertEquals('', $e->render());
        $this->assertNull($result->body());
        $this->assertEquals(403, $result->code());
        $this->assertEquals(Controller::CONTENT_JSON_API, $result->header('Content-Type'));
    }

    public function testWithContent()
    {
        $h = $this->createHeaderMock([['HTTP/1.1 403 Forbidden'], ['Content-Type: application/vnd.api+json']]);
        $e = new Error([['status' => 403, 'code' => 'FORBIDDEN']],403, ['header_func' => $h]);
        $result = $e->message();
        $this->assertInstanceOf(Message::class, $result);
        $expected = json_encode([
            'errors' => [['status' => 403, 'code' => 'FORBIDDEN']],
            'jsonapi' => ['version' => '1.0']
        ]);
        $this->assertNull($result->body());
        $this->assertEquals($expected, $e->render());
        $this->assertEquals($expected, $e->message()->body());
        $this->assertEquals(403, $result->code());
        $this->assertEquals(Controller::CONTENT_JSON_API, $result->header('Content-Type'));
    }
}