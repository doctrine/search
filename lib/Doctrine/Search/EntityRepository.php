<?php

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;

class EntityRepository implements ObjectRepository
{
    /**
     * @var string
     */
    protected $_entityName;
    
    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $_class;
    
    /**
     * @var SearchManager
     */
    private $_sm;
     
    public function __construct(SearchManager $sm, ClassMetadata $class)
    {
        $this->_sm = $sm;
        $this->_entityName = $class->className;
        $this->_class = $class;
    }
    
    /**
     * Execute a custom query
     * 
     * @param object $query
     */
    public function search($query)
    {
        return $this->_sm->getClient()->search($this->_class->index, $this->_class->type, $query);
    }
    
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     * @return object The object.
     */
    public function find($id)
    {
        return $this->_sm->getClient()->find($this->_class->index, $this->_class->type, $id);
    }
    
    /**
     * Finds all objects in the repository.
     *
     * @return mixed The objects.
     */
    public function findAll()
    {
        return $this->_sm->getClient()->findAll($this->_class->index, $this->_class->type);
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
        
    }
    
    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        
    }
    
    /**
     * Returns the class name of the object managed by the repository
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->entityName;
    }
}
