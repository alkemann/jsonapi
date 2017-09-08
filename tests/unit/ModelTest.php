<?php
/**
 * Created by PhpStorm.
 * User: alek
 * Date: 08/09/2017
 * Time: 16:24
 */

namespace alkemann\jsonapi\tests\unit;

use alkemann\jsonapi\tests\mocks\Posts;

class ModelTest extends \PHPUnit_Framework_TestCase
{

    public function testSerialize()
    {
        $data = ['id' => 12, 'title' => 'Winning', 'status' => 'ACTIVE'];
        $m = new Posts($data);

        $expected = [
            'data' => [
                'type' => 'posts',
                'id' => 12,
                'attributes' => [
                    'title' => 'Winning',
                    'status' => 'ACTIVE'
                ]
            ]
        ];
        $result = $m->jsonSerialize();
        $this->assertEquals($expected, $result);
    }
}
