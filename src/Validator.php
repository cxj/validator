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

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use DateTime;
use DateTimeImmutable;
use Exception;
use ResourceBundle;
use SimpleXMLElement;
use Throwable;
use Traversable;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function call_user_func_array;
use function class_exists;
use function class_implements;
use function count;
use function ctype_alnum;
use function ctype_alpha;
use function ctype_digit;
use function ctype_lower;
use function ctype_upper;
use function file_exists;
use function filter_var;
use function function_exists;
use function get_class;
use function get_resource_type;
use function gettype;
use function implode;
use function in_array;
use function interface_exists;
use function is_a;
use function is_array;
use function is_bool;
use function is_callable;
use function is_dir;
use function is_file;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function is_readable;
use function is_resource;
use function is_scalar;
use function is_string;
use function is_subclass_of;
use function is_writable;
use function lcfirst;
use function mb_detect_encoding;
use function mb_strlen;
use function method_exists;
use function preg_match;
use function property_exists;
use function setlocale;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

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
        if (!is_string($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a string. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function stringNotEmpty($value, $message = '')
    {
        $this->string($value, $message);
        $this->notEq($value, '', $message);

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function integer($value, $message = '')
    {
        if (!is_int($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an integer. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function integerish($value, $message = '')
    {
        if (!is_numeric($value) || $value != (int)$value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an integerish value. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function float($value, $message = '')
    {
        if (!is_float($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a float. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function numeric($value, $message = '')
    {
        if (!is_numeric($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a numeric. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function natural($value, $message = '')
    {
        if (!is_int($value) || $value < 0) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a non-negative integer. Got %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function boolean($value, $message = '')
    {
        if (!is_bool($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a boolean. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function scalar($value, $message = '')
    {
        if (!is_scalar($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a scalar. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function object($value, $message = '')
    {
        if (!is_object($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an object. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string|null $type type of resource this should be. @see
     *     https://www.php.net/manual/en/function.get-resource-type.php
     * @param string $message
     *
     * @return Result
     */
    public function resource($value, $type = null, $message = '')
    {
        if (!is_resource($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a resource. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        if ($type && $type !== get_resource_type($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a resource of type %2$s. Got: %s',
                    $this->typeToString($value),
                    $type
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function isCallable($value, $message = '')
    {
        if (!is_callable($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a callable. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     * @return Result
     */
    public function isArray($value, $message = '')
    {
        if (!is_array($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an array. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function isArrayAccessible($value, $message = '')
    {
        if (!is_array($value) && !($value instanceof ArrayAccess)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an array accessible. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function isCountable($value, $message = '')
    {
        if (
            !is_array($value)                        /** @phpstan-ignore-line */
            && !($value instanceof Countable)        /** @phpstan-ignore-line */
            && !($value instanceof ResourceBundle)   /** @phpstan-ignore-line */
            && !($value instanceof SimpleXMLElement) /** @phpstan-ignore-line */
        ) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a countable. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function isIterable($value, $message = '')
    {
        if (!is_array($value) && !($value instanceof Traversable)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an iterable. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string|object $class
     * @param string $message
     *
     * @return Result
     */
    public function isInstanceOf($value, $class, $message = '')
    {
        if (!($value instanceof $class)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an instance of %2$s. Got: %s',
                    $this->typeToString($value),
                    $class
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string|object $class
     * @param string $message
     *
     * @return Result
     */
    public function notInstanceOf($value, $class, $message = '')
    {
        if ($value instanceof $class) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an instance other than %2$s. Got: %s',
                    $this->typeToString($value),
                    $class
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param array<object|string> $classes
     * @param string $message
     * @return Result
     * @return Result
     */
    public function isInstanceOfAny($value, $classes, $message = '')
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return Success::of($value);
            }
        }

        return new Failure(
            sprintf(
                $message ?: 'Expected an instance of any of %2$s. Got: %s',
                $this->typeToString($value),
                implode(
                    ', ',
                    array_map([$this, 'valueToString'], $classes)
                )
            )
        );
    }

    /**
     *
     * @param object|string $value
     * @param string $class
     * @param string $message
     *
     * @return Result
     */
    public function isAOf($value, $class, $message = '')
    {
        $this->string($class, 'Expected class as a string. Got: %s');

        if (!is_a($value, $class, is_string($value))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an instance of this class or to this class among his parents %2$s. Got: %s',
                    $this->typeToString($value),
                    $class
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param object|string $value
     * @param string $class
     * @param string $message
     *
     * @return Result
     */
    public function isNotA($value, $class, $message = '')
    {
        $this->string($class, 'Expected class as a string. Got: %s');

        if (is_a($value, $class, is_string($value))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an instance of this class or to this class among his parents other than %2$s. Got: %s',
                    $this->typeToString($value),
                    $class
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param object|string $value
     * @param string[] $classes
     * @param string $message
     * @return Result
     * @return Result
     */
    public function isAnyOf($value, array $classes, $message = '')
    {
        foreach ($classes as $class) {
            $this->string($class, 'Expected class as a string. Got: %s');

            if (is_a($value, $class, is_string($value))) {
                return Success::of($value);
            }
        }

        return new Failure(
            sprintf(
                $message ?: 'Expected an any of instance of this class or to this class among his parents other than %2$s. Got: %s',
                $this->typeToString($value),
                implode(
                    ', ',
                    array_map(['static', 'valueToString'], $classes)
                )
            )
        );
    }

    /**
     * @param mixed $value
     * @param string $message
     * @return Result
     * @return Result
     */
    public function isEmpty($value, $message = '')
    {
        if (!empty($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an empty value. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function notEmpty($value, $message = '')
    {
        if (empty($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a non-empty value. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function null($value, $message = '')
    {
        if (null !== $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected null. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function notNull($value, $message = '')
    {
        if (null === $value) {
            return new Failure(
                $message ?: 'Expected a value other than null.'
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function true($value, $message = '')
    {
        if (true !== $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be true. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function false($value, $message = '')
    {
        if (false !== $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be false. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function notFalse($value, $message = '')
    {
        if (false === $value) {
            return new Failure(
                $message ?: 'Expected a value other than false.'
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function ip($value, $message = '')
    {
        if (false === filter_var($value, FILTER_VALIDATE_IP)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be an IP. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function ipv4($value, $message = '')
    {
        if (false ===
            filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be an IPv4. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function ipv6($value, $message = '')
    {
        if (false ===
            filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be an IPv6. Got %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function email($value, $message = '')
    {
        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to be a valid e-mail address. Got %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Does non strict comparisons on the items, so ['3', 3] will not pass the
     * assertion.
     *
     * @param array $values
     * @param string $message
     *
     * @return Result
     */
    public function uniqueValues(array $values, $message = '')
    {
        $allValues    = count($values);
        $uniqueValues = count(array_unique($values));

        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;

            return new Failure(
                sprintf(
                    $message ?: 'Expected an array of unique values, but %s of them %s duplicated',
                    $difference,
                    (1 === $difference ? 'is' : 'are')
                )
            );
        }

        return Success::of($values);
    }

    /**
     * @param mixed $value
     * @param mixed $expect
     * @param string $message
     *
     * @return Result
     */
    public function eq($value, $expect, $message = '')
    {
        if ($expect != $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value equal to %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($expect)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param mixed $expect
     * @param string $message
     *
     * @return Result
     */
    public function notEq($value, $expect, $message = '')
    {
        if ($expect == $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a different value than %s.',
                    $this->valueToString($expect)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $expect
     * @param string $message
     *
     * @return Result
     */
    public function same($value, $expect, $message = '')
    {
        if ($expect !== $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value identical to %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($expect)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $expect
     * @param string $message
     *
     * @return Result
     */
    public function notSame($value, $expect, $message = '')
    {
        if ($expect === $value) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value not identical to %s.',
                    $this->valueToString($expect)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $limit
     * @param string $message
     *
     * @return Result
     */
    public function greaterThan($value, $limit, $message = '')
    {
        if ($value <= $limit) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value greater than %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($limit)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $limit
     * @param string $message
     *
     * @return Result
     */
    public function greaterThanEq($value, $limit, $message = '')
    {
        if ($value < $limit) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value greater than or equal to %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($limit)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $limit
     * @param string $message
     *
     * @return Result
     */
    public function lessThan($value, $limit, $message = '')
    {
        if ($value >= $limit) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value less than %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($limit)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $limit
     * @param string $message
     *
     * @return Result
     */
    public function lessThanEq($value, $limit, $message = '')
    {
        if ($value > $limit) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value less than or equal to %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($limit)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Inclusive range, so (3, 3, 5) passes.    TODO
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @param string $message
     * @return Result
     * @return Result
     */
    public function range($value, $min, $max, $message = '')
    {
        if ($value < $min || $value > $max) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value between %2$s and %3$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($min),
                    $this->valueToString($max)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * A more human-readable alias of inArray().    TODO
     * @param mixed $value
     * @param array $values
     * @param string $message
     * @return Result
     * @return Result
     */
    public function oneOf($value, array $values, $message = '')
    {
        $this->inArray($value, $values, $message);

        return Success::of($value);
    }

    /**
     * Does strict comparison, so Assert::inArray(3, ['3']) does not pass the
     * assertion.
     *
     *
     * @param mixed $value
     * @param array $values
     * @param string $message
     *
     * @return Result
     */
    public function inArray($value, array $values, $message = '')
    {
        if (!in_array($value, $values, true)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected one of: %2$s. Got: %s',
                    $this->valueToString($value),
                    implode(
                        ', ',
                        array_map(['static', 'valueToString'], $values)
                    )
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $subString
     * @param string $message
     *
     * @return Result
     */
    public function contains($value, $subString, $message = '')
    {
        if (false === strpos($value, $subString)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($subString)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $subString
     * @param string $message
     *
     * @return Result
     */
    public function notContains($value, $subString, $message = '')
    {
        if (false !== strpos($value, $subString)) {
            return new Failure(
                sprintf(
                    $message ?: '%2$s was not expected to be contained in a value. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($subString)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function notWhitespaceOnly($value, $message = '')
    {
        if (preg_match('/^\s*$/', $value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a non-whitespace string. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $prefix
     * @param string $message
     *
     * @return Result
     */
    public function startsWith($value, $prefix, $message = '')
    {
        if (0 !== strpos($value, $prefix)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to start with %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($prefix)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $prefix
     * @param string $message
     *
     * @return Result
     */
    public function notStartsWith($value, $prefix, $message = '')
    {
        if (0 === strpos($value, $prefix)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value not to start with %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($prefix)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function startsWithLetter($value, $message = '')
    {
        $this->string($value);

        $valid = isset($value[0]);

        if ($valid) {
            $locale = setlocale(LC_CTYPE, "0");
            setlocale(LC_CTYPE, 'C');
            $valid = ctype_alpha($value[0]);
            setlocale(LC_CTYPE, $locale);
        }

        if (!$valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to start with a letter. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $suffix
     * @param string $message
     *
     * @return Result
     */
    public function endsWith($value, $suffix, $message = '')
    {
        if ($suffix !== substr($value, -strlen($suffix))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to end with %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($suffix)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $suffix
     * @param string $message
     *
     * @return Result
     */
    public function notEndsWith($value, $suffix, $message = '')
    {
        if ($suffix === substr($value, -strlen($suffix))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value not to end with %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($suffix)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $pattern
     * @param string $message
     *
     * @return Result
     */
    public function regex($value, $pattern, $message = '')
    {
        if (!preg_match($pattern, $value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The value %s does not match the expected pattern.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $pattern
     * @param string $message
     *
     * @return Result
     */
    public function notRegex($value, $pattern, $message = '')
    {
        if (preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
            return new Failure(
                sprintf(
                    $message ?: 'The value %s matches the pattern %s (at offset %d).',
                    $this->valueToString($value),
                    $this->valueToString($pattern),
                    $matches[0][1]
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function unicodeLetters($value, $message = '')
    {
        $this->string($value);

        if (!preg_match('/^\p{L}+$/u', $value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain only Unicode letters. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function alpha($value, $message = '')
    {
        $this->string($value);

        $locale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alpha($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain only letters. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function digits($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_digit($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain digits only. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function alnum($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alnum($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain letters and digits only. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function lower($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_lower($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain lowercase characters only. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function upper($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, "0");
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_upper($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain uppercase characters only. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string $value
     * @param int $length
     * @param string $message
     *
     * @return Result
     */
    public function length($value, $length, $message = '')
    {
        if ($length !== $this->strlen($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain %2$s characters. Got: %s',
                    $this->valueToString($value),
                    $length
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Inclusive min.
     *
     *
     * @param string $value
     * @param int|float $min
     * @param string $message
     *
     * @return Result
     */
    public function minLength($value, $min, $message = '')
    {
        if ($this->strlen($value) < $min) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain at least %2$s characters. Got: %s',
                    $this->valueToString($value),
                    $min
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Inclusive max.
     *
     *
     * @param string $value
     * @param int|float $max
     * @param string $message
     *
     * @return Result
     */
    public function maxLength($value, $max, $message = '')
    {
        if ($this->strlen($value) > $max) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain at most %2$s characters. Got: %s',
                    $this->valueToString($value),
                    $max
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Inclusive , so Assert::lengthBetween('asd', 3, 5); passes the assertion.
     *
     *
     * @param string $value
     * @param int|float $min
     * @param int|float $max
     * @param string $message
     *
     * @return Result
     */
    public function lengthBetween($value, $min, $max, $message = '')
    {
        $length = $this->strlen($value);

        if ($length < $min || $length > $max) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s',
                    $this->valueToString($value),
                    $min,
                    $max
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Will also pass if $value is a directory, use Assert::file() instead if
     * you need to be sure it is a file.
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function fileExists($value, $message = '')
    {
        $this->string($value);

        if (!file_exists($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The file %s does not exist.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function file($value, $message = '')
    {
        $this->fileExists($value, $message);

        if (!is_file($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The path %s is not a file.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function directory($value, $message = '')
    {
        $this->fileExists($value, $message);

        if (!is_dir($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The path %s is no directory.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function readable($value, $message = '')
    {
        if (!is_readable($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The path %s is not readable.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function writable($value, $message = '')
    {
        if (!is_writable($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'The path %s is not writable.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function classExists($value, $message = '')
    {
        if (!class_exists($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an existing class name. Got: %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param string|object $class
     * @param string $message
     *
     * @return Result
     */
    public function subclassOf($value, $class, $message = '')
    {
        if (!is_subclass_of($value, $class)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected a sub-class of %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($class)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function interfaceExists($value, $message = '')
    {
        if (!interface_exists($value)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an existing interface name. got %s',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param mixed $value
     * @param mixed $interface
     * @param string $message
     *
     * @return Result
     */
    public function implementsInterface($value, $interface, $message = '')
    {
        if (!in_array($interface, class_implements($value))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an implementation of %2$s. Got: %s',
                    $this->valueToString($value),
                    $this->valueToString($interface)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param string|object $classOrObject
     * @param mixed $property
     * @param string $message
     *
     * @return Result
     */
    public function propertyExists($classOrObject, $property, $message = '')
    {
        if (!property_exists($classOrObject, $property)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the property %s to exist.',
                    $this->valueToString($property)
                )
            );
        }

        return Success::of(true);
    }

    /**
     *
     * @param string|object $classOrObject
     * @param mixed $property
     * @param string $message
     *
     * @return Result
     */
    public function propertyNotExists($classOrObject, $property, $message = '')
    {
        if (property_exists($classOrObject, $property)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the property %s to not exist.',
                    $this->valueToString($property)
                )
            );
        }

        return Success::of($classOrObject);
    }

    /**
     * @param string|object $classOrObject
     * @param mixed $method
     * @param string $message
     *
     * @return Result
     */
    public function methodExists($classOrObject, $method, $message = ''): Result
    {
        if (!method_exists($classOrObject, $method)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the method %s to exist.',
                    $this->valueToString($method)
                )
            );
        }

        return Success::of($classOrObject);
    }

    /**
     * @param string|object $classOrObject
     * @param mixed $method
     * @param string $message
     *
     * @return Result
     */
    public function methodNotExists($classOrObject, $method, $message = '')
    {
        if (method_exists($classOrObject, $method)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the method %s to not exist.',
                    $this->valueToString($method)
                )
            );
        }

        return Success::of($classOrObject);
    }

    /**
     *
     * @param array $array
     * @param string|int $key
     * @param string $message
     *
     * @return Result
     */
    public function keyExists($array, $key, $message = '')
    {
        if (!(isset($array[$key]) || array_key_exists($key, $array))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the key %s to exist.',
                    $this->valueToString($key)
                )
            );
        }

        return Success::of($array);
    }

    /**
     *
     * @param array $array
     * @param string|int $key
     * @param string $message
     *
     * @return Result
     */
    public function keyNotExists($array, $key, $message = '')
    {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected the key %s to not exist.',
                    $this->valueToString($key)
                )
            );
        }

        return Success::of($array);
    }

    /**
     * Checks if a value is a valid array key (int or string).
     *
     *
     * @param mixed $value
     * @param string $message
     *
     * @return Result
     */
    public function validArrayKey($value, $message = '')
    {
        if (!(is_int($value) || is_string($value))) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected string or integer. Got: %s',
                    $this->typeToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     * Does not check if $array is countable, this can generate a warning on
     * php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int $number
     * @param string $message
     *
     * @return Result
     */
    public function count($array, $number, $message = '')
    {
        $this->eq(
            count($array),
            $number,
            sprintf(
                $message ?: 'Expected an array to contain %d elements. Got: %d.',
                $number,
                count($array)
            )
        );

        return Success::of($array);
    }

    /**
     * Does not check if $array is countable, this can generate a warning on
     * php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float $min
     * @param string $message
     *
     * @return Result
     */
    public function minCount($array, $min, $message = '')
    {
        if (count($array) < $min) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an array to contain at least %2$d elements. Got: %d',
                    count($array),
                    $min
                )
            );
        }

        return Success::of($array);
    }

    /**
     * Does not check if $array is countable, this can generate a warning on
     * php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float $max
     * @param string $message
     *
     * @return Result
     */
    public function maxCount($array, $max, $message = '')
    {
        if (count($array) > $max) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an array to contain at most %2$d elements. Got: %d',
                    count($array),
                    $max
                )
            );
        }

        return Success::of($array);
    }

    /**
     * Does not check if $array is countable, this can generate a warning on
     * php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float $min
     * @param int|float $max
     * @param string $message
     *
     * @return Result
     */
    public function countBetween($array, $min, $max, $message = '')
    {
        $count = count($array);

        if ($count < $min || $count > $max) {
            return new Failure(
                sprintf(
                    $message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %d',
                    $count,
                    $min,
                    $max
                )
            );
        }

        return Success::of($array);
    }

    /**
     *
     * @param mixed $array
     * @param string $message
     *
     * @return Result
     */
    public function isList($array, $message = '')
    {
        if (!is_array($array) || $array !== array_values($array)) {
            return new Failure(
                $message ?: 'Expected list - non-associative array.'
            );
        }

        return Success::of($array);
    }

    /**
     *
     * @param mixed $array
     * @param string $message
     *
     * @return Result
     */
    public function isNonEmptyList($array, $message = '')
    {
        $this->isList($array, $message);
        $this->notEmpty($array, $message);

        return Success::of($array);
    }

    /**
     *
     * @param mixed $array
     * @param string $message
     *
     * @return Result
     */
    public function isMap($array, $message = '')
    {
        if (
            !is_array($array) ||
            array_keys($array) !==
            array_filter(array_keys($array), '\is_string')
        ) {
            return new Failure(
                $message ?: 'Expected map - associative array with string keys.'
            );
        }

        return Success::of($array);
    }

    /**
     *
     * @param mixed $array
     * @param string $message
     *
     * @return Result
     */
    public function isNonEmptyMap($array, $message = '')
    {
        $this->isMap($array, $message);
        $this->notEmpty($array, $message);

        return Success::of($array);
    }

    /**
     *
     * @param string $value
     * @param string $message
     *
     * @return Result
     */
    public function uuid($value, $message = '')
    {
        $value = str_replace(['urn:', 'uuid:', '{', '}'], '', $value);

        // The nil UUID is special form of UUID that is specified to have all
        // 128 bits set to zero.
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return Success::of($value);
        }

        if (!preg_match(
            '/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/',
            $value
        )) {
            return new Failure(
                sprintf(
                    $message ?: 'Value %s is not a valid UUID.',
                    $this->valueToString($value)
                )
            );
        }

        return Success::of($value);
    }

    /**
     *
     * @param Closure $expression
     * @param string $class
     * @param string $message
     *
     * @return Result
     */
    public function throws(
        Closure $expression,
        $class = 'Exception',
        $message = ''
    )
    {
        $this->string($class);

        $actual = 'none';

        try {
            $expression();
        }
        catch (Exception $e) {
            $actual = get_class($e);
            if ($e instanceof $class) {
                return Success::of($expression);
            }
        }
        catch (Throwable $e) {
            $actual = get_class($e);
            if ($e instanceof $class) {
                return Success::of($expression);
            }
        }

        return new Failure(
            $message ?: sprintf(
                'Expected to throw "%s", got "%s"',
                $class,
                $actual
            )
        );
    }


    /**
     * @param string $name
     * @param array $arguments
     * @throws BadMethodCallException
     * @return Result
     */
    public function __call($name, $arguments)
    {
        if ('nullOr' === substr($name, 0, 6)) {
            if (null !== $arguments[0]) {
                $method = lcfirst(substr($name, 6));
                return call_user_func_array([$this, $method], $arguments);    // todo
            }

            return new Failure("Is not null");
        }

        if ('all' === substr($name, 0, 3)) {
            $this->isIterable($arguments[0]);

            $method = lcfirst(substr($name, 3));
            $args = $arguments;

            foreach ($arguments[0] as $entry) {
                $args[0] = $entry;

                return call_user_func_array([$this, $method], $args); // todo
            }

            return new Failure("Is not any");
        }

        throw new BadMethodCallException('No such method: '.$name);
    }


    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function valueToString($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return get_class($value) .
                       ': ' .
                       self::valueToString($value->__toString());
            }

            if ($value instanceof DateTime ||
                $value instanceof DateTimeImmutable) {
                return get_class($value) .
                       ': ' .
                       self::valueToString($value->format('c'));
            }

            return get_class($value);
        }

        if (is_resource($value)) {
            return 'resource';
        }

        if (is_string($value)) {
            return '"' . $value . '"';
        }

        return (string)$value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function typeToString($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * @param string $value
     *
     * @return int
     */
    protected function strlen($value): int
    {
        if (!function_exists('mb_detect_encoding')) {
            return strlen($value);
        }

        if (false === $encoding = mb_detect_encoding($value)) {
            return strlen($value);
        }

        return mb_strlen($value, $encoding);
    }

    /**
     * EXPERIMENTAL - Tries to make the API easier to use.
     * Caller passes simple strings instead of anonymous function closure.
     * Downside: passing string method name.  Needs to be visible to IDE/Stan.
     *
     * @param string $method
     * @param string $message
     *
     * @return callable
     */
    public function create(string $method, string $message = ''): callable
    {
        $callable = [$this, $method];

        return $this->bind1param(
            fn($s): Result => $callable($s, $message)
        );
    }

    public function create2(string $method, string $message = ''): callable
    {
        $callable = [$this, $method];

        return $this->bind2param(
            fn($p1, $p2): Result => $callable($p1, $p2, $message)
        );
    }

    public function create3(string $method, string $message = ''): callable
    {
        $callable = [$this, $method];

        return $this->bind3param(
            fn($p1, $p2, $p3): Result => $callable($p1, $p2, $p3, $message)
        );
    }

    /**
     * A Functional Either Monad.
     * Returns a closure which will produce Either the Left (Success) or
     * Right (Failure) value.
     *
     * @param callable $fn
     *
     * @return callable
     */
    public function bind1param(callable $fn): callable
    {
        return fn($param): Result => $param instanceof Failure
            ? $param
            : $fn($param->value());
    }

    public function bind2param(callable $fn): callable {
        return fn($param, $p2): Result => $param instanceof Failure
            ? $param
            : $fn($param->value(), $p2);
    }


    public function bind3param(callable $fn): callable {
        return fn($param, $p2, $p3): Result => $param instanceof Failure
            ? $param
            : $fn($param->value(), $p2, $p3);
    }

    /**
     * Returns a closure that will call all of the passed functions in order.
     *
     * @param callable ...$fns
     *
     * @return callable
     */
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
