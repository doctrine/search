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
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

class ElasticSearch
{
    public static function get()
    {
        //Entity loader
        $entities = Config::getEntityNamespacePath();

        //Annotation metadata driver
        $config = new Configuration();
        $md = $config->newDefaultAnnotationDriver(array($entities['path']));
        $config->setMetadataDriverImpl($md);
        $config->setMetadataCacheImpl(new ArrayCache());

        //Set and configure preferred serializer for persistence
        $serializer = SerializerBuilder::create()
            ->addMetadataDir(__DIR__.'/yaml')
            ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
            ->addDefaultHandlers()
            ->build();

        //If using serialization groups you can sepcify the names here
        $config->setEntitySerializer(new JMSSerializer(
            SerializationContext::create()->setGroups(array('Default')),
            $serializer
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
