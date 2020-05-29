<?php
namespace Cxj;

class InvalidString implements ValidationError
{
    protected string $message;


    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
