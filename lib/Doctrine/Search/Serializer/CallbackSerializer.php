<?php

namespace Doctrine\Search\Serializer;

use Doctrine\Search\SerializerInterface;

class CallbackSerializer implements SerializerInterface
{
    protected $callback;

    public function __construct($callback = 'toArray')
    {
        $this->callback = $callback;
    }

    public function serialize($object)
    {
        return $object->{$this->callback}();
    }
}
