<?php
namespace Doctrine\Search\Http\Response;

use Buzz\Message\Response;
use Doctrine\Search\Http\ResponseInterface;

class BuzzResponse implements ResponseInterface
{
    private $buzzResponse;

    /**
     * @param \Buzz\Message\Response $response
     */
    public function __construct(Response $response)
    {
        $this->buzzResponse = $response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->buzzResponse->getStatusCode();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->buzzResponse->getContent();
    }

    /**
     * @return bool
     */
    public function isSuccessfull()
    {
        return 200 === $this->buzzResponse->getStatusCode();
    }

    /**
     * Get a header
     *
     * @return string
     */
    public function getHeader($key)
    {
        return $this->buzzResponse->getHeader($key);
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->buzzResponse->getHeaders();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->buzzResponse->__toString();
    }
}