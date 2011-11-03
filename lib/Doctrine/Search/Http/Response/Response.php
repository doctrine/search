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
     * @param integer $statusCode
     * @param string  $body
     */
    public function __construct($statusCode, $body)
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
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
