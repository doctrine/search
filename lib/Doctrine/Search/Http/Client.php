<?php
namespace Doctrine\Search\Http;

use Doctrine\Search\Http\Adapter as ConnectionAdapter;

class Client
{
    private $adapter;
    
    private $config;
    
    private $host;
    
    private $url;
    
    private $port;
    
    public function __construct(ConnectionAdapter $adapter, $host, $url, $port = 80)
    {
        $this->host = $host;
        $this->url = $url;
        $this->port = $port;
        $this->adapter = $adapter;
        $this->config = $config;
    }
    
    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::getRequest()
     */
    public function getRequest() 
    {
        return $this->adapter->getRequest();
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::getResponse()
     */
    public function getResponse() 
    {
        return $this->adapter->readData();
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::sendRequest()
     */
    public function sendRequest($method = 'GET', $headers = array(), $body = '') 
    {
        $this->adapter->openConnection($this->host, $this->port);
        $this->adapter->sendData($method, $this->url, $headers, $body);
    }

}