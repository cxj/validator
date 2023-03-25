<?php

namespace Cxj\Validator;

class Failure implements Result
{
    protected string $message;

    /**
     * InvalidString constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
