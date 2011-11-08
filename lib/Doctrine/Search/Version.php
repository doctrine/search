<?php
namespace Doctrine\Search;

final class Version {

    /**
     * @var string
     */
    static $version = '0.1 alpha';

    /**
     * Compare a given version with the current version
     *
     * @param string $version
     * @param string $operator
     * @return mixed
     */
    static public function compare($version, $operator = '<')
    {
        return version_compare(static::$version, $version, $operator);
    }

}
