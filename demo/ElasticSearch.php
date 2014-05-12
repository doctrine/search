<?php 

require_once 'config.php';

use Doctrine\Common\ClassLoader;
use Doctrine\Search\Configuration;
use Doctrine\Common\EventManager;
use Doctrine\Search\SearchManager;
use Elastica\Client;
use Doctrine\Search\ElasticSearch\Client as ElasticaAdapter;
use Doctrine\Search\Serializer\JMSSerializer;
use JMS\Serializer\SerializationContext;
use Doctrine\Common\Cache\ArrayCache;

class ElasticSearch
{
	public static function get()
	{
		//Entity loader
		$entities = Config::getEntityNamespacePath();
		$cl = new ClassLoader($entities['namespace'], $entities['path']);
		$cl->register();
		
		//Annotation metadata driver
		$config = new Configuration();
		$md = $config->newDefaultAnnotationDriver(array($entities['namespace']));
		$config->setMetadataDriverImpl($md);
		$config->setMetadataCacheImpl(new ArrayCache());
		
		//Set and configure preferred serializer for persistence
		//If using serialaztion groups you can sepcify the names here
		$config->setEntitySerializer(new JMSSerializer(
			SerializationContext::create()->setGroups('store')
		));
		
		//Add event listeners here
		$eventManager = new EventManager();
		//$eventManager->addEventListener('prePersist', $listener);
		
		//Create the client
		$client = new Client(array('connections' => Config::getServers()));
		
		//Get the search manager
		return new SearchManager(
			$config,
			new ElasticaAdapter($client),
			$eventManager
		);
	}
}