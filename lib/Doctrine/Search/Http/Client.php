<?php
namespace Doctrine\Search\Http;

interface Client
{
    
    /**
     * 
     * Override the default configuration if needed
     * @param HttpClientConfiguration $config
     */
    public function setConfig(\Doctrine\Search\Http\Configuration $config);
    
    /**
     * 
     * Choose curl, socket or something else as connection method
     * @param HttpClientAdapter $adapter
     */
    public function setAdapter(\Doctrine\Search\Http\Adapter $adapter);
    
    /**
     * 
     * returns a Request;
     */
    public function getRequest();
    
    /**
     * 
     * returns a Response
     */
    public function getResponse();
    
    /**
     * 
     * Sets the Http Client's request method
     * @param String $method
     */
    public function setMethod($method);
    
    /**
     * 
     * Sets the parameters to be sent to the server, based on method choosen.
     * (GET is default)
     * @param array $parameter
     */
    public function setParameters(array $parameter);
    
    public function setHeaders(array $headers);
    
    /**
     * 
     * sends the request to the server
     * 
     */
    public function sendRequest();
}