<?php
namespace Cxj;

class Validator
{
    public function string($value, string $message = ''): Result
    {
         if (!\is_string($value)) {
            return new InvalidString($message);
         }
         return Success::of($value);
    }
}
