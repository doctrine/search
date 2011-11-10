<?php
namespace Unit\Doctrine\Search\Http\Client\Mocks;

use Buzz\Client\ClientInterface;
use Buzz\Message;

class FileGetContentsMock implements ClientInterface
{
    public function send(Message\Request $request, Message\Response $response)
    {
        throw new \RuntimeException('Just a fake');
    }
}