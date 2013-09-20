<?php

namespace Doctrine\Search\Serializer;

use Doctrine\Search\SerializerInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

class JMSSerializer implements SerializerInterface
{
    protected $serializer;
    protected $context;

    public function __construct(SerializationContext $context = null)
    {
        $this->context = $context;
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->addDefaultHandlers()
            ->build();
    }

    public function serialize($object)
    {
        $context = $this->context ? clone $this->context : null;
        return json_decode($this->serializer->serialize($object, 'json', $context), true);
    }
    
    public function deserialize($entityName, $data)
    {
        return $this->serializer->deserialize($data, $entityName, 'json');
    }
}
