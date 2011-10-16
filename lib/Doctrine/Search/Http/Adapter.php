<?php
namespace Doctrine\Search\Http;

interface Adapter
{
    public function setConfig(array $config);
    
    public function openConnection($host, $port = 80);
    
    public function sendData($method, $url, $headers = array(), $body = '');
    
    /**
     * 
     * @return String $data;
     */
    public function readData();
    
    public function closeConnection();
}