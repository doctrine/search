<?php

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Exception\DoctrineSearchException;

class EntityRepository implements ObjectRepository
{
    /**
     * @var string
     */
    protected $_entityName;
    
    /**
     * @var \Doctrine\Search\Mapping\ClassMetadata
     */
    private $_class;
    
    /**
     * @var \Doctrine\Search\SearchManager
     */
    private $_sm;
     
    public function __construct(SearchManager $sm, ClassMetadata $class)
    {
        $this->_sm = $sm;
        $this->_entityName = $class->className;
        $this->_class = $class;
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
        throw new DoctrineSearchException('Not yet implemented.');
    }
    
    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $key = key($criteria);
        $value = current($criteria);
        return $this->_sm->getUnitOfWork()->load($this->_entityName, $value, $key);
    }
    
    /**
     * Execute a direct search query on the associated index and type
     * 
     * @param object $query
     */
    public function search($query)
    {
        return $this->_sm->getUnitOfWork()->loadCollection($this->_entityName, $query);
    }
    
    /**
     * Returns the class name of the object managed by the repository
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_entityName;
    }
}
