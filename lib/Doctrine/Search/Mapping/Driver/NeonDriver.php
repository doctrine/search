<?php

namespace Doctrine\Search\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Search\InvalidArgumentException;
use Doctrine\Search\Mapping\IndexMetadata;
use Doctrine\Search\Mapping\TypeMetadata;
use Nette\Neon\Neon;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NeonDriver implements MappingDriver
{

	/**
	 * @var array
	 */
	private $classNames;

	/**
	 * @var string
	 */
	private $metadataDirectory;

	/**
	 * @var array
	 */
	private $typesMapping;

	/**
	 * @var array
	 */
	private $indexesMapping;



	public function __construct($metadataDirectory)
	{
		$this->metadataDirectory = $metadataDirectory;
	}



	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string $className
	 * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata $metadata
	 *
	 * @return void
	 */
	public function loadMetadataForClass($className, ClassMetadata $metadata)
	{
		$typeMapping = $this->getTypeMapping($className);
		$this->loadTypeMapping($metadata->type, $typeMapping);

		$indexMapping = $this->getIndexMapping($typeMapping['index']);
		$this->loadIndexMapping($metadata->index, $indexMapping);
	}



	protected function loadTypeMapping(TypeMetadata $type, $typeMapping)
	{
		$type->name = $typeMapping['type'];

		if (!empty($typeMapping['properties'])) {
			$type->setProperties($typeMapping['properties']);
		}

		if (!empty($typeMapping['parameters'])) {
			$type->setParameters($typeMapping['parameters']);
		}

		$settings = $typeMapping;
		unset($settings['class'], $settings['index'], $settings['type'], $settings['properties'], $settings['parameters'], $settings['serializer']);
		$type->setSettings($settings);
	}



	protected function loadIndexMapping(IndexMetadata $index, $indexMapping)
	{
		foreach (array('name', 'numberOfShards', 'numberOfReplicas', 'charFilter', 'filter', 'analyzer') as $key) {
			if (empty($indexMapping[$key])) {
				continue;
			}

			$index->{$key} = $indexMapping[$key];
		}
	}



	public function getAllClassNames()
	{
		if ($this->classNames !== NULL) {
			return $this->classNames;
		}

		$classes = array();
		foreach ($this->getTypesMapping() as $meta) {
			$classes[] = $meta['class'];
		}

		return $this->classNames = $classes;
	}



	/**
	 * Returns whether the class with the specified name should have its metadata loaded.
	 *
	 * @param string $className
	 * @return boolean
	 */
	public function isTransient($className)
	{
		return (bool) $this->getTypeMapping($className);
	}



	/**
	 * @param string $className
	 * @return array
	 */
	protected function getTypeMapping($className)
	{
		foreach ($this->getTypesMapping() as $mapping) {
			if ($mapping['class'] === $className) {
				return $mapping;
			}
		}

		return NULL;
	}



	/**
	 * @return array
	 */
	protected function getTypesMapping()
	{
		if ($this->typesMapping !== NULL) {
			return $this->typesMapping;
		}

		$this->typesMapping = array();
		foreach (glob($this->metadataDirectory . '/*.type.neon') as $file) {
			$meta = Neon::decode(file_get_contents($file));
			if (!isset($meta['class'])) {
				throw new \InvalidArgumentException("The metadata file $file is missing a required field 'class' with entity name.");
			}

			$meta['type'] = basename($file, '.type.neon');
			$this->typesMapping[$meta['type']] = $meta;
		}

		return $this->typesMapping;
	}



	/**
	 * @param string $indexName
	 * @return array
	 */
	protected function getIndexMapping($indexName)
	{
		foreach ($this->getIndexesMapping() as $mapping) {
			if ($mapping['name'] === $indexName) {
				return $mapping;
			}
		}

		throw new InvalidArgumentException(sprintf('Metadata of index %s not found', $indexName));
	}



	/**
	 * @return array
	 */
	protected function getIndexesMapping()
	{
		if ($this->indexesMapping !== NULL) {
			return $this->indexesMapping;
		}

		$this->indexesMapping = array();
		foreach (glob($this->metadataDirectory . '/*.index.neon') as $file) {
			$meta = Neon::decode(file_get_contents($file));
			$meta['name'] = basename($file, '.index.neon');
			$this->indexesMapping[$meta['name']] = $meta;
		}

		return $this->indexesMapping;
	}

}
