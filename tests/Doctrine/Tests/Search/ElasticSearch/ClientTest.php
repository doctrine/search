<?php

namespace Doctrine\Tests\Search\ElasticSearch;

use Doctrine\Search\ElasticSearch\Client;
use Doctrine\Search\Mapping\ClassMetadata;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Elastica\Client|\PHPUnit_Framework_MockObject_MockObject */
    protected $elasticaClient;

    /** @var \Doctrine\Search\ElasticSearch\Client */
    protected $client;

    protected function setUp()
    {
        $this->elasticaClient = $this->getMockBuilder('Elastica\Client')
            ->setMethods(array('getIndex'))
            ->getMock();

        $this->client = new Client($this->elasticaClient);
    }

    public function testFind()
    {
        $index = $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMock();

        $result = $this->getMockBuilder('Elastica\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('comments')
            ->will($this->returnValue($index));

        $type = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->setMethods(array('getDocument'))
            ->getMock();

        $index->expects($this->once())
            ->method('getType')
            ->with('comment')
            ->will($this->returnValue($type));

        $document = $this->getMockBuilder('Elastica\Document')
            ->disableOriginalConstructor()
            ->getMock();

        $type->expects($this->once())
            ->method('getDocument')
            ->with('123', array('foo' => 'bar'))
            ->will($this->returnValue($document));

        $class = new ClassMetadata('Doctrine\Tests\Models\Comments\Comment');
        $class->index = 'comments';
        $class->type = 'comment';

        $this->assertSame($document, $this->client->find($class, '123', array('foo' => 'bar')));
    }

    public function testCreateIndex()
    {
        $index = $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $index->expects($this->once())
            ->method('create')
            ->with(array('foo' => 'bar'), true);

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('comments')
            ->will($this->returnValue($index));

        $this->client->createIndex('comments', array('foo' => 'bar'));
    }

    public function testDeleteIndex()
    {
        $index = $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->setMethods(array('delete'))
            ->getMock();

        $index->expects($this->once())
            ->method('delete');

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('comments')
            ->will($this->returnValue($index));

        $this->client->deleteIndex('comments');
    }
}
