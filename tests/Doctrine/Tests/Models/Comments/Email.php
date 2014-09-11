<?php

namespace Doctrine\Tests\Models\Comments;

use JMS\Serializer\Annotation as JMS;

class Email
{
    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"privateapi", "store"})
     */
    private $email;

    /**
     * @JMS\Type("DateTime")
     * @JMS\Expose @JMS\Groups({"privateapi", "store"})
     */
    private $createdAt;

    public function __construct($email)
    {
        $this->email = $email;
        $this->createdAt = new \DateTime('now');
    }
}
