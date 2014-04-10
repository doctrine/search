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
		
		//Set and configure preferred serializer for persistence
		//If using serialaztion groups you can sepcify the names here
		$config->setEntitySerializer(new JMSSerializer(
			SerializationContext::create()->setGroups('store')
		));
		
		//Add event listeners here
		$eventManager = new EventManager();
		//$eventManager->addEventListener('prePersist', $listener);
		
		//Get the search manager
		return new SearchManager(
			$config,
			new ElasticaAdapter(new Client(Config::getServers())),
			$eventManager
		);
	}
}