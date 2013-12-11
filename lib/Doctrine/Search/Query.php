<?php

namespace Doctrine\Search;

use Doctrine\Search\Exception\DoctrineSearchException;
use Doctrine\Common\Collections\ArrayCollection;

class Query
{
    const HYDRATE_BYPASS = -1;
    
    const HYDRATE_INTERNAL = -2;

    const HYDRATION_PARAMETER = 'ids';

    /**
     * @var SearchManager
     */
    protected $sm;

    /**
     * @var object
     */
    protected $query;

    /**
     * @var object
     */
    protected $hydrationQuery;

    /**
     * @var string
     */
    protected $hydrationParameter = self::HYDRATION_PARAMETER;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var integer
     */
    protected $hydrationMode;

    /**
     * @var boolean
     */
    protected $useResultCache;

    /**
     * @var integer
     */
    protected $cacheLifetime;

    /**
     * @var integer
     */
    protected $count;

    public function __construct(SearchManager $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Magic method to pass query building to the underlying query
     * object, saving the need to abstract.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        if (!$this->query) {
            throw new DoctrineSearchException('No client query has been provided using Query#searchWith().');
        }
        
        call_user_func_array(array($this->query, $method), $arguments);
        return $this;
    }

    /**
     * Specifies the searchable entity class to search against.
     *
     * @param string $entityClass
     */
    public function from($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * Set the query object to be executed on the search engine
     *
     * @param mixes $query
     */
    public function searchWith($query)
    {
        $this->query = $query;
        return $this;
    }

    protected function getSearchManager()
    {
        return $this->sm;
    }

    /**
     * Set the hydration mode from the underlying query modes
     * or bypass and return search result directly from the client
     *
     * @param integer $mode
     */
    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
        return $this;
    }

    /**
     * If hydrating with Doctrine then you can use the result cache
     * on the default or provided query
     *
     * @param boolean $useCache
     * @param integer $cacheLifetime
     */
    public function useResultCache($useCache, $cacheLifetime = null)
    {
        $this->useCache = $useCache;
        $this->cacheLifetime = $cacheLifetime;
        return $this;
    }

    /**
     * Return the total hit count for the given query as provided by
     * the search engine.
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Set a custom Doctrine Query to execute in order to hydrate the search
     * engine results into required entities. The assumption is made the the
     * search engine result id is correlated to the entity id. An optional
     * query parameter override can be specified.
     *
     * @param object $hydrationQuery
     * @param string $parameter
     */
    public function hydrateWith($hydrationQuery, $parameter = null)
    {
        $this->hydrationQuery = $hydrationQuery;
        if ($parameter) {
            $this->hydrationParameter = $parameter;
        }
        return $this;
    }

    /**
     * Return a provided hydration query
     *
     * @return object
     */
    protected function getHydrationQuery()
    {
        if (!$this->hydrationQuery) {
            throw new DoctrineSearchException('A hydration query is required for hydrating results to entities.');
        }

        return $this->hydrationQuery;
    }

    /**
     * Execute search and hydrate results if required.
     *
     * @param integer $hydrationMode
     * @throws DoctrineSearchException
     * @return mixed
     */
    public function getResult($hydrationMode = null)
    {
        if ($hydrationMode) {
            $this->hydrationMode = $hydrationMode;
        }

        $class = $this->getSearchManager()->getClassMetadata($this->entityClass);
        $resultSet = $this->getSearchManager()->getClient()->search(
            $this->query,
            $class->index,
            $class->type
        );

        $resultClass = get_class($resultSet);
        
        // TODO: abstraction of support for different result sets
        switch($resultClass) {
            case 'Elastica\ResultSet':
                $this->count = $resultSet->getTotalHits();
                $results = $resultSet->getResults();
                break;
            default:
                throw new DoctrineSearchException("Unexpected result set class '$resultClass'");
        }

        // Return results depending on hydration mode
        if ($this->hydrationMode == self::HYDRATE_BYPASS) {
            return $resultSet;
        } elseif ($this->hydrationMode == self::HYDRATE_INTERNAL) {
            $unitOfWork = $this->sm->getUnitOfWork();
            $collection = new ArrayCollection();
            foreach ($results as $document) {
                $collection[] = $unitOfWork->hydrateEntity($class, $document);
            }
            return $collection;
        }
        
        // Document ids are used to lookup dbms results
        $fn = function ($result) {
            return $result->getId();
        };
        $ids = array_map($fn, $results);
        
        return $this->getHydrationQuery()
            ->setParameter($this->hydrationParameter, $ids ?: null)
            ->useResultCache($this->useResultCache, $this->cacheLifetime)
            ->getResult($this->hydrationMode);
    }
}
