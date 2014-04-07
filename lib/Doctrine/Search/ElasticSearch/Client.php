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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Search\ElasticSearch;

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
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
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
            $elasticadoc = new Document($id);
            foreach($parameters as $name => $value) {
                if(isset($document[$value])) {
                    if(method_exists($elasticadoc, "set{$name}")) {
                        $elasticadoc->{"set{$name}"}($document[$value]);
                    } else {
                        $elasticadoc->setParam($name, $document[$value]);
                    }
                }
            }
            $elasticadoc->setData($document);
            $bulk[] = $elasticadoc;
        }

        $type->addDocuments($bulk);
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
    public function find(ClassMetadata $class, $id)
    {
        try {
            $type = $this->getIndex($class->index)->getType($class->type);
            $document = $type->getDocument($id);
        } catch (NotFoundException $ex) {
            throw new NoResultException();
        }
        
        return $document;
    }
    
    public function findOneBy(ClassMetadata $class, $key, $value)
    {
        $query = new Query();
        $query->setVersion(true);
        $query->setSize(1);
        
        $filter = new Term(array($key => $value));
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
        foreach($classes as $class) {
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

        $mapping = new Mapping($type, $properties);
        $mapping->disableSource($metadata->source);
        if (isset($metadata->boost)) {
            $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => $metadata->boost));
        }
        if (isset($metadata->parent)) {
            $mapping->setParent($metadata->parent);
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
     * @param array $fieldMapping
     */
    protected function getMapping($fieldMapping)
    {
        $properties = array();

        foreach ($fieldMapping as $propertyName => $fieldMapping) {
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

            if (isset($fieldMapping->index)) {
                $properties[$propertyName]['index'] = $fieldMapping->index;
            }

            if (isset($fieldMapping->boost)) {
                $properties[$propertyName]['boost'] = $fieldMapping->boost;
            }
            
            if (isset($fieldMapping->analyzer)) {
                $properties[$propertyName]['analyzer'] = $fieldMapping->analyzer;
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
     * @param array $fieldMapping
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
}
