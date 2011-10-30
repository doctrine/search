<?php
namespace Doctrine\Search\Http\Adapter;

class CurlTest /* extends \PHPUnit_Framework_TestCase */ {
	
	protected $adapter;
	
	protected function setUp() {
		$this->adapter = new Curl();
	}
	
}