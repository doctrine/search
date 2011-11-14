<?php

namespace Doctrine\Search\Http\Request;

use Doctrine\Search\Http\Request\GuzzleRequest;
use Guzzle\Http\Message\RequestFactory;
use Guzzle\Service\Client;
use Guzzle\Service\Plugin\MockPlugin;

/**
 * @author mtdowling <michael@guzzlephp.org>
 */
class GuzzleRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCastsToString()
    {
        $request = new GuzzleRequest(RequestFactory::get('http://test.com/'));
        $this->assertContains("GET / HTTP/1.1\r\n", (string) $request);
    }

    public function testRetreivesUrl()
    {
        $request = new GuzzleRequest(RequestFactory::get('http://test.com/'));
        $this->assertEquals('http://test.com/', $request->getUrl());
    }
}
