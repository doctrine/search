<?php

namespace Doctrine\Tests\Search\ElasticSearch;

use Doctrine\Search\ElasticSearch\Client;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->elasticaClient = $this->getMock('Elastica_Client');
        $this->client = new Client($this->elasticaClient);
    }

    public function testFind()
    {
        $index = $this->getMockBuilder('Elastica_Index')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->getMockBuilder('Elastica_ResultSet')
            ->disableOriginalConstructor()
            ->getMock();

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('foo')
            ->will($this->returnValue($index));

        $index->expects($this->once())
            ->method('search')
            ->with('foobar')
            ->will($this->returnValue($result));

        $this->assertEquals(array(), $this->client->find('foo', '', 'foobar'));
    }

    public function testCreateIndex()
    {
        $index = $this->getMockBuilder('Elastica_Index')
            ->disableOriginalConstructor()
            ->getMock();

        $index->expects($this->once())
            ->method('create');

        $index->expects($this->once())
            ->method('addDocuments');

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('foo')
            ->will($this->returnValue($index));

        $this->client->createIndex('foo', '', array('foo' => 'bar'));
    }

    public function testDeleteIndex()
    {
        $index = $this->getMockBuilder('Elastica_Index')
            ->disableOriginalConstructor()
            ->getMock();

        $index->expects($this->once())
            ->method('delete');

        $this->elasticaClient->expects($this->once())
            ->method('getIndex')
            ->with('foo')
            ->will($this->returnValue($index));

        $this->client->deleteIndex('foo');
    }
}
