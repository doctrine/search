<?php
spl_autoload_register(function ($class) {
	
	if ( 0 !== strpos($class, 'Doctrine\\Search')) {
		return false;
	} 
	
	$path = __DIR__ . '/../lib';
	$file = strtr($class, '\\', '/') . '.php';
	$filename = $path . '/' . $file;
	
	if ( file_exists($filename) ) {
		return (Boolean) require_once $filename;
	}
	
	return false;
} );

require_once __DIR__ . '/../lib/vendor/Buzz/lib/Buzz/ClassLoader.php';
Buzz\ClassLoader::register();