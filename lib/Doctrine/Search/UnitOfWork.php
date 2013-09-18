<?php

namespace Doctrine\Search;

use Doctrine\Search\SearchManager;
use Doctrine\Search\Exception\DoctrineSearchException;
use Doctrine\Search\Persisters\BasicEntityPersister;

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
        
        $this->scheduledForPersist[] = $entity;
        
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
        
        $this->scheduledForDelete[] = $entity;

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
        //TODO: implement for named entity classes
        $this->scheduledForDelete = array();
        $this->scheduledForPersist = array();
        
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
        $this->commitPersisted();
        $this->commitRemoved();
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
        
        foreach ($documents as $index => $documentTypes) {
            foreach ($documentTypes as $type => $documents) {
                $client->addDocuments($index, $type, $documents);
            }
        }
    }
    
    /**
     * Commit deleted entities to the database
     */
    private function commitRemoved()
    {
        $documents = $this->sortObjects($this->scheduledForDelete, false);
        $client = $this->sm->getClient();
        
        foreach ($documents as $index => $documentTypes) {
            foreach ($documentTypes as $type => $documents) {
                $client->removeDocuments($index, $type, $documents);
            }
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
            $metadata = $this->sm->getClassMetadata(get_class($object));
            $document = $serialize ? $serializer->serialize($object) : $object;
            $id = $object->getId();
            if (!$id) {
                throw new DoctrineSearchException('Entity does not have an id to index.');
            }
            $documents[$metadata->index][$metadata->type][$id] = $document;
        }
        
        return $documents;
    }
}
