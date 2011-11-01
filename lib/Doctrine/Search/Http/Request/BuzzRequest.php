<?php
namespace Doctrine\Search\Http\Request;

use Buzz\Message\Request;
use Doctrine\Search\Http\RequestInterface;

class BuzzRequest implements RequestInterface
{
    private $request;
    
    public function __construct(Request $request)
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