<?php

namespace Doctrine\Search\ElasticSearch;

use Doctrine;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Mapping\TypeMetadata;
use Doctrine\Search\SearchManager;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Type\Mapping as ElasticaTypeMapping;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SchemaManager implements Doctrine\Search\SchemaManager
{

	/**
	 * @var \Doctrine\Search\ElasticSearch\Client
	 */
	private $client;

	/**
	 * @var \Elastica\Client
	 */
	private $elastica;



	public function __construct(Client $client)
	{
		$this->client = $client;
		$this->elastica = $client->getClient();
	}



	/**
	 * @param ClassMetadata $class
	 * @return \Elastica\Index
	 */
	protected function getIndex(ClassMetadata $class)
	{
		return $this->elastica
			->getIndex($class->getIndexName());
	}



	/**
	 * @param ClassMetadata $class
	 * @return \Elastica\Type
	 */
	protected function getType(ClassMetadata $class)
	{
		return $this->getIndex($class)
			->getType($class->getTypeName());
	}



	/**
	 * @param array|ClassMetadata[] $classes
	 */
	public function dropMappings(array $classes)
	{
		foreach ($classes as $class) {
			if (!$this->hasIndex($class->getIndexName())) {
				continue;
			}

			if (!$this->hasType($class)) {
				continue;
			}

			$this->dropType($class);
		}

		foreach ($classes as $class) {
			if (!$this->hasIndex($class->getIndexName())) {
				continue;
			}

			$this->dropIndex($class->getIndexName());
		}
	}



	/**
	 * @param array|ClassMetadata[] $classes
	 * @param bool $withAliases
	 * @return array
	 */
	public function createMappings(array $classes, $withAliases = FALSE)
	{
		$aliases = [];
		$date = date('YmdHis');
		foreach ($classes as $class) {
			if ($withAliases) {
				$indexAlias = $class->getIndexName() . '_' . $date;
				$aliases[$indexAlias] = $class->getIndexName();

				$fakeMetadata = clone $class;
				$fakeMetadata->index->name = $indexAlias;

				$class = $fakeMetadata;
			}

			if (!$this->hasIndex($class->getIndexName())) {
				$this->createIndex($class);
			}

			$this->createType($class);
		}

		return $aliases;
	}



	public function createAliases(array $aliases)
	{
		foreach ($aliases as $alias => $original) {
			try {
				$this->createAlias($alias, $original);
			} catch (ResponseException $e) {
			}
		}
	}



	public function hasIndex($index)
	{
		return $this->elastica->getIndex($index)->exists();
	}



	public function createIndex(ClassMetadata $class)
	{
		$index = $this->elastica->getIndex($class->getIndexName());
		$response = $index->create(array(
			'number_of_shards' => $class->index->numberOfShards,
			'number_of_replicas' => $class->index->numberOfReplicas,
			'analysis' => array(
				'char_filter' => $class->index->charFilter,
				'analyzer' => $class->index->analyzer,
				'filter' => $class->index->filter,
			),
		), TRUE);

		return $response;
	}



	public function dropIndex($index)
	{
		return $this->elastica->getIndex($index)->delete();
	}



	public function hasType(ClassMetadata $class)
	{
		return $this->getType($class)->exists();
	}



	public function createType(ClassMetadata $class)
	{
		$mapping = new ElasticaTypeMapping($this->getType($class), self::settingsToUnderscore($class->type->properties));
		$mapping->disableSource($class->type->source);

		if ($class->type->boost !== NULL) {
			$mapping->setParam('_boost', array('name' => '_boost', 'null_value' => $class->type->boost));
		}

		if ($class->parent !== NULL) {
			$mapping->setParent($class->parent);
		}

		foreach ($class->type->settings as $key => $value) {
			$mapping->setParam($key, $value);
		}

		return $mapping->send();
	}



	public function dropType(ClassMetadata $class)
	{
		return $this->getType($class)->delete();
	}



	public function createAlias($alias, $original)
	{
		try {
			$this->elastica->request(sprintf('_all/_alias/%s', $original), Request::DELETE);

		} catch (ResponseException $e) {
			if (stripos($e->getMessage(), 'AliasesMissingException') === FALSE) {
				throw $e;
			}
		}

		$this->elastica->request(sprintf('/%s/_alias/%s', $alias, $original), Request::PUT);
	}



	private static function settingsToUnderscore($properties)
	{
		foreach ($properties as $name => $settings) {
			$fixed = [];
			foreach ($settings as $key => $value) {
				$fixed[self::toUnderscore($key)] = $value;
			}

			if (isset($settings['properties'])) {
				$fixed['properties'] = self::settingsToUnderscore($settings['properties']);
			}

			$properties[$name] = $fixed;
		}

		return $properties;
	}



	/**
	 * camelCaseField name -> underscore_separated.
	 *
	 * @param string $s
	 * @return string
	 */
	private static function toUnderscore($s)
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1_', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);

		return $s;
	}

}
