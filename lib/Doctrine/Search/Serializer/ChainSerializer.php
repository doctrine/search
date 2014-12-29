<?php

namespace Doctrine\Search\Serializer;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Search\DefaultSerializerNotProvidedException;
use Doctrine\Search\SerializerInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ChainSerializer implements SerializerInterface
{

	/**
	 * @var SerializerInterface[]
	 */
	private $serializers = [];

	/**
	 * @var SerializerInterface
	 */
	private $defaultSerializer;



	public function addSerializer($classType, SerializerInterface $serializer)
	{
		$this->serializers[strtolower($classType)] = $serializer;
	}



	public function setDefaultSerializer(SerializerInterface $serializer)
	{
		$this->defaultSerializer = $serializer;
	}



	/**
	 * @param object $object
	 * @throws DefaultSerializerNotProvidedException
	 * @return string
	 */
	public function serialize($object)
	{
		$lName = strtolower(ClassUtils::getClass($object));
		if (isset($this->serializers[$lName])) {
			return $this->serializers[$lName]->serialize($object);
		}

		if (!$this->defaultSerializer) {
			throw new DefaultSerializerNotProvidedException;
		}

		return $this->defaultSerializer->serialize($object);
	}



	/**
	 * @param string $entityName
	 * @param string $data
	 * @throws DefaultSerializerNotProvidedException
	 * @return object
	 */
	public function deserialize($entityName, $data)
	{
		$lName = strtolower($entityName);
		if (isset($this->serializers[$lName])) {
			return $this->serializers[$lName]->deserialize($entityName, $data);
		}

		if (!$this->defaultSerializer) {
			throw new DefaultSerializerNotProvidedException;
		}

		return $this->defaultSerializer->deserialize($entityName, $data);
	}

}
