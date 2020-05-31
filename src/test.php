<?php
namespace Cxj\Validator;

require '../vendor/autoload.php';

$validator = new Validator();


// Test 1
$validate = $validator->compose(
    $validator->railway_bind([$validator, "string"])

);
$result = $validate(Success::of(123));
echo "Test 1, invalid string: ";
var_dump($result);

// Test 2
$validate = $validator->compose(
    $validator->railway_bind(
        fn($s): Result => $validator->stringNotEmpty($s, "String Not EMpty!")
    )
);
$result = $validate(Success::of("abc"));
echo "Test 2, string not empty: ";
var_dump($result);

// Test 3
$v = $validator->railway_bind(
    fn($s): Result => $validator->stringNotEmpty(
        $s,
        "Dude, what were you thinking?"
    )
);
$result = $v(Success::of(123));
echo "Test 3, string not empty and not string: ";
var_dump($result);

// Test 4
$v = $validator->create([$validator, "string"], "NO, not a string");
$result = $v(Success::of(123));
echo "Test 4, param not a string: ";
var_dump($result);

// Test 5
$v = $validator->createString("The string she is not a string");
$result = $v(Success::of(456));
echo "Test 5, param not a string: ";
var_dump($result);
