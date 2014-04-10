<?php

namespace Entities;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;

/**
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(index="searchdemo", type="comments", source=true, parent="users")
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
	 * @MAP\ElasticField(type="string", includeInAll=false, index="no")
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
		if(!$this->id) $this->id = uniqid();
		return $this->id;
	}
	
	public function setParent($parent)
	{
		$this->_parent = $parent;
	}
}