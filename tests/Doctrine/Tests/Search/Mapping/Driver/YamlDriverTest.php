<?php

namespace Doctrine\Tests\Search\Mapping\Driver;

use Doctrine\Search\Mapping\Driver\YamlDriver;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Tests\Models\Comments\User;
use Doctrine\Tests\Models\Comments\SuperUser;

/**
 * Test class for YamlDriver.
 */
class YamlDriverTest extends AbstractDriverTest
{
    /**
     * @var \Doctrine\Search\Mapping\Driver\YamlDriver
     */
    private $yamlDriver;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Yaml\Yaml', true)) {
            $this->markTestSkipped('This test requires the Symfony YAML component');
        }
        
        $this->yamlDriver = new YamlDriver(__DIR__ . DIRECTORY_SEPARATOR . 'files');
    }

    public function testLoadMetadataForClass()
    {
        $className = __NAMESPACE__.'\YamlUser';
        $metadata = new ClassMetadata($className);
        $this->yamlDriver->loadMetadataForClass($className, $metadata);
        
        $expected = $this->loadExpectedMetadataFor($className, 'users');
        
        $this->assertEquals($expected, $metadata);
    }
    
    public function testLoadMetadataForSubClass()
    {
        $className = __NAMESPACE__.'\YamlSuperUser';
        $metadata = new ClassMetadata($className);
        $this->yamlDriver->loadMetadataForClass($className, $metadata);
        
        $expected = $this->loadExpectedMetadataFor($className, 'superusers');
        
        $this->assertEquals($expected, $metadata);
    }
}

class YamlUser extends User
{
}

class YamlSuperUser extends YamlUser
{
}
