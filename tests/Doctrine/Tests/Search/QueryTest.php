<?php

namespace Doctrine\Tests\Search;

use Doctrine\Common\EventManager;
use Doctrine\Search\Query;
use Doctrine\Tests\SearchMocks\ResultDocumentMock;
use Doctrine\Search\SearchManager;
use Doctrine\Tests\Models\Blog\BlogPost;
use Doctrine\Tests\Models\Comments\User;

class QueryTest extends \PHPUnit_Framework_TestCase
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
     * @var \Doctrine\Search\SearchManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sm;

    /**
     * @var \Doctrine\Search\Mapping\ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var EventManager
     */
    private $evm;

    /**
     * @var \Doctrine\Search\Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_query;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->metadataFactory = $this->getMock('Doctrine\\Search\\Mapping\\ClassMetadataFactory');

        $this->metadataMock = $this->getMockBuilder('Doctrine\\Search\\Mapping\\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->index = 'testIndex';

        $this->searchClient = $this->getMockBuilder('Doctrine\\Search\\ElasticSearch\\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configuration = $this->getMock('Doctrine\\Search\\Configuration');

        $this->configuration->expects($this->once())
            ->method('getClassMetadataFactory')
            ->will($this->returnValue($this->metadataFactory));

        $this->configuration->expects($this->once())
            ->method('getEntitySerializer')
            ->will($this->returnValue($this->getMockBuilder('Doctrine\\Search\\Serializer\\JMSSerializer')
                ->disableOriginalConstructor()
                ->getMock()));

        $this->configuration->expects($this->once())
            ->method('getMetadataCacheImpl')
            ->will($this->returnValue($this->getMock('Doctrine\\Common\\Cache\\ArrayCache')));

        $this->evm = new EventManager();

        $this->sm = new SearchManager($this->configuration, $this->searchClient, $this->evm);

        $this->_query = new Query($this->sm);
    }

    public function testFrom()
    {
        $classes = array(BlogPost::class, User::class);
        $this->assertInstanceOf(Query::class, $this->_query->from($classes));
        $this->assertEquals($classes, $this->_query->getFrom());
    }

    public function testAddFrom()
    {
        $class = User::class;
        $this->assertInstanceOf(Query::class, $this->_query->addFrom(User::class));
        $this->assertEquals(array($class), $this->_query->getFrom());
    }

    public function testSearchWith()
    {
        $query = array('query' => 'match_all');
        $this->assertInstanceOf(Query::class, $this->_query->searchWith($query));
    }

    public function testSetHydrationMode()
    {
        $hydrationMode = Query::HYDRATE_BYPASS;
        $this->assertInstanceOf(Query::class, $this->_query->setHydrationMode($hydrationMode));
        $this->assertEquals($hydrationMode, $this->_query->getHydrationMode());
    }

    public function testUseResultCache()
    {
        $useCache = true;
        $cacheTtl = 124000;
        $resultArray = array(
            'useResultCache' => $useCache,
            'cacheLifetime' => $cacheTtl,
        );
        $this->assertInstanceOf(Query::class, $this->_query->useResultCache($useCache, $cacheTtl));
        $this->assertEquals($resultArray, $this->_query->getResultCache());
    }

    public function testHydrateWith()
    {
        $hydrationQuery = new Query($this->sm);
        $hydrationParameter = Query::HYDRATION_PARAMETER;
        $this->assertInstanceOf(Query::class, $this->_query->hydrateWith($hydrationQuery, $hydrationParameter));
    }

    public function testGetSingleResult()
    {
        $queryMock = $this->getMockBuilder('Doctrine\\Search\\Query')
            ->disableOriginalConstructor()
            ->getMock();
        $resultSetMock = $this->getMockBuilder('Elastica\\ResultSet')
            ->setMethods(array('getTotalHits', 'getFacets', 'getResults'))
            ->getMock();
        $resultSetMock->expects($this->once())
            ->method('getTotalHits')
            ->willReturn(2);
        $resultSetMock->expects($this->once())
            ->method('getFacets')
            ->willReturn('facets');
        $resultDoc = new ResultDocumentMock();
        $resultDoc2 = new ResultDocumentMock();
        $resultDoc->setId(1);
        $resultDoc2->setId(2);
        $result = array($resultDoc, $resultDoc2);
        $resultSetMock->expects($this->once())
            ->method('getResults')
            ->willReturn($result);
        $this->searchClient->expects($this->once())
            ->method('search')
            ->willReturn($resultSetMock);

        $this->_query->searchWith($queryMock);
        $this->_query->addFrom(BlogPost::class);
        $this->_query->getResult(Query::HYDRATE_BYPASS);
    }
}
