<?php

namespace Doctrine\Tests\Search;
use Doctrine\Search\Version;

class VersionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider getVersions
     */
    public function testCompareVersion($version, $expected)
    {
        $this->assertEquals($expected, Version::compare($version));
    }

    static public function getVersions()
    {
        return array(
            array('1.0', 1),
            array('0', -1),
            array('-1', -1),
            array('0.1-alpha', 0),
            array('0.1', 1),
            array('0.1-beta', 1),
        );
    }

}
