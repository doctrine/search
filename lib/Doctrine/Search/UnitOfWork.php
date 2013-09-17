<?php

namespace Doctrine\Search;

use Doctrine\Search\SearchManager;
use Doctrine\Search\Exception\DoctrineSearchException;

class UnitOfWork
{
     /**
      * The SearchManager that "owns" this UnitOfWork instance.
      *
      * @var \Doctrine\Search\SearchManager
      */
     private $sm;
     
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
    }
    
    /**
     * Persists an entity as part of the current unit of work.
     *
     * @param object $entity The entity to persist.
     */
    public function persist($entity)
    {
        $this->scheduledForPersist[] = $entity;
    }
    
    /**
     * Deletes an entity as part of the current unit of work.
     *
     * @param object $entity The entity to remove.
     */
    public function remove($entity)
    {
        $this->scheduledForDelete[] = $entity;
    }
    
    /**
     * Clears the UnitOfWork.
     *
     * @param string $entityName if given, only entities of this type will get detached
     */
    public function clear($entityName = null)
    {
        //TODO: to be implemented
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
        //TODO: single/array entity commit handling
        $this->commitPersisted();
        $this->scheduledForPersist = array();
        
        $this->commitRemoved();
        $this->scheduledForDelete = array();
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
