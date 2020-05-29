<?php
namespace Cxj;

//use IsString;
//use Validator;
require '../vendor/autoload.php';

/*
function railway_bind(callable $fn): callable
{
    return fn($param): Result
        => $param instanceof ValidationError
        ? $param
        : $fn($param->value);
}

function compose(callable ...$fns): callable
{
    return function ($x) use ($fns) 
        {
            $ret = is_object($x) ? clone($x) : $x;
            foreach ($fns as $fn) {
                $ret = $fn($ret);
            }
            return $ret;
        };
}
*/

$validator = new Validator();
$define = new Define();

$validate = $define->compose(
    $define->railway_bind([$validator, "string"])
);

$result = $validate(Success::of(123));

var_dump($result);
