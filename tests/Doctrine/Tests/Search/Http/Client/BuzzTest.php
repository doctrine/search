<?php
namespace Doctrine\Tests\Search\Http\Adapter\Client;

use Doctrine\Tests\Search\Http\Client\Mocks\FileGetContentsMock;

use Doctrine\Search\Http\Client\BuzzClient;
use Buzz\Browser;

/**
 * @author Bachi
 */
class BuzzTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    private $browser;

    protected function setUp()
    {
        $this->browser = $this->getMock('Buzz\\Browser', array(), array(), '', false);
        $this->client = new BuzzClient($this->browser, 'www.google.de', 80);
    }

    public function testCallExistingHost()
    {
        $buzzResponse = $this->getMockBuilder('Buzz\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $this->browser->expects($this->once())
            ->method('call')
            ->will($this->returnValue($buzzResponse));

        $response = $this->client->sendRequest();
        $this->assertInstanceOf('Doctrine\\Search\\Http\\ResponseInterface', $response);

        //@todo this should be tested in the Response-Test
        /*$this->assertContains('<html>', $response->getContent());
        $this->assertTrue($response->isSuccessfull());*/
    }
}