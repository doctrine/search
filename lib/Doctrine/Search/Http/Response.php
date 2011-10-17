<?php
namespace Doctrine\Search\Http;

interface Response
{
    public function __toString();
    
    public function getContent();
}