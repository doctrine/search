<?php

namespace Doctrine\Search\Serializer;

use Doctrine\Search\SerializerInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;

class JMSSerializer implements SerializerInterface
{
    protected $serializer;
    protected $context;

    public function __construct(SerializationContext $context = null)
    {
        $this->context = $context;
        $this->serializer = SerializerBuilder::create()->addDefaultHandlers()->build();	
    }

    public function serialize($object)
    {
        $context = $this->context ? clone $this->context : null; 
        return json_decode($this->serializer->serialize($object, 'json', $context), true);
    }
}