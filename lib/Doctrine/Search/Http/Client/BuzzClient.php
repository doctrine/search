<?php
namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\Response\BuzzResponse;
use Doctrine\Search\Http\Request\BuzzRequest;
use Doctrine\Search\Http\ClientInterface;
use Doctrine\Search\Http\RequestInterface;
use Buzz\Browser;

class BuzzClient implements ClientInterface
{
    private $browser;
    
    private $host;
    
    private $url;
    
    private $port;
    
    private $request;
    
    private $response;
    
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
        return $this->request;
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::getResponse()
     */
    public function getResponse() 
    {
        return $this->response;
    }

    /* (non-PHPdoc)
     * @see Doctrine\Search\Http.Client::sendRequest()
     */
    public function sendRequest($method = RequestInterface::METHOD_GET, array $headers = array(), $body = '')
    {
        $method = strtolower($method);

        if ( false === in_array($method, array(RequestInterface::METHOD_GET, RequestInterface::METHOD_POST, RequestInterface::METHOD_HEAD, RequestInterface::METHOD_DELETE, RequestInterface::METHOD_PUT)) ) {
            throw new Exception(sprintf('The request method %s is invalid', $method));
        }

        if ( in_array($method, array(RequestInterface::METHOD_DELETE, RequestInterface::METHOD_POST, RequestInterface::METHOD_PUT)) ) {
           $response = $this->browser->$method($this->host . ':' . $this->port . '/' . $this->url, $body);
        } else {
            $response = $this->browser->$method($this->host . ':' . $this->port . '/' . $this->url);
        }

        if ( $response->getStatusCode() >= 400 ) {
            $this->response = new BuzzErrorResponse($response);
        } else {
            $this->response = new BuzzResponse($response);
        }
        
        $this->request = new BuzzRequest($this->browser->getJournal()->getLastRequest());
    }

}