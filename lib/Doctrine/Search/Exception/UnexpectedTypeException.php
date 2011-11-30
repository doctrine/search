<?php
namespace Doctrine\Search\Exception;

use \Doctrine\Search\Exception\DoctrineSearchException;

class UnexpectedTypeException extends DoctrineSearchException {

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
