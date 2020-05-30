<?php
namespace Cxj\Validator;

class Success implements Result
{
    protected $value;

    public final function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }

    /**
     * Lift the value.
     */
    public static function of($value): self
    {
        return new static($value);
    }
}
