<?php

namespace Doctrine\Search;

use Doctrine\Search\SearchManager;
use Doctrine\Search\Exception\DoctrineSearchException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Search\Mapping\ClassMetadata;
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
        $this->scheduledForPersist[$oid] = $entity;

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
        $this->scheduledForDelete[$oid] = $entity;

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
            //TODO: implement for named entity classes
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

        //TODO: single/array entity commit handling
        $this->commitRemoved();
        $this->commitPersisted();

        //Force refresh of updated indexes
        if($entity === true) {
            $client = $this->sm->getClient();
            foreach(array_unique($this->updatedIndexes) as $index) {
                $client->refreshIndex($index);
            }
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
        $documents = $this->sortObjects($this->scheduledForPersist);
        $client = $this->sm->getClient();

        foreach ($documents as $entityName => $documents) {
            $classMetadata = $this->sm->getClassMetadata($entityName);
            $this->updatedIndexes[] = $classMetadata->index;
            $client->addDocuments($classMetadata, $documents);
        }
    }

    /**
     * Commit deleted entities to the database
     */
    private function commitRemoved()
    {
        $documents = $this->sortObjects($this->scheduledForDelete, false);
        $client = $this->sm->getClient();

        foreach ($documents as $entityName => $documents) {
            $classMetadata = $this->sm->getClassMetadata($entityName);
            $this->updatedIndexes[] = $classMetadata->index;
            $client->removeDocuments($classMetadata, $documents);
        }
    }

    /**
     * Prepare entities for commit. Entities scheduled for deletion do not need
     * to be serialized.
     *
     * @param array $objects
     * @param boolean $serialize
     * @throws DoctrineSearchException
     * @return array
     */
    private function sortObjects(array $objects, $serialize = true)
    {
        $documents = array();
        $serializer = $this->sm->getSerializer();

        foreach ($objects as $object) {
            $document = $serialize ? $serializer->serialize($object) : $object;

            if(!array_key_exists('id', $document)){
                $id = $object->getId();
                if (!isset($id)) {
                    throw new DoctrineSearchException('Entity must have an id to be indexed');
                } else {
                    $document['id'] = $id;
                }
            }

            $documents[get_class($object)][] = $document;
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
     * @param unknown $query
     */
    public function loadCollection(array $classes, $query)
    {
        $results = $this->sm->getClient()->search($query, $classes);
        return $this->hydrateCollection($classes, $results);
    }

    /**
     * Construct an entity collection
     *
     * @param array $classes
     * @param Traversable $resultSet
     */
    public function hydrateCollection(array $classes, Traversable $resultSet)
    {
        $collection = new ArrayCollection();
        foreach ($resultSet as $document) {
            foreach($classes as $class) {
                if($document->getIndex() == $class->index && $document->getType() == $class->type) {
                    break;
                }
            }
            $collection[] = $this->hydrateEntity($class, $document);
        }

        return $collection;
    }

    /**
     * Construct an entity object
     *
     * @param ClassMetadata $class
     * @param object $document
     */
    public function hydrateEntity(ClassMetadata $class, $document)
    {
        // TODO: add support for different result set types from different clients
        // perhaps by wrapping documents in a layer of abstraction
        $data = $document->getData();
        $fields = array_merge(
            $document->hasFields() ? $document->getFields() : array(),
            array('_version' => $document->getVersion())
        );

        foreach($fields as $name => $value) {
            if (isset($class->parameters[$name])) {
                $data[$name] = $value;
            } else {
                foreach($class->parameters as $param => $mapping) {
                    if ($mapping->name == $name) {
                        $data[$param] = $value;
                        break;
                    }
                }
            }
        }

        $data[$class->getIdentifier()] = $document->getId();

        $entity = $this->sm->getSerializer()->deserialize($class->className, json_encode($data));

        if ($this->evm->hasListeners(Events::postLoad)) {
            $this->evm->dispatchEvent(Events::postLoad, new Event\LifecycleEventArgs($entity, $this->sm));
        }

        return $entity;
    }

    /**
     * Checks whether an entity is registered in the identity map of this UnitOfWork.
     *
     * @param object $entity
     *
     * @return boolean
     */
    public function isInIdentityMap($entity)
    {
        $oid = spl_object_hash($entity);
        return isset($this->scheduledForPersist[$oid]) || isset($this->scheduledForDelete[$oid]);
    }
}
