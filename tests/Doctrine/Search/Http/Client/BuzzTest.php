<?php
namespace Doctrine\Search\Http\Adapter\Client;
/**
 * @author Bachi
 */
 
class BuzzTest extends \PHPUnit_Framework_TestCase {

    public function testCallExistingHost()
    {
        $browser = new \Buzz\Browser();
        $client = new \Doctrine\Search\Http\Client\Buzz($browser, 'google.de', '/', 80);
        $client->sendRequest('GET');
        $response = $client->getResponse();
        $this->assertInstanceOf('Doctrine\\Search\\Http\\Response', $response);
        $this->assertContains('<html>', $response->getContent());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCallNotExistingHost()
    {
        $client = new \Doctrine\Search\Http\Client\Buzz(new \Buzz\Browser(), 'not-existing-host.xyz', '/', 80);
        $client->sendRequest('get');
    }
}
