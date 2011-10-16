<?php
use Doctrine\Search\SearchManager;
use Doctrine\Search\Solr\Client as SearchClient;

$searchClient = new SearchClient();
$connection = new Doctrine\Search\Solr\Connection;
