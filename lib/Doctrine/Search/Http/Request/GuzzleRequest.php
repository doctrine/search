<?php
namespace Doctrine\Search\Http\Request;

use Guzzle\Http\Message\RequestInterface as GuzzleRequestInterface;
use Doctrine\Search\Http\RequestInterface;

class GuzzleRequest implements RequestInterface
{
    private $request;

    public function __construct(GuzzleRequestInterface $request)
    {
        $this->request = $request;
    }

    public function __toString()
    {
        return (string) $this->request;
    }

    public function getUrl()
    {
        return (string) $this->request->getUrl();
    }
}