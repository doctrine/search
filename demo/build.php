<?php 

require_once 'vendor/autoload.php';
require_once 'ElasticSearch.php';

use Entities\User;
use Entities\Email;

$sm = ElasticSearch::get();

$client = $sm->getClient();
$metadatas = $sm->getMetadataFactory()->getAllMetadata();

// Delete indexes
foreach($metadatas as $metadata)
{
	if($client->getIndex($metadata->index)->exists())
	{
		$client->deleteIndex($metadata->index);
	}
}

// Recreate indexes and types
foreach($metadatas as $oMetadata)
{
	if(!$client->getIndex($metadata->index)->exists())
	{
		$client->createIndex($metadata->index);
	}
	$client->createType($metadata);
}


//Install fixtures here... can use Doctrine/data-fixtures package with
//special SearchManager adapter if required.
$user1 = new User();
$user1->setName('Hash');
$user1->setUsername('mrhash');
$user1->addEmail(new Email('user1@example.com'));

$user2 = new User();
$user2->setName('Timothy Leary');
$user2->setUsername('timmyl');
$user2->addEmail(new Email('user2@example.com'));
$user2->addEmail(new Email('user2@test.com'));
$user2->addFriend($user1);

$sm->persist(array($user1, $user2));
$sm->flush();