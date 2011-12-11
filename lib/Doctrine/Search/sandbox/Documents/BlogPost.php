<?php

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Search\Mapping\Annotations as SEARCH;

/**
 * @ODM\Document
 * @SEARCH\ElasticSearchable(index="blog", type="post", numberOfShards=1, numberOfReplicas=1)
 *
 */
class BlogPost
{
    /** @ODM\Id */
    public $id;

    /**
     * @ODM\String
     * @SEARCH\Field(boost=2.0)
     */
    public $name;

    /**
     * @ODM\String
     * @SEARCH\Field(boost=1.0)
     */
    public $title;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}