<?php

require_once 'vendor/autoload.php';
require_once 'ElasticSearch.php';

use Entities\User;
use Entities\Email;
use Entities\Comment;

$sm = ElasticSearch::get();

$client = $sm->getClient();
$metadatas = $sm->getMetadataFactory()->getAllMetadata();

// Delete indexes
foreach($metadatas as $metadata) {
    if($client->getIndex($metadata->index)->exists()) {
        $client->deleteIndex($metadata->index);
    }
}

// Recreate indexes and types
foreach($metadatas as $metadata) {
    if(!$client->getIndex($metadata->index)->exists()) {
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

$comment1 = new Comment($user1, 'comment 1 from user 1');
$comment2 = new Comment($user2, 'comment 1 from user 2');
$comment3 = new Comment($user2, 'comment 2 from user 2');

$sm->persist(array($user1, $user2, $comment1, $comment2, $comment3));
$sm->flush();
