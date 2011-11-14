<?php

namespace Doctrine\Search\Http\Response;

use Doctrine\Search\Http\Response\GuzzleResponse;
use Guzzle\Http\Message\Response;

/**
 * @author mtdowling <michael@guzzlephp.org>
 */
class GuzzleRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCastsToString()
    {
        $guzzle = new Response(200, null, 'test');
        $response = new GuzzleResponse($guzzle);
        $this->assertEquals("HTTP/1.1 200 OK\r\n\r\ntest", (string) $response);
    }

    public function testIsSuccessful()
    {
        $guzzle = new Response(200, null, 'test');
        $response = new GuzzleResponse($guzzle);
        $this->assertTrue($response->isSuccessfull());

        $guzzle = new Response(401);
        $response = new GuzzleResponse($guzzle);
        $this->assertFalse($response->isSuccessfull());
    }

    public function testGetStatusCode()
    {
        $guzzle = new Response(401);
        $response = new GuzzleResponse($guzzle);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGetContent()
    {
        $guzzle = new Response(200, null, 'test');
        $response = new GuzzleResponse($guzzle);
        $this->assertEquals('test', $response->getContent());
    }
}
