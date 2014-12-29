<?php

namespace Doctrine\Search\ElasticSearch\Mapping;

use Doctrine\Search;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TypeMetadata extends Search\Mapping\TypeMetadata
{

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var array
	 */
	public $properties = array();



	protected function validateSettings(array $options)
	{

	}



	protected function validateProperty($name, array $property)
	{
		if (!isset($property['type'])) {
			throw new Search\InvalidMetadataException("Type of property is mandatory.");
		}
	}

}
