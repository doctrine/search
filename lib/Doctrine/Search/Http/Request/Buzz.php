<?php
namespace Doctrine\Search\Http\Request;

use Buzz\Message\Request as BuzzRequest;

use Doctrine\Search\Http\Request;

class Buzz implements Request
{
    private $request;
    
    public function __construct(BuzzRequest $request)
    {
        $this->request = $request;
    }
    
    public function __toString()
    {
        $this->request->__toString();
    }
    
    public function getUrl()
    {
        $this->request->getUrl();
    }
}