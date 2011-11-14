<?php

namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\RequestInterface;
use Doctrine\Search\Http\Response\GuzzleResponse as Response;
use Guzzle\Service\ClientInterface;
use Guzzle\Service\Client;
use Guzzle\Http\Url;

class GuzzleClient extends AbstractClient
{
    protected $client;

    public function __construct($host = 'localhost', $port = 80, $username = '', $password = '')
    {
        $scheme = ($this->getOption('port') == 443) ? 'https' : 'http';
        $url = new Url($scheme, $host, $username ?: null, $password ?: null, $port);
        $this->setClient(new Client((string) $url));
        parent::__construct($host, $port, $username, $password);
    }

    /**
     * You can overwrite the default client if needed
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send a request
     *
     * @param  string            $method   The request method
     * @param  array             $headers  Some http headers
     * @param  string            $body     POST variables
     * @return ResponseInterface
     */
    public function sendRequest($method = RequestInterface::METHOD_GET, $path = '/', $data = '')
    {
        $headers = array();
        $headers['Connection'] = (true === $this->getOption('keep-alive') ? 'Keep-Alive' : 'Close');

        $response = $this->client->createRequest($method, (string) $path, $headers, $data)->send();

        return new Response($response);
    }
}
