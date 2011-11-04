<?php
namespace Doctrine\Search\Http\Adapter;

class CurlTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Curl
     */
	protected $adapter;
	
	protected function setUp() {
		$this->adapter = new Curl();

        if ( ! extension_loaded('curl') ) {
            $this->markTestSkipped('Extension cURL is not loaded');
        }
	}

    /**
     * @dataProvider getTestData
     */
    public function testSendData($host, $port, $method, $url, $body)
    {
        $this->adapter->openConnection($host, $port);
        $this->adapter->sendData($method, $url, array(), $body);

        $this->assertContains(200, $this->adapter->readData());
    }

    static public function getTestData()
    {
        return array(
            array('http://www.php.net', 80, 'GET', '/', '')
        );
    }
	
}