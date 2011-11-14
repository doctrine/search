<?php
namespace Doctrine\Search\Http\Response;

use Guzzle\Http\Message\Response;
use Doctrine\Search\Http\ResponseInterface;

class GuzzleResponse implements ResponseInterface
{
    private $guzzleResponse;

    /**
     * @param Guzzle\Http\Message\Response $response
     */
    public function __construct(Response $response)
    {
        $this->guzzleResponse = $response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->guzzleResponse->getStatusCode();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return (string) $this->guzzleResponse->getBody();
    }

    /**
     * @return bool
     */
    public function isSuccessfull()
    {
        return $this->guzzleResponse->isSuccessful();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->guzzleResponse;
    }
}