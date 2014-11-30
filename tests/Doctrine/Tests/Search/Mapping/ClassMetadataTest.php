<?php

namespace Doctrine\Tests\Search\Mapping;

use Doctrine\Common\Persistence\Mapping\RuntimeReflectionService;
use Doctrine\Search\Mapping\ClassMetadata;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ReflectionService
     */
    protected $reflectionService;

    protected function setUp()
    {
        $this->classMetadata = new ClassMetadata('Doctrine\Tests\Models\Blog\BlogPost');
        $this->reflectionService = new RuntimeReflectionService();
    }

    public function test__sleep()
    {
        // removed className, because it is set by constructor
        $fields = array(
            'boost',
            'index',
            'numberOfReplicas',
            'numberOfShards',
            'parent',
            'timeToLive',
            'type',
            'value',
        );

        //fill the metadata fields
        foreach ($fields as $field) {
            $this->classMetadata->$field = 1;
        }

        $this->classMetadata->fieldMappings = array();

        $serializedClass = serialize($this->classMetadata);
        $unserializedClass = unserialize($serializedClass);
        $unserializedClass->wakeupReflection($this->reflectionService);

        $this->assertEquals($unserializedClass, $this->classMetadata);
    }

    public function test__wakeup()
    {
        $serializedClass = serialize($this->classMetadata);
        $unserializedClass = unserialize($serializedClass);
        $unserializedClass->wakeupReflection($this->reflectionService);

        $this->assertEquals($unserializedClass, $this->classMetadata);
    }

    public function testGetName()
    {
        $this->assertEquals('Doctrine\Tests\Models\Blog\BlogPost', $this->classMetadata->getName());
    }

    public function testGetIdentifier()
    {
        $this->assertNull($this->classMetadata->getIdentifier());
    }

    public function testGetReflectionClass()
    {
        $this->assertInstanceOf('ReflectionClass', $this->classMetadata->getReflectionClass());
    }

    public function testIsIdentifier()
    {
        $this->assertFalse($this->classMetadata->isIdentifier('test'));
    }

    public function testHasField()
    {
        $this->assertFalse($this->classMetadata->hasField('testtestasdf'));
    }

    public function testHasAssociation()
    {
        $this->assertFalse($this->classMetadata->hasAssociation('testtestasdf'));
    }

    public function testIsSingleValuedAssociation()
    {
        $this->assertFalse($this->classMetadata->isSingleValuedAssociation('testtestasdf'));
    }

    public function testIsCollectionValuedAssociation()
    {
        $this->assertFalse($this->classMetadata->isCollectionValuedAssociation('testtestasdf'));
    }


    public function testGetAssociationNames()
    {
        $this->assertEquals(array(), $this->classMetadata->getAssociationNames());
    }

    public function testGetTypeOfField()
    {
        $this->markTestIncomplete();
    }

    public function testGetAssociationTargetClass()
    {
        $this->assertInternalType('string', $this->classMetadata->getAssociationTargetClass('name'));
    }

    public function testIsAssociationInverseSide()
    {
        $this->assertInternalType('string', $this->classMetadata->isAssociationInverseSide('name'));
    }

    public function testGetAssociationMappedByTargetField()
    {
        $this->assertInternalType('string', $this->classMetadata->getAssociationMappedByTargetField('name'));
    }
}

