<?php
namespace Cxj\Validator;

require '../vendor/autoload.php';

$validator = new Validator();
$define    = new Define();

$validate = $define->compose(
    $define->railway_bind([$validator, "string"])

);

$result = $validate(Success::of(123));

echo "Test 1, invalid string: ";
var_dump($result);

$validate = $define->compose(
    $define->railway_bind(
        fn($s): Result => $validator->stringNotEmpty($s, "String Not EMpty!")
    )
);

$result = $validate(Success::of("abc"));

echo "Test 2, string not empty: ";
var_dump($result);

//--
$v = $define->railway_bind(
    fn($s): Result => $validator->stringNotEmpty(
        $s,
        "Dude, what were you thinking?"
    )
);
$result = $v(Success::of(123));
echo "Test 3, string not empty and not string: ";
var_dump($result);
