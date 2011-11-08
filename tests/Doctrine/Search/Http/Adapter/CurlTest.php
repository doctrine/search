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
    public function testSendData($host, $port, $method, $url, $body, $contains)
    {
        $this->adapter->connect($host, $port);
        $this->adapter->request($method, $url, array(), $body);

        $this->assertContains($contains, $this->adapter->readData(), true);
    }

    static public function getTestData()
    {
        return array(
            array('http://www.google.de', 80, 'GET', '/', '', '<html'),
            array('http://google.de', 80, 'GET', '/', '', '301'),
            array('http://www.php.net', 80, 'GET', '/', '', 'php'),
        );
    }
	
}