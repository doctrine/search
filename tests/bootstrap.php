<?php
require_once __DIR__ . '/../lib/vendor/Buzz/lib/Buzz/ClassLoader.php';
require_once __DIR__ . '/../lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

// use statements
use Doctrine\Common\ClassLoader;

$loader = new ClassLoader('Doctrine\\Common', __DIR__ . '/../lib/vendor/doctrine-common');
$loader->register();
$loader = new ClassLoader('Doctrine\\Search', __DIR__ . '/../lib');
$loader->register();

Buzz\ClassLoader::register();