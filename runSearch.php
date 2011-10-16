<?php
use Doctrine\Search\SearchManager;
use Doctrine\Search\Solr\Client as SearchClient;
use Doctrine\Search\Solr\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;



$connection = new Connection();
$configuration = new Configuration('localhost', '8888', 'testindex', );
$searchClient = new SearchClient();
$objectManager = new DocumentManager();

$searchManager = new SearchManager($searchClient, $objectManager);
