<?php
namespace Doctrine\Search\Http;

interface RequestInterface
{
    const METHOD_POST   = 'post';
    const METHOD_GET    = 'get';
    const METHOD_PUT    = 'put';
    const METHOD_DELETE = 'delete';
    const METHOD_HEAD   = 'head';
    
    public function __toString();
    
    public function getUrl();
}