<?php

namespace Doctrine\Tests\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\Search\Query;
use Doctrine\Tests\SearchMocks\ResultDocumentMock;
use Doctrine\Search\SearchManager;
use Doctrine\Search\UnitOfWork;
use Doctrine\Tests\Models\Blog\BlogPost;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
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
     * @var \Doctrine\Search\UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_unitOfWork;

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

        $this->_unitOfWork = new UnitOfWork($this->sm);
    }

    public function testEntityIsPersisted()
    {
        $blog = new BlogPost();
        $this->_unitOfWork->persist($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForPersist($blog));
    }

    public function testEntityIsDeleted()
    {
        $blog = new BlogPost();
        $this->_unitOfWork->remove($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForDelete($blog));
    }

    public function testClear()
    {
        $blog = new BlogPost();
        $this->_unitOfWork->persist($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForPersist($blog));
        $this->_unitOfWork->clear();
        $this->assertFalse($this->_unitOfWork->isScheduledForPersist($blog));
        $this->_unitOfWork->remove($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForDelete($blog));
        $this->_unitOfWork->clear();
        $this->assertFalse($this->_unitOfWork->isScheduledForDelete($blog));
    }

    public function testCommit()
    {
        $this->metadataMock->index = 'testIndex';
        $this->metadataFactory->expects($this->any())
            ->method('getMetadataFor')
            ->will($this->returnValue($this->metadataMock));
        $blog = new BlogPost('test');
        $blog->setId(1);
        $this->_unitOfWork->persist($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForPersist($blog));
        $this->_unitOfWork->commit();
        $this->assertFalse($this->_unitOfWork->isScheduledForPersist($blog));
        $this->_unitOfWork->remove($blog);
        $this->assertTrue($this->_unitOfWork->isScheduledForDelete($blog));
        $this->_unitOfWork->commit();
        $this->assertFalse($this->_unitOfWork->isScheduledForDelete($blog));
    }

    public function testLoad()
    {
        $result = new ResultDocumentMock();
        $this->searchClient->expects($this->once())
            ->method('findOneBy')
            ->willReturn($result);
        $this->metadataMock->parameters = array(
            'testField1' => 'mapping',
            'testField2' => 'mapping',
            'testField3' => 'mapping',
            '_version' => 'v_1',
        );
        $this->metadataMock->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('123');
        $query = new Query($this->sm);

        $this->_unitOfWork->load($this->metadataMock, $query, array('field' => 'tesfield'));
    }

    public function testLoadCollection()
    {
        $arrayCollection = new ArrayCollection();
        $arrayCollection->add(new ResultDocumentMock());
        $arrayCollection->add(new ResultDocumentMock());
        $this->searchClient->expects($this->any())
            ->method('search')
            ->willReturn($arrayCollection);

        $this->metadataMock->parameters = array(
            'testField1' => 'mapping',
            'testField2' => 'mapping',
            'testField3' => 'mapping',
            '_version' => 'v_1',
        );
        $this->metadataMock->index = 'testIndex';
        $this->metadataMock->index = 'testType';
        $this->metadataMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn('123');
        $query = new Query($this->sm);

        $this->_unitOfWork->loadCollection(array($this->metadataMock), $query);
    }

    public function testIsInIdentityMap()
    {
        $blog = new BlogPost();
        $this->_unitOfWork->persist($blog);
        $this->assertTrue($this->_unitOfWork->isInIdentityMap($blog));
    }
}
