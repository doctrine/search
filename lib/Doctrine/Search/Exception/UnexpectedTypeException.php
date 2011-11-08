<?php
namespace Doctrine\Search\Exception;

class UnexpectedTypeException extends \UnexpectedValueException {

    public function __construct($value, $expected)
    {
        parent::__construct(
            sprintf(
                'Expected argument of type "%s", "%s" given',
                $expected,
                (is_object($value) ? get_class($value) : gettype($value))
            )
        );
    }

}
