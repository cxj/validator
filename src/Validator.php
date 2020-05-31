<?php
/**
 * This file is part of the cxj/validator package.
 *
 * Copyright 2020 (c) Chris Johnson <cxjohnson@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cxj\Validator;

/**
 * Efficient functional tests to validate method input and output parameters.
 * @package Cxj\Validator
 */
class Validator
{
    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function string($value, string $message = ''): Result
    {
        if (!\is_string($value)) {
            return new InvalidString($message);
        }

        return Success::of($value);
    }

    public function stringNotEmpty($value, string $message = ''): Result
    {
        if ($this->string($value) instanceof ValidationError) {
            return new InvalidString($message);
        }
        if ($value != "") {
            return new ValidationError($message);
        }

        return Success::of($value);
    }

    /**
     * @param $method
     * @param string $message
     *
     * @return callable
     */
    public function create(array $method, string $message): callable
    {
        return $this->railway_bind(
            fn($s): Result => $method($s, $message)
        );
    }

    public function createString($message): callable
    {
        return $this->create([$this, "string"], $message);
    }

    public function railway_bind(callable $fn): callable
    {
        return fn($param): Result => $param instanceof ValidationError
            ? $param
            : $fn($param->value());
    }

    public function compose(callable ...$fns): callable
    {
        return function ($x) use ($fns) {
            $ret = is_object($x) ? clone($x) : $x;
            foreach ($fns as $fn) {
                $ret = $fn($ret);
            }

            return $ret;
        };
    }
}
