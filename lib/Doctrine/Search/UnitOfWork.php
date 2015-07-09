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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Search\Mapping\ClassMetadata;
use Elastica\Document;
use Traversable;



class UnitOfWork
{
    /**
     * The SearchManager that "owns" this UnitOfWork instance.
     *
     * @var \Doctrine\Search\SearchManager
     */
    private $sm;

    /**
     * The EventManager used for dispatching events.
     *
     * @var \Doctrine\Common\EventManager
     */
    private $evm;

    /**
     * @var array
     */
    private $scheduledForPersist = array();

    /**
     * @var array
     */
    private $scheduledForDelete = array();

    /**
     * @var array
     */
    private $updatedIndexes = array();

    /**
     * Initializes a new UnitOfWork instance, bound to the given SearchManager.
     *
     * @param \Doctrine\Search\EntityManager $sm
     */
    public function __construct(SearchManager $sm)
    {
        $this->sm = $sm;
        $this->evm = $sm->getEventManager();
    }

    /**
     * Persists an entity as part of the current unit of work.
     *
     * @param object $entity The entity to persist.
     */
    public function persist($entity)
    {
        if ($this->evm->hasListeners(Events::prePersist)) {
            $this->evm->dispatchEvent(Events::prePersist, new Event\LifecycleEventArgs($entity, $this->sm));
        }

        $oid = spl_object_hash($entity);
        $class = ClassUtils::getRealClass(get_class($entity));
        $this->scheduledForPersist[$class][$oid] = $entity;

        if ($this->evm->hasListeners(Events::postPersist)) {
            $this->evm->dispatchEvent(Events::postPersist, new Event\LifecycleEventArgs($entity, $this->sm));
        }
    }

    /**
     * Deletes an entity as part of the current unit of work.
     *
     * @param object $entity The entity to remove.
     */
    public function remove($entity)
    {
        if ($this->evm->hasListeners(Events::preRemove)) {
            $this->evm->dispatchEvent(Events::preRemove, new Event\LifecycleEventArgs($entity, $this->sm));
        }

        $oid = spl_object_hash($entity);
        $class = ClassUtils::getRealClass(get_class($entity));
        unset($this->scheduledForPersist[$class][$oid]);
        $this->scheduledForDelete[$class][$oid] = $entity;

        if ($this->evm->hasListeners(Events::postRemove)) {
            $this->evm->dispatchEvent(Events::postRemove, new Event\LifecycleEventArgs($entity, $this->sm));
        }
    }

    /**
     * Clears the UnitOfWork.
     *
     * @param string $entityName if given, only entities of this type will get detached
     */
    public function clear($entityName = null)
    {
        if ($entityName === null) {
            $this->scheduledForDelete =
            $this->scheduledForPersist =
            $this->updatedIndexes = array();
        } else {
            throw new NotImplementedException;
        }

        if ($this->evm->hasListeners(Events::onClear)) {
            $this->evm->dispatchEvent(Events::onClear, new Event\OnClearEventArgs($this->sm, $entityName));
        }
    }

    /**
     * Commits the UnitOfWork, executing all operations that have been postponed
     * up to this point.
     *
     * The operations are executed in the following order:
     *
     * 1) All entity inserts
     * 2) All entity deletions
     *
     * @param null|object|array $entity
     */
    public function commit($entity = null)
    {
        if ($this->evm->hasListeners(Events::preFlush)) {
            $this->evm->dispatchEvent(Events::preFlush, new Event\PreFlushEventArgs($this->sm));
        }

        if (is_object($entity)) {
            throw new NotImplementedException;

        } elseif (is_array($entity)) {
            throw new NotImplementedException;

        } else {
            $this->commitRemoved();
            $this->commitPersisted();
        }

        $client = $this->sm->getClient();
        foreach (array_unique($this->updatedIndexes) as $index) {
            $client->refreshIndex($index);
        }

        $this->clear();

        if ($this->evm->hasListeners(Events::postFlush)) {
            $this->evm->dispatchEvent(Events::postFlush, new Event\PostFlushEventArgs($this->sm));
        }
    }

    /**
     * Commit persisted entities to the database
     */
    private function commitPersisted()
    {
        $sortedDocuments = $this->sortObjects($this->scheduledForPersist);
        $client = $this->sm->getClient();

        foreach ($sortedDocuments as $entityName => $documents) {
            $classMetadata = $this->sm->getClassMetadata($entityName);
            $client->addDocuments($classMetadata, $documents);
            $this->updatedIndexes[] = $classMetadata->getIndexName();
        }
    }

    /**
     * Commit deleted entities to the database
     */
    private function commitRemoved()
    {
        $sortedDocuments = $this->sortObjects($this->scheduledForDelete, false);
        $client = $this->sm->getClient();

        foreach ($sortedDocuments as $entityName => $documents) {
            $classMetadata = $this->sm->getClassMetadata($entityName);
            $client->removeDocuments($classMetadata, $documents);
            $this->updatedIndexes[] = $classMetadata->getIndexName();
        }
    }

    /**
     * Prepare entities for commit. Entities scheduled for deletion do not need
     * to be serialized.
     *
     * @param array $scheduledObjects
     * @param boolean $serialize
     * @throws DoctrineSearchException
     * @return array
     */
    private function sortObjects(array $scheduledObjects, $serialize = true)
    {
        $documents = array();
        $serializer = $this->sm->getSerializer();

        foreach ($scheduledObjects as $type => $objects) {
            $metadata = $this->sm->getClassMetadata($type);

            foreach ($objects as $object) {
                $document = $serialize ? $serializer->serialize($object) : $object;

                if (!$metadata->getIdentifier()) {
                    throw new DoctrineSearchException('Entity must have an id to be indexed');
                }

                $id = implode('-', (array) $metadata->getIdentifierValues($object));
                $documents[$type][$id] = $document;
            }
        }

        return $documents;
    }

    /**
     * Load and hydrate a document model
     *
     * @param ClassMetadata $class
     * @param mixed $value
     * @param array $options
     */
    public function load(ClassMetadata $class, $value, $options = array())
    {
        $client = $this->sm->getClient();

        if (isset($options['field'])) {
            $document = $client->findOneBy($class, $options['field'], $value);
        } else {
            $document = $client->find($class, $value, $options);
        }

        return $this->hydrateEntity($class, $document);
    }

    /**
     * Load and hydrate a document collection
     *
     * @param array $classes
     * @param mixed $query
     * @return ArrayCollection|Searchable[]
     */
    public function loadCollection(array $classes, $query)
    {
        $results = $this->sm->getClient()->search($query, $classes);
        return $this->hydrateCollection($classes, $results);
    }

    /**
     * Construct an entity collection
     *
     * @param array|ClassMetadata[] $classes
     * @param Traversable|Document[] $resultSet
     * @return ArrayCollection|Searchable[]
     */
    public function hydrateCollection(array $classes, Traversable $resultSet)
    {
        $map = array();
        foreach ($classes as $class) {
            $map[$class->getIndexName()][$class->getTypeName()] = $class;
        }

        if ($om = $this->sm->getObjectManager()) { // preload entities by one query
            $documentsByType = array();
            foreach ($resultSet as $document) {
                /** @var ClassMetadata $class */
                $class = $map[$document->getIndex()][$document->getType()];
                $documentsByType[$class->className][$document->getId()] = $document;
            }

            foreach ($documentsByType as $className => $documents) {
                $metadata = $this->sm->getClassMetadata($className);

                $repository = $om->getRepository($className);
                $repository->findBy([$metadata->getIdentifier() => array_keys($documents)]);
            }
        }

        $collection = new ArrayCollection();
        foreach ($resultSet as $document) {
            /** @var ClassMetadata $class */
            $class = $map[$document->getIndex()][$document->getType()];
            $collection[] = $this->hydrateEntity($class, $document);
        }

        return $collection;
    }



    /**
     * Construct an entity object
     *
     * @param ClassMetadata $class
     * @param object|Document $document
     * @return Searchable
     */
    public function hydrateEntity(ClassMetadata $class, $document)
    {
        // TODO: add support for different result set types from different clients by implementing Persisters per client
        $data = $document->getData();
        $fields = array_merge(
            $document->hasFields() ? $document->getFields() : array(),
            array('_version' => $document->getVersion())
        );

        foreach ($fields as $name => $value) {
            if (isset($class->type->parameters[$name])) {
                $data[$name] = $value;

            } elseif ($key = array_search($name, $class->type->parameters, TRUE)) {
                $data[$key] = $value;
            }
        }

        $data[$class->getIdentifier()] = $document->getId();

        if ($om = $this->sm->getObjectManager()) {
            $entity = $om->find($class->className, $document->getId());

        } else {
            $entity = $this->sm->getSerializer()->deserialize($class->className, json_encode($data));
        }

        if ($this->evm->hasListeners(Events::postLoad)) {
            $this->evm->dispatchEvent(Events::postLoad, new Event\PostLoadEventArgs($entity, $data, $this->sm));
        }

        return $entity;
    }

    /**
     * Checks whether an entity is registered in the identity map of this UnitOfWork.
     *
     * @param Searchable $entity
     * @return boolean
     */
    public function isInIdentityMap($entity)
    {
        $oid = spl_object_hash($entity);
        $class = ClassUtils::getRealClass(get_class($entity));
        return isset($this->scheduledForPersist[$class][$oid])
            || isset($this->scheduledForDelete[$class][$oid]);
    }



    /**
     * @return array
     */
    public function getScheduledForDelete()
    {
        return $this->scheduledForDelete;
    }



    /**
     * @return array
     */
    public function getScheduledForPersist()
    {
        return $this->scheduledForPersist;
    }

}
