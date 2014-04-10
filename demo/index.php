<?php 

require_once 'vendor/autoload.php';
require_once 'ElasticSearch.php';

//Get the search manager
$sm = ElasticSearch::get();



//Execute a direct Elastica term search
$query = new Elastica\Filter\Term(array('username' => 'timmyl'));
$users = $sm->getRepository('Entities\User')->search($query);

foreach($users as $user)
{
	print_r($user);
}



//Execute a single term lookup, modify and persist
$user = $sm->getRepository('Entities\User')->findOneBy(array('username' => 'mrhash'));
print_r($user);
$user->setName('New name');
$sm->persist($user);
$sm->flush();



//Execute a single lookup with no results
try 
{
	$user = $sm->find('Entities\User', 'unknownid');
}
catch(Doctrine\Search\Exception\NoResultException $exception)
{
	print_r($exception->getMessage());
} 



//Search for comments with parent user
$query = new Elastica\Filter\HasParent(
	new Elastica\Filter\Term(array('username' => 'mrhash')),
	'users'
);
$comments = $sm->getRepository('Entities\Comment')->search($query);

foreach($comments as $comment)
{
	print_r($comment);
}
