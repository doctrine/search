<?php
namespace Doctrine\Search\Http;

interface Request
{
    public function __toString();
    
    public function getUrl();
}