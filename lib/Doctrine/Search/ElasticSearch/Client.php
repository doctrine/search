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

use Doctrine\Search\Mapping\Annotations\ElasticField;
use Doctrine\Search\Mapping\Annotations\ElasticRoot;
use Doctrine\Search\SearchClientInterface;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Exception\NoResultException;
use Elastica\Client as ElasticaClient;
use Elastica\Type\Mapping;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query\MatchAll;
use Elastica\Filter\Term;
use Elastica\Exception\NotFoundException;
use Elastica\Search;
use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Query;

/**
 * SearchManager for ElasticSearch-Backend
 *
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 * @author  Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Client implements SearchClientInterface
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
    public function createIndex($name, array $config = array())
    {
        $index = $this->getIndex($name);
        $index->create($config, true);
        return $index;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndex($name)
    {
        return $this->client->getIndex($name);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteIndex($index)
    {
        $this->getIndex($index)->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function refreshIndex($index)
    {
        $this->getIndex($index)->refresh();
    }

    /**
     * {@inheritDoc}
     */
    public function createType(ClassMetadata $metadata)
    {
        $type = $this->getIndex($metadata->index)->getType($metadata->type);
        $properties = $this->getMapping($metadata->fieldMappings);
        $rootProperties = $this->getRootMapping($metadata->rootMappings);

        $mapping = new Mapping($type, $properties);
        $mapping->disableSource($metadata->source);
        if (isset($metadata->boost)) {
            $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => $metadata->boost));
        }
        if (isset($metadata->parent)) {
            $mapping->setParent($metadata->parent);
        }
        foreach ($rootProperties as $key => $value) {
            $mapping->setParam($key, $value);
        }

        $mapping->send();

        return $type;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteType(ClassMetadata $metadata)
    {
        $type = $this->getIndex($metadata->index)->getType($metadata->type);
        return $type->delete();
    }

    /**
     * Generates property mapping from entity annotations
     *
     * @param array|ElasticField[] $mappings
     */
    protected function getMapping($mappings)
    {
        $properties = array();

        foreach ($mappings as $propertyName => $fieldMapping) {
            if (isset($fieldMapping->name)) {
                $propertyName = $fieldMapping->name;
            }

            $properties[$propertyName]['type'] = $fieldMapping->type;

            if (isset($fieldMapping->path)) {
                $properties[$propertyName]['path'] = $fieldMapping->path;
            }

            if (isset($fieldMapping->includeInAll)) {
                $properties[$propertyName]['include_in_all'] = $fieldMapping->includeInAll;
            }

            if (isset($fieldMapping->nullValue)) {
                $properties[$propertyName]['null_value'] = $fieldMapping->nullValue;
            }

            if (isset($fieldMapping->store)) {
                $properties[$propertyName]['store'] = $fieldMapping->store;
            }

            if (isset($fieldMapping->index)) {
                $properties[$propertyName]['index'] = $fieldMapping->index;
            }

            if (isset($fieldMapping->boost)) {
                $properties[$propertyName]['boost'] = $fieldMapping->boost;
            }

            if (isset($fieldMapping->analyzer)) {
                $properties[$propertyName]['analyzer'] = $fieldMapping->analyzer;
            }

            if (isset($fieldMapping->indexName)) {
                $properties[$propertyName]['index_name'] = $fieldMapping->indexName;
            }

            if ($fieldMapping->type == 'attachment' && isset($fieldMapping->fields)) {
                $callback = function ($field) {
                    unset($field['type']);
                    return $field;
                };
                $properties[$propertyName]['fields'] = array_map($callback, $this->getMapping($fieldMapping->fields));
            }

            if ($fieldMapping->type == 'multi_field' && isset($fieldMapping->fields)) {
                $properties[$propertyName]['fields'] = $this->getMapping($fieldMapping->fields);
            }

            if (in_array($fieldMapping->type, array('nested', 'object')) && isset($fieldMapping->properties)) {
                $properties[$propertyName]['properties'] = $this->getMapping($fieldMapping->properties);
            }
        }

        return $properties;
    }

    /**
     * Generates parameter mapping from entity annotations
     *
     * @param array $paramMapping
     */
    protected function getParameters($paramMapping)
    {
        $parameters = array();
        foreach ($paramMapping as $propertyName => $mapping) {
            $paramName = isset($mapping->name) ? $mapping->name : $propertyName;
            $parameters[$paramName] = $propertyName;
        }
        return $parameters;
    }

    /**
     * Generates root mapping from entity annotations
     *
     * @param array|ElasticRoot[] $mappings
     */
    protected function getRootMapping($mappings)
    {
        $properties = array();

        foreach ($mappings as $rootMapping) {
            $propertyName = $rootMapping->name;
            $mapping = array();

            if (isset($rootMapping->value)) {
                $mapping = $rootMapping->value;
            }

            if (isset($rootMapping->match)) {
                $mapping['match'] = $rootMapping->match;
            }

            if (isset($rootMapping->pathMatch)) {
                $mapping['path_match'] = $rootMapping->pathMatch;
            }

            if (isset($rootMapping->unmatch)) {
                $mapping['unmatch'] = $rootMapping->unmatch;
            }

            if (isset($rootMapping->pathUnmatch)) {
                $mapping['path_unmatch'] = $rootMapping->pathUnmatch;
            }

            if (isset($rootMapping->matchPattern)) {
                $mapping['match_pattern'] = $rootMapping->matchPattern;
            }

            if (isset($rootMapping->matchMappingType)) {
                $mapping['match_mapping_type'] = $rootMapping->matchMappingType;
            }

            if (isset($rootMapping->mapping)) {
                $mapping['mapping'] = current($this->getMapping($rootMapping->mapping));
            }

            if (isset($rootMapping->id)) {
                $properties[$propertyName][][$rootMapping->id] = $mapping;
            } else {
                $properties[$propertyName] = $mapping;
            }
        }

        return $properties;
    }
}
