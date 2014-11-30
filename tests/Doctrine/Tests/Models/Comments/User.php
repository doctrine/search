<?php

namespace Doctrine\Tests\Models\Comments;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;

/**
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(index="searchdemo", type="users", source=true, numberOfShards=2, numberOfReplicas=1)
 * @MAP\ElasticRoot(name="dynamic_templates", id="template_2", match="description*", mapping={
 *		@MAP\ElasticField(type="multi_field", fields={
 *			@MAP\ElasticField(name="{name}", type="string", includeInAll=false),
 *			@MAP\ElasticField(name="untouched", type="string", analyzer="not_analyzed")
 *		})
 * })
 * @MAP\ElasticRoot(name="date_detection", value="false")
 */
class User
{
    /**
     * @MAP\Id
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api"})
     *
     * Using Serialization groups allows us to provide a version of serialized object
     * for storage, and a different one for passing into a document output renderer, such
     * as might be useful for an api.
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api", "store"})
     * @MAP\ElasticField(type="string", includeInAll=false, index="no", boost=2.0)
     */
    private $name;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api", "store"})
     * @MAP\ElasticField(type="multi_field", fields={
     *    @MAP\ElasticField(name="username", type="string", includeInAll=true, analyzer="whitespace"),
     *    @MAP\ElasticField(name="username.term", type="string", includeInAll=false, index="not_analyzed")
     * })
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api", "store"})
     * @MAP\ElasticField(type="ip", includeInAll=false, index="no", store=true, nullValue="127.0.0.1")
     */
    private $ip;

    /**
     * @JMS\Type("string")
     * @JMS\Expose @JMS\Groups({"api", "store"})
     * @see dynamic template root mapping
     */
    private $description;
    
    /**
     * @JMS\Type("array")
     * @JMS\Expose @JMS\Groups({"store"})
     * @MAP\ElasticField(type="string", includeInAll=false, index="not_analyzed")
     */
    private $friends = array();

    /**
     * @JMS\Type("array<Doctrine\Tests\Models\Comments\Email>")
     * @JMS\Expose @JMS\Groups({"privateapi", "store"})
     * @MAP\ElasticField(type="nested", properties={
     *    @MAP\ElasticField(name="email", type="string", includeInAll=false, index="not_analyzed"),
     *    @MAP\ElasticField(name="createdAt", type="date")
     * })
     */
    private $emails = array();

    /**
     * @JMS\Type("boolean")
     * @JMS\Expose @JMS\Groups({"store"})
     * @MAP\ElasticField(type="boolean", nullValue=false)
     */
    private $active;

    public function getId()
    {
        if (!$this->id) {
            $this->id = uniqid();
        }
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getFriends()
    {
        return $this->friends;
    }

    public function addFriend(User $user)
    {
        if (!in_array($user->getId(), $this->friends)) {
            $this->friends[] = $user->getId();
        }
    }

    public function addEmail(Email $email)
    {
        $this->emails[] = $email;
    }
}
