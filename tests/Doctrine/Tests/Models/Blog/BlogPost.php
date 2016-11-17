<?php

namespace Doctrine\Tests\Models\Blog;

use Doctrine\Search\Mapping\Annotations as MAP;

/**
 * @MAP\ElasticSearchable(index="blog", type="post", numberOfShards=1, numberOfReplicas=1)
 */
class BlogPost
{
    const CLASSNAME = __CLASS__;

    public $id;

    /**
     * @MAP\Field(boost=2.0, type="string")
     */
    public $name;

    /**
     * @MAP\Field(boost=1.0, type="string")
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

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
