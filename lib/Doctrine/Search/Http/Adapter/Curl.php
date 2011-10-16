<?php
namespace Doctrine\Search\Http\Adapter;

use Doctrine\Search\Http\Adapter;
use Doctrine\Search\Http\Adapter\AdapterInvalidArgumentException;

class Curl implements Adapter
{
    private $config = array('timeout' => 10,
                            'maxredirects' => 3);
    
    private $curlConnection;
    
    private $headers = array('Accept' => '');
    
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new AdapterInitializationException('The cURL PHP extension has to be installed and loaded to use this Doctrine\Http\Client.');
        }
    }
    
    
    public function setConfig(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }
    
    public function openConnection($host, $port = 80)
    {
        $this->curlConnection = curl_init();
        if ($port != 80) {
            curl_setopt($this->curlConnection, CURLOPT_PORT, intval($port));
        }
        
        curl_setopt($this->curlConnection, CURLOPT_CONNECTTIMEOUT, $this->config['timeout']);
        curl_setopt($this->curlConnection, CURLOPT_MAXREDIRS, $this->config['maxredirects']);

        if (!$this->curl) {
            $this->close();

            throw new AdapterExecutionException('Connection to ' .  $host . ':' . $port . 'is impossible.');
        }
    }
    
    public function sendData($method, $url, $headers = array(), $body = '')
    {
        $this->headers = array_merge($this->headers, $headers);
        
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURL_HTTP_VERSION_1_1, true);
        
        $curlMethod = $this->getCurlMethod($method);
        curl_setopt($this->curl, $curlMethod['method'], $curlMethod['methodValue']);
        
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        // ensure actual response is returned
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
        
        if(strtoupper($method) == 'POST')
        {
            
        }
        elseif(strtoupper($method) == 'PUT')
        {
            
        }
    }
    
    private function getCurlMethod($method)
    {
        
        $curlMethodValue = true;
        
        switch(strtoupper($method))
        {
            case 'GET':
                $curlMethod = CURLOPT_HTTPGET;
                break;
            
            case 'POST':
                $curlMethod = CURLOPT_POST;
                break;
                
            case 'PUT':
                 $curlMethod = CURLOPT_CUSTOMREQUEST;
                 $curlMethodValue = "PUT";
                 break;
                 
            case 'DELETE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlMethodValue = "DELETE";
                break;

            case 'OPTIONS' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlMethodValue = "OPTIONS";
                break;

            case 'TRACE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlMethodValue = "TRACE";
                break;
            
            case 'HEAD' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlMethodValue = "HEAD";
                break;
                
            default:
                throw new AdapterInvalidArgumentException('Method '. strtoupper($method) .' is not supported');
                
        }
        
        return array('method' => $curlMethod, 'methodValue' => $curlMethodValue);
    }
    
    /**
     * 
     * @return String $data;
     */
    public function readData()
    {
        
    }
    
    public function closeConnection()
    {
        
    }
}