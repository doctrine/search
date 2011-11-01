<?php
namespace Doctrine\Search\Http;

interface AdapterInterface
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
    
    public function getRequest();
}