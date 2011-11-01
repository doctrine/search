<?php
namespace Doctrine\Search\Http;


interface ClientInterface
{
    /**
     * Get the last request
     *
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Get the last response
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Send a request
     *
     * @param string $method   The request method
     * @param array  $headers  Some http headers
     * @param string $body     POST variables
     */
    public function sendRequest($method, array $headers, $body);

    /**
     * Sets the host
     *
     * @param string $host
     */
    public function setHost($host);

    /**
     * Sets the port
     *
     * @param int $port
     */
    public function setPort($port);

    /**
     * Sets the url
     *
     * @param string $url
     */
    public function setUrl($url);

}