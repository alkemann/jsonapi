<?php

namespace alkemann\jsonapi\tests\unit;

use alkemann\jsonapi\exceptions\InternalSeverError;
use alkemann\jsonapi\Model;
use alkemann\jsonapi\tests\mocks\Posts;


class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $data = ['id' => 12, 'title' => 'Winning', 'status' => 'ACTIVE'];
        $m = new Posts($data);

        $expected = [
            'type' => 'posts',
            'id' => 12,
            'attributes' => [
                'title' => 'Winning',
                'status' => 'ACTIVE'
            ]
        ];
        $result = $m->jsonSerialize();
        $this->assertEquals($expected, $result);
    }

    public function testSaveFail()
    {
        $this->expectException(InternalSeverError::class);
        $data = ['id' => 12, 'title' => 'Winning', 'status' => 'ACTIVE'];
        $m = new class($data) extends Model {
            public function saveModel(array $data = [], array $options = []): bool {
                return false;
            }
        };
        $m->save($data);
    }

    public function testSave()
    {
        $data = ['id' => 12, 'title' => 'Winning', 'status' => 'ACTIVE'];
        $m = new class($data) extends Model {
            public function saveModel(array $data = [], array $options = []): bool {
                return true;
            }
        };
        $this->assertTrue($m->save($data));
    }
}
