<?php
/**
 * @file ValidatorTest.php
 * Replace with one line description.
 */

use Cxj\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Cxj\Validator\Success;

class ValidatorTest extends TestCase
{
    protected Validator $validator;

    public function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testStringOk(): void
    {
        $v = $this->validator->create("string", "Is a string");
        $result = $v(Success::of("123"));

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals("123", $result->value());
    }
}
