<?php
namespace Doctrine\Search\Http\Adapter\Client;

/**
 * @author Bachi
 */
class BuzzTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    
    protected function setUp()
    {
        $this->client = new \Doctrine\Search\Http\Client\BuzzClient('www.google.de', 80);
    }
    
    public function testCallExistingHost()
    {
        
        $response = $this->client->sendRequest();
        $this->assertInstanceOf('Doctrine\\Search\\Http\\ResponseInterface', $response);
        $this->assertContains('<html>', $response->getContent());
        $this->assertTrue($response->isSuccessfull());
    }

    /**
     * 
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetBrowserError()
    {
        $this->client->setBrowser(array());
    }
    

    public function testCallNotExistingHost()
    {
        $this->setExpectedException('\RuntimeException');
        $client = new \Doctrine\Search\Http\Client\BuzzClient('www.not-existing-host.de', 80);
        $client->sendRequest('get');
    }

}