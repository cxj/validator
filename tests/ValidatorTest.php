<?php
/**
 * @file ValidatorTest.php
 * Replace with one line description.
 */

use Cxj\Validator\Failure;
use Cxj\Validator\Success;
use Cxj\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    protected Validator $validator;

    public function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testStringOk(): void
    {
        $v      = $this->validator->create("string", "Is a string: %s");
        $result = $v(Success::of("123"));
        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals("123", $result->value());
    }

    public function testStringBad(): void
    {
        $v      = $this->validator->create("string", "Is not a string: %s");
        $result = $v(Success::of(123));
        $this->assertInstanceOf(Failure::class, $result);
        $this->assertEquals("Is not a string: integer", $result->getMessage());
    }
}
