<?php
namespace Doctrine\Search\Http;


interface ClientInterface
{
    public function getRequest();
    
    public function getResponse();
    
    public function sendRequest($method = 'GET', $headers = array(), $body = '');
    
    public function setHost($host);
    
    public function setPort($port);
    
    public function setUrl($url);

}