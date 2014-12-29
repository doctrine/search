<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Search;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ObjectManager;



/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class SearchManager implements ObjectManager
{
    /**
     * @var SearchClient
     */
    private $client;

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * @var \Doctrine\Search\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * The EntityRepository instances.
     *
     * @var array
     */
    private $repositories = array();

    /**
     * The UnitOfWork used to coordinate object-level transactions.
     *
     * @var \Doctrine\Search\UnitOfWork
     */
    private $unitOfWork;

    /**
     * Constructor
     *
     * @param Configuration         $config
     * @param SearchClient $client
     * @param EventManager          $eventManager
     */
    public function __construct(Configuration $config, SearchClient $client, EventManager $eventManager)
    {
        $this->configuration = $config;
        $this->client = $client;
        $this->eventManager = $eventManager;

        $this->metadataFactory = $this->configuration->getClassMetadataFactory();
        $this->metadataFactory->setSearchManager($this);
        $this->metadataFactory->setTypeMetadataFactory($client);
        $this->metadataFactory->setConfiguration($this->configuration);
        $this->metadataFactory->setCacheDriver($this->configuration->getMetadataCacheImpl());

        $this->serializer = $this->configuration->getEntitySerializer();
        $this->objectManager = $this->configuration->getObjectManager();

        $this->unitOfWork = new UnitOfWork($this);
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Gets the UnitOfWork used by the SearchManager to coordinate operations.
     *
     * @return \Doctrine\Search\UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * Gets the EventManager used by the SearchManager.
     *
     * @return \Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Loads class metadata for the given class
     *
     * @param string $className
     *
     * @return \Doctrine\Search\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * @return Mapping\ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * @return SearchClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return \Doctrine\Search\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function find($entityName, $id)
    {
        $options = array();
        if (is_array($id)) {
            if (!isset($id['id'])) {
                throw new \InvalidArgumentException('An "id" field is required');
            }
            $options = $id;
            $id = $options['id'];
            unset($options['id']);
        }

        $class = $this->getClassMetadata($entityName);
        return $this->unitOfWork->load($class, $id, $options);
    }

    /**
     * Adds the object to the index
     *
     * @param array|object $objects
     *
     * @throws UnexpectedTypeException
     */
    public function persist($objects)
    {
        if (!is_array($objects) && !$objects instanceof \Traversable) {
            $objects = array($objects);
        }
        foreach ($objects as $object) {
            if (!is_object($object)) {
                throw new UnexpectedTypeException($object, 'object');
            }
            $this->unitOfWork->persist($object);
        }
    }

    /**
     * Remove the object from the index
     *
     * @param array|object $objects
     *
     * @throws UnexpectedTypeException
     */
    public function remove($objects)
    {
        if (!is_array($objects) && !$objects instanceof \Traversable) {
            $objects = array($objects);
        }
        foreach ($objects as $object) {
            if (!is_object($object)) {
                throw new UnexpectedTypeException($object, 'object');
            }
            $this->unitOfWork->remove($object);
        }
    }

    /**
     * Commit all changes
     */
    public function flush($object = null)
    {
        $this->unitOfWork->commit($object);
    }

    /**
     * Gets the repository for an entity class.
     *
     * @param string $entityName The name of the entity.
     * @return EntityRepository The repository class.
     */
    public function getRepository($entityName)
    {
        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        $metadata = $this->getClassMetadata($entityName);
        $repository = new EntityRepository($this, $metadata);
        $this->repositories[$entityName] = $repository;

        return $repository;
    }

    /**
     * Returns a search engine Query wrapper which can be executed to retrieve results.
     *
     * @return Query
     */
    public function createQuery()
    {
        return new Query($this);
    }

    public function initializeObject($obj)
    {
    }

    public function contains($object)
    {
    }

    public function merge($object)
    {
    }

    /**
     * Clears the SearchManager. All entities that are currently managed
     * by this EntityManager become detached.
     *
     * @param string $objectName if given, only entities of this type will get detached
     */
    public function clear($objectName = null)
    {
        $this->unitOfWork->clear($objectName);
    }

    public function detach($object)
    {
    }

    public function refresh($object)
    {
    }

}
