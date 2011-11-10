<?php
namespace Unit\Doctrine\Search\ElasticSearch\Tests;
use Doctrine\Search\ElasticSearch\Client;
use Doctrine\Search\ElasticSearch\Connection;
use Doctrine\Search\Http\Response\BuzzResponse;

class ClientTest extends \PHPUnit_Framework_TestCase 
{
    private $client;

    private $httpClient;

    private $json;
    
    private $connection;
    
    protected function setUp() 
    {
        $this->httpClient = $this->getMock('Doctrine\\Search\\Http\\Client\\BuzzClient', array(), array(), '', false);
        $this->json = '{ 
                            "user": "kimchy", 
                            "postDate": "2009-11-15T14:12:12", 
                            "message": "Another tweet, will it be indexed?" 
                       }';

        $this->client = new Client($this->httpClient);
    }

    /**
     * 
     * @expectedException PHPUnit_Framework_Error
     */
    public function testFindWrongParameter()
    {
        $buzzResponse = $this->getMock('Buzz\Message\Response'); 
        $buzzResponse->expects($this->never())
            ->method('getContent')
            ->will($this->returnValue($this->json));
        
        $mockedResponse = new BuzzResponse($buzzResponse);
        
        $this->httpClient->expects($this->never())
            ->method('sendRequest')
            ->will($this->returnValue($mockedResponse));
            
        $this->client->find(array('query'));
    }
    
    /**
     * @dataProvider getQuery
     */
    public function testFind($query)
    {
        $buzzResponse = $this->getMock('Buzz\Message\Response'); 
        $buzzResponse->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->json));
        
        $mockedResponse = new BuzzResponse($buzzResponse);
        
        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($mockedResponse));
        
        $result = $this->client->find($query);
        $this->assertEquals(json_decode($this->json), $result);
    }
    
    static public function getQuery()
    {
        return array(
            array('http://localhost:9200')
        );
    }
}