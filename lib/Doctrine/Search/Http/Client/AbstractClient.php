<?php
namespace Doctrine\Search\Http\Client;

use \Doctrine\Search\Http\ClientInterface;

abstract class AbstractClient implements ClientInterface
{

    protected $options = array(
        'host' => 'localhost',
        'port' => '80',
        'timeout' => 10,
        'keep-alive' => true,
        'username' => '',
        'password' => '',
    );

    public function __construct($host = 'localhost', $port = 80, $username = '', $password = '')
    {
        $this->setOption('host', rtrim($host, '/'));
        $this->setOption('port', $port);
        $this->setOption('username', $username);
        $this->setOption('password', $password);
    }

    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            throw new Exception(sprintf('The option %s is not available', $key));
        }

        return $this->options[$key];
    }

    public function setOption($key, $value)
    {
        if (!isset($this->options[$key])) {
            throw new Exception(sprintf('The option %s does not exist', $key));
        }

        $this->options[$key] = $value;
    }

}
