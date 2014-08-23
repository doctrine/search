<?php

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

class config
{
    //List of available servers
    public static function getServers()
    {
        return array(
            array('host' => 'localhost', 'port' => 9200)
        );
    }

    //Entities namespace and location
    public static function getEntityNamespacePath()
    {
        return array('namespace' => 'Entities', 'path' => '.');
    }
}
