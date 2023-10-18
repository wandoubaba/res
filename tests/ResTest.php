<?php

use PHPUnit\Framework\TestCase;
use Wandoubaba\Res;

class ResTest extends TestCase
{
    public function testConstructor()
    {
        $code = 200;
        $msg = 'Success';
        $data = ['name' => 'John', 'age' => 25];

        $res = new Res($code, $msg, $data);

        $this->assertEquals($code, $res->code);
        $this->assertEquals($msg, $res->msg);
        $this->assertEquals($data, $res->data);
    }

    public function testJsonSerialize()
    {
        $res = new Res();
        $res->setCode(200);
        $res->setMsg('Success');
        $res->setData(['name' => 'John', 'age' => 25]);

        $expected = [
            'code' => 200,
            'msg' => 'Success',
            'data' => ['name' => 'John', 'age' => 25]
        ];

        $result = $res->jsonSerialize();

        $this->assertEquals($expected, array_intersect_key($result, $expected));
        $this->assertArrayHasKey('time_taked', $result);
    }
}
