<?php
namespace Doctrine\Search\Http;

interface Configuration
{
    public function setConfig(array $config);
    
    public function setUserAgent();
    
    public function getUserAgent();
    
    public function setTimeOut();
    
    public function getTimeOut();
    
}