<?php

namespace Doctrine\Tests\Search;

use Doctrine\Common\EventManager;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Tests\Models\Blog\BlogPost;

class SearchManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\Search\Mapping\ClassMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataFactory;

    /**
     * @var \Doctrine\Search\ElasticSearch\Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchClient;

    /**
     * @var \Doctrine\Search\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configuration;

    /**
     * @var SearchManager
     */
    protected $sm;

    /**
     * @var EventManager
     */
    private $evm;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->metadataFactory = $this->getMock('Doctrine\\Search\\Mapping\\ClassMetadataFactory');

        $this->searchClient = $this->getMockBuilder('Doctrine\\Search\\ElasticSearch\\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = $this->getMock('Doctrine\\Search\\Configuration');
        $this->configuration->expects($this->once())
              ->method('getClassMetadataFactory')
              ->will($this->returnValue($this->metadataFactory));

        $this->configuration->expects($this->once())
              ->method('getMetadataCacheImpl')
              ->will($this->returnValue($this->getMock('Doctrine\\Common\\Cache\\ArrayCache')));

        $this->evm = new EventManager();

        $this->sm = new SearchManager($this->configuration, $this->searchClient, $this->evm);
    }

    /**
     * Tests if the returned configuration is a Doctrine\\Search\\Configuration
     */
    public function testGetConfiguration()
    {
        $this->assertInstanceOf('Doctrine\\Search\\Configuration', $this->sm->getConfiguration());
    }

    public function testGetClassMetadata()
    {
        $classMetadata = new ClassMetadata(BlogPost::CLASSNAME);

        $this->metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with('Some\Class')
            ->will($this->returnValue($classMetadata));

        $this->assertEquals($classMetadata, $this->sm->getClassMetadata('Some\Class'));
    }

    public function testGetClassMetadataFactory()
    {
        $mdf = $this->sm->getClassMetadataFactory();
        $this->assertInstanceOf('Doctrine\\Search\\Mapping\\ClassMetadataFactory', $mdf);
    }

    /**
     * @todo Implement testFind().
     */
    public function testFind()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testPersist().
     */
    public function testPersist()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRemove().
     */
    public function testRemove()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBulk().
     */
    public function testBulk()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCommit().
     */
    public function testCommit()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
