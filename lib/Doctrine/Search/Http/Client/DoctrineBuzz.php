<?php
namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\ClientInterface;
use Buzz\Browser;

class DoctrineBuzz implements ClientInterface
{
    private $browser;
    
    private $host;
    
    private $url;
    
    private $port;
    
    public function __construct(Browser $browser, $host, $url, $port = 80)
    {
        $this->setHost($host);
        $this->setPort($port);
        $this->setUrl($url);
        $this->browser = $browser;
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
        //return $this->request;
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::getResponse()
     */
    public function getResponse() 
    {
        //return $this->response;
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::sendRequest()
     */
    public function sendRequest($method = 'GET', $headers = array(), $body = '') 
    {
        //@todo: Try-Catch the Adapter-Exceptions
        //$this->browser->openConnection($this->host, $this->port);
        //$this->adapter->sendData($method, $this->url, $headers, $body);
    }

}