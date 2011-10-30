<?php
namespace Doctrine\Search\Http\Response;

use Buzz\Message\Response as BuzzResponse;

use Doctrine\Search\Http\Response;

class Buzz implements Response
{
    private $buzzResponse;

    /**
     * @param \Buzz\Message\Response $response
     */
    public function __construct(BuzzResponse $response)
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
     * @return string
     */
    public function __toString()
    {
        return $this->buzzResponse->__toString();
    }
}