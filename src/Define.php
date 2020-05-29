<?php
namespace Cxj;

class Define {

    public function railway_bind(callable $fn): callable
    {
        return fn($param): Result
            => $param instanceof ValidationError
            ? $param
            : $fn($param->value());
    }

    public function compose(callable ...$fns): callable
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
}
