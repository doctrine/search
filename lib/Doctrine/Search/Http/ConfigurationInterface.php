<?php
namespace Doctrine\Search\Http;

interface ConfigurationInterface
{
    public function setConfig(array $config);
    
    public function setUserAgent();
    
    public function getUserAgent();
    
    public function setTimeOut();
    
    public function getTimeOut();
    
}