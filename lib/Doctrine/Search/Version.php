<?php
namespace Doctrine\Search;

final class Version {

    /**
     * @var string
     */
    static $version = '0.1-alpha';

    /**
     * Compare a given version with the current version
     *
     * @param string $version
     * @param string $operator
     * @return int Return -1 if it older, 0 if it the same, 1 if $version is newer
     */
    static public function compare($version)
    {

        return version_compare($version, static::$version);
    }

}
