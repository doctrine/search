<?php

namespace Doctrine\Search\Http\Adapter\Client;

use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use Guzzle\Service\Plugin\MockPlugin;

/**
 * @author mtdowling <michael@guzzlephp.org>
 */
class GuzzleTest extends \PHPUnit_Framework_TestCase
{
    public function testCallExistingHost()
    {
        $guzzle = new Client('http://test.com/');
        // Queue up a mock response
        $mock = new MockPlugin();
        $mock->addResponse(new Response(200, null, 'body'));
        $guzzle->getEventManager()->attach($mock);

        $client = new \Doctrine\Search\Http\Client\GuzzleClient('test.com', 80);
        $client->setClient($guzzle);
        $response = $client->sendRequest('get');

        $this->assertInstanceOf('Doctrine\\Search\\Http\\ResponseInterface', $response);
        $this->assertEquals('body', $response->getContent());
        $this->assertTrue($response->isSuccessfull());
    }

    /**
     * @expectedException Exception
     */
    public function testCallNotExistingHost()
    {
        $guzzle = new Client('http://www.test.com/');

        $client = new \Doctrine\Search\Http\Client\GuzzleClient('www.not-existing-host.de', 80);
        $client->setClient($guzzle);

        // Queue up a mock response
        $mock = new MockPlugin();
        $mock->addResponse(new Response(404));
        $guzzle->getEventManager()->attach($mock);

        $client->sendRequest('get');
    }
}
