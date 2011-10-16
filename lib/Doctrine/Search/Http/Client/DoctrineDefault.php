<?php
namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\Client;
use Doctrine\Search\Http\Adapter as ConnectionAdapter;

class DoctrineDefault implements Client
{
    private $adapter;
    
    private $host;
    
    private $url;
    
    private $port;
    
    public function __construct(ConnectionAdapter $adapter, $host, $url, $port = 80)
    {
        $this->setHost($host);
        $this->setPort($port);
        $this->setUrl($url);
        $this->adapter = $adapter;
    }
    
    public function setHost($host)
    {
        $this->host = $host;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
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
        //@todo: Try-Catch the Adapter-Exceptions
        $this->adapter->openConnection($this->host, $this->port);
        $this->adapter->sendData($method, $this->url, $headers, $body);
    }

}