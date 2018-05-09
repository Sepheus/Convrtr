<?php
declare(strict_types=1);

use PHPUnit\Framework\Testcase;

final class ONITests extends TestCase {

    public function testRot() : void {
        $obj = new ONI("Hello");
        $this->assertEquals($obj->rot(13), "Uryyb");
        $this->assertEquals($obj->rot(-13), "Uryyb");
        $this->assertEquals($obj->rot(26), "Hello");
    }

    public function testIntegerInitialiser() : void {
        $obj = new ONI(10);
        $this->assertCount(10, $obj);
    }

    public function testByteWrap() : void {
        $obj = new ONI(1);
        $this->assertEquals($obj->shift_c(-1), "\xFF");
        $this->assertEquals($obj->shift_p([1]), "\xFF");
        $this->assertEquals($obj->shift_k("\x01"), "\xFF");
    }
}