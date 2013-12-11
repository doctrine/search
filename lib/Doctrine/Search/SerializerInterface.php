<?php

namespace Doctrine\Search;

interface SerializerInterface
{
    public function serialize($object);
    public function deserialize($entityName, $data);
}
