<?php
namespace Doctrine\Search\Http\Response;

use \Doctrine\Search\Http\ResponseInterface;

class Response implements ResponseInterface {

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param integer $statusCode
     * @param string  $body
     */
    public function __construct($statusCode, array $headers, $body)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get a header
     *
     * @return string
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }


    /**
     * Get the statuscode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isSuccessfull()
    {
        return 200 === $this->getStatusCode();
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->body;
    }

}
