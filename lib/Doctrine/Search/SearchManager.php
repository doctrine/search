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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Search\SearchClientInterface;
use Doctrine\Search\Configuration;
use Doctrine\Search\Exception\UnexpectedTypeException;
use Doctrine\Search\Exception\InvalidHydrationModeException;
use Doctrine\Search\EntityRepository;
use Doctrine\Search\UnitOfWork;
use Doctrine\Search\Query;
use Doctrine\Common\EventManager;

/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class SearchManager implements ObjectManager
{
    /**
     * @var SearchClientInterface
     */
    private $client;

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * @var Doctrine\Search\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /** 
     * @var ObjectManager 
     */
    private $entityManager;
    
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
     * @param SearchClientInterface $client
     */
    public function __construct(Configuration $config, SearchClientInterface $client, EventManager $eventManager)
    {
        $this->configuration = $config;
        $this->client = $client;
        $this->eventManager = $eventManager;

        $this->metadataFactory = $this->configuration->getClassMetadataFactory();
        $this->metadataFactory->setSearchManager($this);
        $this->metadataFactory->setConfiguration($this->configuration);
        $this->metadataFactory->setCacheDriver($this->configuration->getMetadataCacheImpl());

        $this->serializer = $this->configuration->getEntitySerializer();
        $this->entityManager = $this->configuration->getEntityManager();
        
        $this->unitOfWork = new UnitOfWork($this);
    }

    /**
     * Inject a Doctrine 2 object manager
     *
     * @param ObjectManager $em
     */
    public function setEntityManager(ObjectManager $om)
    {
        $this->entityManager = $om;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
     * @return Doctrine\Search\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * @return SearchClientInterface
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
     * @return Doctrine\Search\Mapping\ClassMetadataFactory
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
        return $this->unitOfWork->load($entityName, $id);
    }

    /**
     * Adds the object to the index
     *
     * @param object $object
     *
     * @throws UnexpectedTypeException
     */
    public function persist($object)
    {
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $this->unitOfWork->persist($object);
    }

    /**
     * Remove the object from the index
     *
     * @param object $object
     *
     * @throws UnexpectedTypeException
     */
    public function remove($object)
    {
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $this->unitOfWork->remove($object);
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
     * Returns a search engine Query wrapper which can be executed
     * to retrieve results.
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
