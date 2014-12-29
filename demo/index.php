<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ElasticSearch.php';
require_once __DIR__ . '/Pagination/PagerfantaAdapter.php';

//Get the search manager
$sm = ElasticSearch::get();



//Execute a direct Elastica term search
echo PHP_EOL."*** Direct Term Search ***".PHP_EOL;
$query = new Elastica\Filter\Term(array('username' => 'timmyl'));
$users = $sm->getRepository('Doctrine\Tests\Models\Comments\User')->search($query);

foreach ($users as $user) {
    print_r($user);
}



//Execute a single term lookup, modify and persist
echo PHP_EOL."*** Single term lookup, modify and persist ***".PHP_EOL;
$user = $sm->getRepository('Doctrine\Tests\Models\Comments\User')->findOneBy(array('username' => 'mrhash'));
print_r($user);
$user->setName('New name');
$sm->persist($user);
$sm->flush();



//Execute a single lookup with no results
echo PHP_EOL."*** Single lookup with no results ***".PHP_EOL;
try {
    $user = $sm->find('Doctrine\Tests\Models\Comments\User', 'unknownid');
} catch (\Doctrine\Search\NoResultException $exception) {
    print_r($exception->getMessage());
    echo PHP_EOL;
}



//Search for comments with parent user. Because of the way ES returns
//results, you have to explicitly ask for the _parent or _routing field if required.
//On single document query e.g. find() the _parent field is returned by ES anyway.
echo PHP_EOL."*** Comments with parent user ***".PHP_EOL;
$query = new Elastica\Query();
$query->setFilter(new Elastica\Filter\HasParent(
    new Elastica\Filter\Term(array('username' => 'mrhash')),
    'users'
));
$query->setFields(array('_source', '_parent'));
$comments = $sm->getRepository('Doctrine\Tests\Models\Comments\Comment')->search($query);

foreach ($comments as $comment) {
    print_r($comment);
}


//Paginated response with Pagerfanta library. In this case the Doctrine\Search\Query
//wrapper provides a mechanism for specifying the query but it should be possible to
//pass an Elastica query directly into a modified pagination adapter.
echo PHP_EOL."*** Pagerfanta paginated results ***".PHP_EOL;
$query = $sm->createQuery()
    ->from('Doctrine\Tests\Models\Comments\Comment')
    ->searchWith(new Elastica\Query())
    ->setQuery(new Elastica\Query\MatchAll())
    ->setFields(['_source', '_parent'])
    ->setHydrationMode(Doctrine\Search\Query::HYDRATE_INTERNAL);

$pager = new Pagerfanta\Pagerfanta(new PagerfantaAdapter($query));
$pager->setAllowOutOfRangePages(true);
$pager->setMaxPerPage(1);
$pager->setCurrentPage(2);
$comments = $pager->getCurrentPageResults();

foreach ($comments as $comment) {
    print_r($comment);
}

echo "Total comments found by query: ".$pager->getNbResults().PHP_EOL;
