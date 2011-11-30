<?php
require_once __DIR__ . '/bootstrap.php';

use Doctrine\Search\SearchManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\MongoDB\Connection;
use Doctrine\Search\Mapping\Driver\AnnotationDriver as SearchAnnotationDriver;

$config = new Configuration();

$config->setProxyDir(__DIR__ . '/tmp/cache/Proxies');
$config->setProxyNamespace('Proxies');

$config->setHydratorDir(__DIR__ . '/tmp/cache/Hydrators');
$config->setHydratorNamespace('Hydrators');

$config->setDefaultDB('doctrine_odm_tests');

$reader = new AnnotationReader();

$documentsDir = __DIR__ . '/Documents';
$annotationDriver = new AnnotationDriver($reader, $documentsDir);
$config->setMetadataDriverImpl($annotationDriver);

$conn = new Connection(null, array(), $config);
$dm = DocumentManager::create($conn, $config);
$uow = $dm->getUnitOfWork();

$evm = $dm->getEventManager();
$config = $dm->getConfiguration();
$driver = $config->getMetadataDriverImpl();
$paths = $driver->getPaths();

$metaDataFactory = $dm->getMetadataFactory();


$listener = new Doctrine\Search\Listener\MongoDBSearchListener();
$evm->addEventListener('loadClassMetadata', $listener);

$metaDataFactory->getAllMetadata();
var_dump($evm->hasListeners('loadClassMetadata'));
