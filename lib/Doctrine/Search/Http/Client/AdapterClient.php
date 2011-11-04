<?php
namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\AdapterInterface;
use Doctrine\Search\Http\Response\Response;

class AdapterClient extends AbstractClient {

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    public function __construct(AdapterInterface $adapter, $host = 'localhost', $port = 80, $username = '', $password = '')
    {
        $this->adapter = $adapter;
        parent::__construct($host, $port, $username, $password);
    }

    /**
     * Set adapter
     *
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * Send a request
     *
     * @param  string            $method   The request method
     * @param  string            $path     Request path
     * @param  string            $body     Raw POST variables
     * @return ResponseInterface
     */
    public function sendRequest($method, $path, $data)
    {
        $host = $this->getOption('host');
        $port = $this->getOption('port');
        $headers = array();

        $headers['Connection'] = $this->getOption('keep-alive') ? 'Keep-Alive' : 'Close';

        $username = $this->getOption('username');
        $password = $this->getOption('password');

        if ( null === $username && null !== $password ) {
            $headers['Authorization'] = sprintf('Basic: %s', base64_encode($username.':'.$password));
        }

        $this->adapter->openConnection($host, $port);
        $this->adapter->sendData($method, $headers, $path, $data);

        $body = $this->adapter->readData();

        return new Response(200, $body);
    }
}
