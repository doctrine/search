<?php

require_once __DIR__ . '/../lib/vendor/Buzz/lib/Buzz/ClassLoader.php';
require_once __DIR__ . '/../lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

// use statements
use Doctrine\Common\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Buzz\ClassLoader as BuzzAutoloader;

$loader = new ClassLoader('Doctrine\\Common', __DIR__ . '/../lib/vendor/doctrine-common/lib');
$loader->register();
$loader = new ClassLoader('Doctrine\\Search', __DIR__ . '/../lib');
$loader->register();
$loader = new ClassLoader('Unit\\Doctrine\\Search', __DIR__ . '/');
$loader->register();
AnnotationRegistry::registerFile(__DIR__ . '/../lib/Doctrine/Search/Mapping/Annotations/DoctrineAnnotations.php');

BuzzAutoloader::register();

$loader = new ClassLoader('Guzzle', __DIR__ . '/../lib/vendor/Guzzle/src');
$loader->register();
