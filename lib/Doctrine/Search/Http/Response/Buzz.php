<?php
namespace Doctrine\Search\Http\Response;

use Buzz\Message\Response as BuzzResponse;

use Doctrine\Search\Http\Response;

class Buzz implements Response
{
    private $buzzResponse;
    
    public function __construct(BuzzResponse $response)
    {
        $this->buzzResponse = $response;
    }
    
    public function getContent()
    {
        return $this->buzzResponse->getContent();
    }
    
    public function __toString()
    {
        return $this->buzzResponse->__toString();
    }
}