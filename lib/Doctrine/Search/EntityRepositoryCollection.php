<?php

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Exception\DoctrineSearchException;

class EntityRepositoryCollection implements ObjectRepository
{
    /**
     * @var array
     */
    private $_repositories = array();
    
    /**
     * @var \Doctrine\Search\SearchManager
     */
    private $_sm;
     
    public function __construct(SearchManager $sm)
    {
        $this->_sm = $sm;
    }
    
    /**
     * Add a repository to the collection
     * 
     * @param EntityRepository $repository
     */
    public function addRepository(EntityRepository $repository)
    {
        $this->_repositories[] = $repository;
    }
    
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     * @return object The object.
     */
    public function find($id)
    {
        return $this->_sm->find($this->_entityName, $id);
    }
    
    /**
     * Finds all objects in the repository.
     *
     * @return mixed The objects.
     */
    public function findAll()
    {
        return $this->findBy(array());
    }
    
    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @throws \UnexpectedValueException
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new DoctrineSearchException('Not implemented.');
    }
    
    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        throw new DoctrineSearchException('Not implemented.');
    }
    
    /**
     * Execute a direct search query on the associated index and type
     * 
     * @param object $query
     */
    public function search($query)
    {
        $classes = $this->getClassMetadata();
        return $this->_sm->getUnitOfWork()->loadCollection($classes, $query);
    }
    
    /**
     * Execute a direct delete by query on the associated index and type
     *
     * @param object $query
     */
    public function delete($query)
    {
        $classes = $this->getClassMetadata();
        foreach($classes as $class) {
            $this->_sm->getClient()->removeAll($class, $query);
        }
    }
    
    /**
     * Returns the class names of the objects managed by the repository
     *
     * @return string
     */
    public function getClassName()
    {
        $classNames = array();
        foreach($this->_repositories as $repository)
        {
            $classNames[] = $repository->getClassName();
        }
        return $classNames;
    }
    
    /**
     * Returns the class metadata of the objects managed by the repository
     *
     * @return array
     */
    protected function getClassMetadata()
    {
        $classes = array();
        foreach($this->_repositories as $repository) {
            $classes[] = $repository->getClassMetadata();
        }
        return $classes;
    }
}
