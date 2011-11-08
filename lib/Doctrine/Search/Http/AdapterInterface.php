<?php
namespace Doctrine\Search\Http;

interface AdapterInterface
{
    /**
     * Sets the config
     *
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * Connect to the server
     *
     * @param string $host
     * @param int $port
     *
     * @throw AdapterExecutionException
     *
     * @return void
     */
    public function connect($host, $port = 80);

    /**
     * Check if the adapter is connected to the server
     *
     * @return boolean
     */
    public function isConnected();

    /**
     * Fire the request to the server
     *
     * @param string $method  The request method
     * @param string $url     The relative url
     * @param array $headers  Additional headers
     * @param string $body    Request body for post requests
     *
     * @throw AdapterExecutionException If the request invalid
     *
     * @return string The response
     */
    public function request($method, $url, $headers = array(), $body = '');

    /**
     *
     * @return String $data;
     */
    public function readData();

    /**
     * Close the connection to the server
     */
    public function disconnect();
}