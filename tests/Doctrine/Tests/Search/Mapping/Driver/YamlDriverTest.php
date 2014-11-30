<?php

namespace Doctrine\Tests\Search\Mapping\Driver;

use Doctrine\Search\Mapping\Driver\YamlDriver;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Tests\Models\Comments\User;

/**
 * Test class for YamlDriver.
 */
class YamlDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Search\Mapping\Driver\YamlDriver
     */
    private $yamlDriver;

    /**
     * @var \Doctrine\Search\Mapping\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $classMetadata;

    /**
     * @var \ReflectionClass|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reflectionClass;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Yaml\Yaml', true)) {
            $this->markTestSkipped('This test requires the Symfony YAML component');
        }
        
        $this->yamlDriver = new YamlDriver(__DIR__ . DIRECTORY_SEPARATOR . 'files');
    }

    public function testLoadMetadataForClass()
    {
        $className = __NAMESPACE__.'\YamlAlternateUser';
        $class = new ClassMetadata($className);
        $metadata = $this->yamlDriver->loadMetadataForClass($className, $class);
        
        $expected = new ClassMetadata($className);
        $expected->type = 'users';
        $expected->identifier = 'id';
        $expected->index = 'searchdemo';
        $expected->numberOfShards = 2;
        $expected->numberOfReplicas = 1;
        $expected->timeToLive = 180;
        $expected->boost = 2.0;
        $expected->source = true;
        
        $expected->addRootMapping(array(
            'name' => 'dynamic_templates',
            'id' => 'template_2',
            'match' => 'description*',
            'mapping' => array(
                array(
                    'name' => '{name}',
                    'type' => 'string',
                    'includeInAll' => false
                ),
                array(
                    'name' => 'untouched',
                    'type' => 'string',
                    'analyzer' => 'not_analyzed'
                )
            )
        ));
        
        $expected->addRootMapping(array(
            'name' => 'date_detection',
            'value' => false
        ));
        
        $expected->mapField(array(
            'fieldName' => 'name',
            'type' => 'string',
            'includeInAll' => false,
            'index' => 'no',
            'boost' => 2.0
        ));
        
        $expected->mapField(array(
            'fieldName' => 'username',
            'type' => 'multi_field',
            'fields' => array(
                array(
                    'fieldName' => 'username',
                    'type' => 'string',
                    'includeInAll' => true,
                    'analyzer' => 'whitespace'
                ),
                array(
                    'fieldName' => 'username.term',
                    'type' => 'string',
                    'includeInAll' => false,
                    'analyzer' => 'not_analyzed'
                )
            )
        ));
        
        $expected->mapField(array(
            'fieldName' => 'ip',
            'type' => 'ip',
            'includeInAll' => false,
            'index' => 'no',
            'store' => true,
            'nullValue' => '127.0.0.1'
        ));
        
        $expected->mapField(array(
            'fieldName' => 'emails',
            'type' => 'nested',
            'properties' => array(
                 array(
                     'fieldName' => 'email',
                     'type' => 'string',
                     'includeInAll' => false,
                     'analyzer' => 'not_analyzed'
                 ),
                 array(
                     'fieldName' => 'createdAt',
                     'type' => 'date'
                 )
             )
        ));
        
        $this->assertEquals($expected, $metadata);
    }
}

class YamlAlternateUser extends User
{
    private $id;
    private $name;
    private $username;
    private $ip;
    private $friends = array();
    private $emails = array();
    private $active;
}
