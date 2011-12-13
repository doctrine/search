<?php
namespace Unit\Doctrine\Search;

use Doctrine\Search\SearchManager;
use Doctrine\Search\Http\Client\BuzzClient;
use Doctrine\Search\Configuration;
use Buzz\Browser;
/**
 * Test class for SearchManager.
 * Generated by PHPUnit on 2011-12-11 at 15:43:17.
 */
class SearchManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Doctrine\Search\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var Doctrine\Search\ElasticSearch\Client
     */
    private $searchClient;

    /**
     * @var Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var Doctrine\Search\Configuration
     */
    private $configuration;

    /**
     * @var SearchManager
     */
    protected $sm;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //Prepare the SearchManger's dependencies
        $this->metadataFactory = $this->getMock('Doctrine\\Search\\Mapping\\ClassMetadataFactory');


        $this->searchClient = $this->getMock('Doctrine\\Search\\ElasticSearch\\Client', array(), array(), '', false);

        $this->reader = $this->getMock('Doctrine\\Common\\Annotations\\Reader');


        $this->configuration = $this->getMock('Doctrine\\Search\\Configuration');
        $this->configuration->expects($this->once())
                      ->method('getClassMetadataFactory')
                      ->will($this->returnValue($this->metadataFactory));

        $this->configuration->expects($this->once())
                              ->method('getMetadataCacheImpl')
                              ->will($this->returnValue('Doctrine\\Common\\Cache\\ArrayCache'));


        $this->sm = new SearchManager($this->configuration, $this->searchClient, $this->reader);
    }


    /**
     * Tests if the returned configuration is a Doctrine\\Search\\Configuration
     */
    public function testGetConfiguration()
    {
        $this->assertInstanceOf('Doctrine\\Search\\Configuration', $this->sm->getConfiguration());
    }

    /**
     * Tests if the returned configuration is a Doctrine\\Common\\Annotations\\AnnotationReader
     */
    public function testGetAnnotationReader()
    {
        $this->assertInstanceOf('Doctrine\\Common\\Annotations\\Reader', $this->sm->getAnnotationReader());
    }

    /**
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetObjectManagerWrongParameter()
    {
        $this->sm->setObjectManager(array());
    }


    public function testSetObjectManager()
    {
        $om = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $this->sm->setObjectManager($om);

        $omGet = $this->sm->getObjectManager();

        $this->assertEquals($om, $omGet);
    }

    public function testLoadClassMetadata()
    {
        $this->metadataFactory->expects($this->once())
                               ->method('getMetadataFor')
                               ->will($this->returnValue(new \Doctrine\Search\Mapping\ClassMetadata('Unit\Doctrine\Search\BlogPostInternal')));

        $metaData = $this->sm->loadClassMetadata('Unit\Doctrine\Search\BlogPostInternal');
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\ClassMetadata', $metaData);
    }

    /**
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testLoadClassMetadataWrongParameter()
    {
        $metaData = $this->sm->loadClassMetadata(new \StdClass());
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

use Doctrine\Search\Mapping\Annotations as SEARCH;

/**
 * @SEARCH\ElasticSearchable(index="blog", type="post", numberOfShards=1, numberOfReplicas=1)
 *
 */
class BlogPostInternal
{
    public $id;

    /**
     * @SEARCH\Field(boost=2.0)
     */
    public $name;

    /**
     * @SEARCH\Field(boost=2.0)
     */
    public $title;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}