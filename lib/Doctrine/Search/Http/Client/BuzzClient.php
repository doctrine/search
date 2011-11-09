<?php
namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\RequestInterface;
use Doctrine\Search\Http\Response\BuzzResponse as Response;
use Buzz\Browser;

class BuzzClient extends AbstractClient
{
    protected $browser;

    public function __construct($host = 'localhost', $port = 80, $username = '', $password = '')
    {
        $this->setBrowser(new Browser());
        parent::__construct($host, $port, $username, $password);
    }
    
    /**
     * You can overwrite the default browser if needed
     * 
     * @param Buzz\Browser $browser
     */
    public function setBrowser(Browser $browser)
    {
        $this->browser = $browser;
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
        $url = $this->getOption('host').':'.$this->getOption('port');
        $headers = array();

        $username = $this->getOption('username');
        $password = $this->getOption('password');

        if ( null !== $username && null !== $password ) {
            $headers['Authorization'] = sprintf('Basic: %s', base64_encode($username.':'.$password));
        }

        $headers['Connection'] = (true === $this->getOption('keep-alive') ? 'Keep-Alive' : 'Close');

        if ( $method === RequestInterface::METHOD_POST ) {
            $response = $this->browser->call($url, $method, $headers, $data);
        } else {
            $response = $this->browser->call($url, $method, $headers);
        }

        return new Response($response);
    }


}