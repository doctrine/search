<?php

namespace Doctrine\Search\Mapping;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class TypeMetadata
{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var bool
	 */
	public $source = TRUE;

	/**
	 * @var integer
	 */
	public $boost;

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var array
	 */
	public $properties = array();

	/**
	 * @var array
	 */
	public $parameters = array();



	public function __construct($className)
	{
		$this->className = $className;
	}



	public function setSettings(array $options)
	{
		$this->validateSettings($options);
		$this->settings = $options;
	}



	public function setProperties(array $properties)
	{
		foreach ($properties as $name => $property) {
			$this->validateProperty($name, $property);
		}

		$this->properties = $properties;
	}



	public function setParameters(array $parameters)
	{
		foreach ($parameters as $name => $field) {
			$this->parameters[is_numeric($name) ? $field : $name] = $field;
		}
	}



	abstract protected function validateSettings(array $options);

	abstract protected function validateProperty($name, array $property);

}
