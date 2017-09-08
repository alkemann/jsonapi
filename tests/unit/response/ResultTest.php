<?php

namespace alkemann\jsonapi\tests\unit\response;

use alkemann\h2l\Message;
use alkemann\h2l\util\Http;
use alkemann\jsonapi\Controller;
use alkemann\jsonapi\response\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    private function createHeaderMock(array $expectations): \Closure
    {
        $header = $this->getMockBuilder(\stdClass::class)->setMethods(['head'])->getMock();
        $method = $header->expects($this->exactly(2))->method('head');
        call_user_func_array([$method, 'withConsecutive'], $expectations);
        return function($s) use ($header) { $header->head($s); };
    }

    public function testContent()
    {
        $h = $this->createHeaderMock([['HTTP/1.1 201 Created'], ['Content-Type: application/vnd.api+json']]);
        $result = new Result(['id' => 12], Http::CODE_CREATED, ['header_func' => $h]);

        $message = $result->message();
        $this->assertInstanceOf(Message::class, $message);
        $expected = json_encode(['data' => ['id' => 12], 'jsonapi' => ['version' => Controller::VERSION]]);
        $this->assertNull($message->body());
        $this->assertEquals($expected, $result->render());
        $this->assertNull($message->body());
        $this->assertEquals($expected, $result->message()->body());
        $this->assertEquals(201, $message->code());
        $this->assertEquals(Controller::CONTENT_JSON_API, $message->header('Content-Type'));
    }

    public function testWithExtras()
    {
        $header = $this->getMockBuilder(\stdClass::class)->setMethods(['head'])->getMock();
        $header->expects($this->exactly(2))
            ->method('head')
            ->withConsecutive(
                ['Content-Type: application/vnd.api+json'],
                ['Location: http://example.com/posts/1']
            );
        $h = function($s) use ($header) { $header->head($s); };
        $data = ['type' => 'posts', 'id' => 12, 'attributes' => ['title' => 'Winning']];
        $result = new Result($data, 200, ['header_func' => $h]);
        $result->withLocation('http://example.com/posts/1');
        $result->withLinks(['self' => 'http://example.com/posts/1']);
        $result->withMeta(['self' => 'http://example.com/posts/1']);
        $result->withIncluded(['author' => ['not really correct format']]);

        $message = $result->message();
        $this->assertInstanceOf(Message::class, $message);
        $expected = json_encode([
            'data' => $data,
            'meta' => ['self' => 'http://example.com/posts/1'],
            'links' => ['self' => 'http://example.com/posts/1'],
            'included' => ['author' => ['not really correct format']],
            'jsonapi' => ['version' => '1.0']
        ]);
        $this->assertNull($message->body());
        $this->assertEquals($expected, $result->render());
        $this->assertNull($message->body());
        $this->assertEquals($expected, $result->message()->body());

        $this->assertEquals(200, $message->code());
        $this->assertEquals(Controller::CONTENT_JSON_API, $message->header('Content-Type'));
    }

    public function testGeneratorData()
    {
        $data = $data = [
            ['type' => 'posts', 'id' => '12'],
            ['type' => 'posts', 'id' => '13'],
            ['type' => 'posts', 'id' => '14'],
        ];
        $generator = function() use ($data) {
            foreach ($data as $row) yield $row;
        };

        $result = new Result($generator(), 200, ['header_func' => function() {}]);

        $expected = json_encode([
            'data' => $data,
            'meta' => ['count' => 3],
            'jsonapi' => ['version' => '1.0']
        ]);
        $this->assertEquals($expected, $result->render());
    }
}