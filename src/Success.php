<?php
namespace Cxj\Validator;

class Success implements Result
{
    /**
     * @var mixed $value - the lifted value.
     */
    protected $value;

    /**
     * Success constructor.
     * Immutable object.
     *
     * @param mixed $value
     */
    public final function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Lift the value.
     * @param mixed $value
     *
     * @return static - new lifted immutable value object.
     */
    public static function of($value): self
    {
        return new static($value);
    }
}
