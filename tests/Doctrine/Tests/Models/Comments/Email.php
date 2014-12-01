<?php

namespace Doctrine\Tests\Models\Comments;

class Email
{
    private $email;

    private $createdAt;

    public function __construct($email)
    {
        $this->email = $email;
        $this->createdAt = new \DateTime('now');
    }
}
