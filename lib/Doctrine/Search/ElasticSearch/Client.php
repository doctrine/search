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

namespace Doctrine\Search\ElasticSearch;

use Doctrine;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Mapping\TypeMetadata;
use Doctrine\Search\Mapping\TypeMetadataFactory;
use Doctrine\Search\NoResultException;
use Doctrine\Search\SearchClient;
use Elastica\Client as ElasticaClient;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Search;



/**
 * SearchManager for ElasticSearch-Backend
 *
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 * @author  Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Client implements SearchClient
{
    /**
     * @var ElasticaClient
     */
    private $client;

    /**
     * @param ElasticaClient $client
     */
    public function __construct(ElasticaClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return ElasticaClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function addDocuments(ClassMetadata $class, array $documents)
    {
        $type = $this->getIndex($class->index)->getType($class->type);

        $parameters = $this->getParameters($class->parameters);

        $bulk = array();
        foreach ($documents as $id => $document) {
            $elasticaDoc = new Document($id);
            foreach ($parameters as $name => $value) {
                if (isset($document[$value])) {
                    if (method_exists($elasticaDoc, "set{$name}")) {
                        $elasticaDoc->{"set{$name}"}($document[$value]);
                    } else {
                        $elasticaDoc->setParam($name, $document[$value]);
                    }
                    unset($document[$value]);
                }
            }
            $elasticaDoc->setData($document);
            $bulk[] = $elasticaDoc;
        }

        if (count($bulk) > 1) {
            $type->addDocuments($bulk);
        } else {
            $type->addDocument($bulk[0]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeDocuments(ClassMetadata $class, array $documents)
    {
        $type = $this->getIndex($class->index)->getType($class->type);
        $type->deleteIds(array_keys($documents));
    }

    /**
     * {@inheritDoc}
     */
    public function removeAll(ClassMetadata $class, $query = null)
    {
        $type = $this->getIndex($class->index)->getType($class->type);
        $query = $query ?: new MatchAll();
        $type->deleteByQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function find(ClassMetadata $class, $id, $options = array())
    {
        try {
            $type = $this->getIndex($class->index)->getType($class->type);
            $document = $type->getDocument($id, $options);
        } catch (NotFoundException $ex) {
            throw new NoResultException();
        }

        return $document;
    }

    public function findOneBy(ClassMetadata $class, $field, $value)
    {
        $query = new Query();
        $query->setVersion(true);
        $query->setSize(1);

        $filter = new Term(array($field => $value));
        $query->setFilter($filter);

        $results = $this->search($query, array($class));

        if (!$results->count()) {
            throw new NoResultException();
        }

        return $results[0];
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $classes)
    {
        return $this->buildQuery($classes)->search();
    }

    protected function buildQuery(array $classes)
    {
        $searchQuery = new Search($this->client);
        $searchQuery->setOption(Search::OPTION_VERSION, true);
        foreach ($classes as $class) {
            if ($class->index) {
                $indexObject = $this->getIndex($class->index);
                $searchQuery->addIndex($indexObject);
                if ($class->type) {
                    $searchQuery->addType($indexObject->getType($class->type));
                }
            }
        }
        return $searchQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function search($query, array $classes)
    {
        return $this->buildQuery($classes)->search($query);
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex($index)
    {
        $this->getIndex($index)->refresh();
    }

    /**
     * @param string $className
     * @return TypeMetadata
     */
    public function createTypeMetadata($className)
    {
        return new Mapping\TypeMetadata($className);
    }

}
