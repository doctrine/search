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

use Doctrine;
use Elastica;



class Query
{
    const HYDRATE_BYPASS = -1;
    const HYDRATE_INTERNAL = -2;
    const HYDRATE_QUERY = NULL;

    const HYDRATION_PARAMETER = 'ids';

    /**
     * @var SearchManager
     */
    protected $sm;

    /**
     * @var Elastica\Query
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
     * @var array
     */
    protected $entityClasses;

    /**
     * @var integer
     */
    protected $hydrationMode = self::HYDRATE_INTERNAL;

    /**
     * @var boolean
     */
    protected $useResultCache;

    /**
     * @var integer
     */
    protected $cacheLifetime;

    /**
     * @var string
     */
    protected $resultCacheId;

    /**
     * @var integer
     */
    protected $count;

    /**
     * @var array
     */
    protected $facets;

    /**
     * @var integer
     */
    protected $firstResult;

    /**
     * @var integer
     */
    protected $maxResults;



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
     * @return Query
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
     * @param string|array $entityClasses
     * @return Query
     */
    public function from($entityClasses)
    {
        $this->entityClasses = (array)$entityClasses;
        return $this;
    }



    /**
     * Set the query object to be executed on the search engine
     *
     * @param mixed $query
     * @return Query
     */
    public function searchWith($query)
    {
        $client = $this->sm->getClient();

        if ($client instanceof ElasticSearch\Client) {
            $query = Elastica\Query::create($query);

        } else {
            throw new NotImplementedException;
        }

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
     * @return Query
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
     * @param string $resultCacheId
     * @return Query
     */
    public function useResultCache($useCache, $cacheLifetime = null, $resultCacheId = NULL)
    {
        $this->useResultCache = $useCache;
        $this->cacheLifetime = $cacheLifetime;
        $this->resultCacheId = $resultCacheId;
        return $this;
    }



    /**
     * @return int
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }



    /**
     * @param int $firstResult
     * @return Query
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        return $this;
    }



    /**
     * @return int
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }



    /**
     * @param int $maxResults
     * @return Query
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }



    /**
     * Set a custom Doctrine Query to execute in order to hydrate the search
     * engine results into required entities. The assumption is made the the
     * search engine result id is correlated to the entity id. An optional
     * query parameter override can be specified.
     *
     * @param object $hydrationQuery
     * @param string $parameter
     * @return Query
     */
    public function hydrateWith($hydrationQuery, $parameter = null)
    {
        if ($hydrationQuery instanceof Doctrine\ORM\QueryBuilder) {
            if (!$this->entityClasses) {
                $this->entityClasses = $hydrationQuery->getRootEntities();
            }

            $hydrationQuery = $hydrationQuery->getQuery();

        } elseif ($hydrationQuery instanceof Doctrine\ORM\AbstractQuery) {
            // pass

        } else {
            throw new NotImplementedException(sprintf('Unsupported type of hydration query provided: %s', get_class($hydrationQuery)));
        }

        $this->hydrationMode = self::HYDRATE_QUERY;
        $this->hydrationQuery = $hydrationQuery;
        if ($parameter) {
            $this->hydrationParameter = $parameter;
        }

        return $this;
    }



    /**
     * Return a provided hydration query
     *
     * @return Doctrine\ORM\AbstractQuery
     */
    protected function getHydrationQuery()
    {
        if (!$this->hydrationQuery) {
            throw new InvalidStateException('A hydration query is required for hydrating results to entities.');
        }

        return $this->hydrationQuery;
    }



    /**
     * Execute search and hydrate results if required.
     *
     * @param integer $hydrationMode
     * @throws InvalidStateException
     * @return array|Searchable[]
     */
    public function getResult($hydrationMode = null)
    {
        if ($hydrationMode) {
            $this->hydrationMode = $hydrationMode;
        }

        $classes = array();
        foreach ($this->entityClasses as $entityClass) {
            $classes[] = $this->sm->getClassMetadata($entityClass);
        }

        if ($this->query instanceof Elastica\Query) {
            if ($this->maxResults) {
                $this->query->setSize($this->maxResults);
            }
            if ($this->firstResult) {
                $this->query->setFrom($this->firstResult);
            }
        }

        $client = $this->getSearchManager()->getClient();
        $resultSet = $client->search($this->query, $classes);

        if ($resultSet instanceof Elastica\ResultSet) {
            $this->count = $resultSet->getTotalHits();
            $this->facets = $resultSet->getFacets();
            $results = $resultSet->getResults();

        } else {
            throw new NotImplementedException(sprintf('Unexpected result set class \'%s\'', get_class($resultSet)));
        }

        // Return results depending on hydration mode
        if ($this->hydrationMode == self::HYDRATE_BYPASS) {
            return $resultSet;

        } elseif ($this->hydrationMode == self::HYDRATE_INTERNAL) {
            return $this->sm->getUnitOfWork()->hydrateCollection($classes, $resultSet);
        }

        // Document ids are used to lookup dbms results
        $ids = array_map(function ($result) {
            /** @var Elastica\Document $result */
            return $result->getId();
        }, $results);

        return $this->getHydrationQuery()
            ->setParameter($this->hydrationParameter, $ids ?: null)
            ->useResultCache($this->useResultCache, $this->cacheLifetime, $this->resultCacheId)
            ->getResult($this->hydrationMode);
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
     * @return array
     */
    public function getFacets()
    {
        return $this->facets;
    }

}
