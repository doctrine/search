<?php
class TestTest
{
    private $config = array('test' => 1);
    
    public function __construct(array $config = array())
    {
        $config = array_merge($this->config, $config);
        
        var_dump($config);
    }
}


$test = new TestTest();

$test2 = new TestTest(array('test' => 2, 'test2' => 3));