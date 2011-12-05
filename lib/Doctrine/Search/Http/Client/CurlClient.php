<?php

namespace Doctrine\Search\Http\Client;

use Doctrine\Search\Http\Response\Response;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class CurlClient extends AbstractClient
{
    /**
     * Send a request
     *
     * @param  string            $method   The request method
     * @param  array             $headers  Some http headers
     * @param  string            $body     POST variables
     * @return ResponseInterface
     */
    public function sendRequest($method = 'get', $path = '/', $data = '')
    {
        $url     = sprintf('%s:%s/%s', $this->getOption('host'), $this->getOption('port'), ltrim($path, '/'));
        $options = array();
        list($curlHttpMethod, $curlHttpMethodValue) = $this->getCurlMethod($method);

        $options[CURL_HTTP_VERSION_1_1]  = true;
        $options[CURLOPT_HEADER]         = true;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[$curlHttpMethod]        = $curlHttpMethodValue;

        if ( 'post' === $method || 'put' === $method ) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $rawHeadersString = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $rawHeaders = explode(PHP_EOL, $rawHeadersString);
        $headers    = array();

        foreach ( $rawHeaders as $line ) {
            $line = trim($line);
            if ( preg_match('@^(\w+)\s*(.*)\sHTTP\s*/\s*\d{1}\.\d{1}$@', $line, $matches) ) {
                $headers['method'] = $matches[1];
                $headers['path'] = $matches[2];
            } else if ( preg_match('@^(.*)\s*:\s*(.*)\s*$@', $line, $matches) ) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ( false === $content ) {
            throw new \Exception(sprintf(
                'Request to %s failed (%s)',
                $url,
                curl_error($ch)
            ));
        }

        curl_close($ch);

        return new Response($status, $headers, $content);
    }

    private function getCurlMethod($method)
    {
        $curlMethodValue = true;
        switch (strtoupper($method)) {
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
                throw new \RuntimeException('Method '. strtoupper($method) .' is not supported');
        }

        return array($curlMethod, $curlMethodValue);
    }
}
