<?php

namespace Doctrine\Tests\Models\Comments;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;

/**
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(index="searchdemo", type="comments", source=true, parent="users")
 * @MAP\ElasticRoot(name="dynamic_templates", id="template_1", match="comment*", mapping={
 *		@MAP\ElasticField(type="multi_field", fields={
 *			@MAP\ElasticField(name="{name}", type="string", includeInAll=false),
 *			@MAP\ElasticField(name="untouched", type="string", index="not_analyzed")
 *		})
 * })
 * @MAP\ElasticRoot(name="date_detection", value="false")
 */
class Comment
{
    /**
     * @MAP\Id
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api"})
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api", "store"})
     * @see dynamic template root mapping
     */
    private $comment;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"store"})
     * @MAP\Parameter
     */
    private $_parent;

    public function __construct(User $user, $comment)
    {
        $this->setParent($user->getId());
        $this->comment = $comment;
    }

    public function getId()
    {
        if (!$this->id) {
            $this->id = uniqid();
        }
        return $this->id;
    }

    public function setParent($parent)
    {
        $this->_parent = $parent;
    }
}
