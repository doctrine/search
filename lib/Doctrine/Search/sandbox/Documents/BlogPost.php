<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Search\Mapping\Annotations as SEARCH;

/**
 * @ODM\Document
 * @SEARCH\Searchable(index="blog", type="post", numberOfShards=1, numberOfReplicas=1)
 */
class BlogPost
{
    /** @ODM\Id */
    public $id;

    /** @ODM\String */
    public $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}