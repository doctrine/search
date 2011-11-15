<?php

require_once __DIR__ . '/../../../vendor/Buzz/lib/Buzz/ClassLoader.php';
require_once __DIR__ . '/../../../vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

// use statements
use Doctrine\Common\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Buzz\ClassLoader as BuzzAutoloader;

$loader = new ClassLoader('Doctrine\\Common', __DIR__ . '/../../../vendor/doctrine-common/lib');
$loader->register();
$loader = new ClassLoader('Doctrine\\Search', __DIR__ . '/..');
$loader->register();
$loader = new ClassLoader('Doctrine\\ODM\\MongoDB', __DIR__ . '/vendor/doctrine-mongodb-odm/lib');
$loader->register();
$loader = new ClassLoader('Doctrine\\MongoDB', __DIR__ . '/vendor/doctrine-mongodb/lib');
$loader->register();
$loader = new ClassLoader('Documents', __DIR__ );
$loader->register();
AnnotationRegistry::registerFile(__DIR__ . '/../Mapping/Annotations/DoctrineAnnotations.php');
BuzzAutoloader::register();