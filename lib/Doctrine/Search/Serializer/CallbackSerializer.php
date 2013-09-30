<?php

namespace Doctrine\Search\Serializer;

use Doctrine\Search\SerializerInterface;

class CallbackSerializer implements SerializerInterface
{
    protected $serializerCallback;
    protected $deserializerCallback;

    public function __construct($serializerCallback = 'toArray', $deserializerCallback = 'fromArray')
    {
        $this->serializerCallback = $serializerCallback;
        $this->deserializerCallback = $deserializerCallback;
    }

    public function serialize($object)
    {
        return $object->{$this->serializerCallback}();
    }
    
    public function deserialize($entityName, $data)
    {
        $entity = new $entityName();
        $entity->{$this->deserializerCallback}($data);
        return $entity;
    }
}
