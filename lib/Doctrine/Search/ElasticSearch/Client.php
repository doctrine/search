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
use Elastica\Query\Term;
use Elastica\Exception\NotFoundException;
use Elastica\Search;

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
     * {@inheritDoc}
     */
    public function addDocuments($index, $type, array $documents)
    {
        $type = $this->getIndex($index)->getType($type);

        $batch = array();
        foreach ($documents as $id => $document) {
            $batch[] = new Document($id, $document);
        }

        $type->addDocuments($batch);
    }

    /**
     * {@inheritDoc}
     */
    public function removeDocuments($index, $type, array $documents)
    {
        $type = $this->getIndex($index)->getType($type);
        $type->deleteIds(array_keys($documents));
    }

    /**
     * {@inheritDoc}
     */
    public function removeAll($index, $type)
    {
        $type = $this->getIndex($index)->getType($type);
        $type->deleteByQuery(new MatchAll());
    }

    /**
     * {@inheritDoc}
     */
    public function find($index, $type, $id)
    {
        try {
            $type = $this->getIndex($index)->getType($type);
            $document = $type->getDocument($id);
        } catch (NotFoundException $ex) {
            throw new NoResultException();
        }
        
        return $document;
    }
    
    public function findOneBy($index, $type, $key, $value)
    {
        $query = new Term();
        $query->setTerm($key, $value);
        
        $results = $this->search($query, $index, $type);
        
        if (!$results->count()) {
            throw new NoResultException();
        }
        
        return $results[0];
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAll($index, $type)
    {
        $type = $this->getIndex($index)->getType($type);
        //TODO: override paging limit
        return $type->createSearch()->search();
    }

    /**
     * {@inheritDoc}
     */
    public function search($query, $index = null, $type = null)
    {
        $searchQuery = new Search($this->client);
        
        if ($index) {
            $indexObject = $this->getIndex($index);
            $searchQuery->addIndex($indexObject);
            if ($type) {
                $searchQuery->addType($indexObject->getType($type));
            }
        }
        
        return $searchQuery->search($query);
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
    public function createType(ClassMetadata $metadata)
    {
        $type = $this->getIndex($metadata->index)->getType($metadata->type);
        $properties = $this->getMapping($metadata->fieldMappings);

        $mapping = new Mapping($type, $properties);
        $mapping->disableSource($metadata->source);
        $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => $metadata->boost));
        if (isset($metadata->parent)) {
            $mapping->setParent($metadata->parent);
        }
        $mapping->send();

        return $type;
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

            if (isset($fieldMapping->includeInAll)) {
                $properties[$propertyName]['include_in_all'] = $fieldMapping->includeInAll;
            }

            if (isset($fieldMapping->index)) {
                $properties[$propertyName]['index'] = $fieldMapping->index;
            }

            if (isset($fieldMapping->boost)) {
                $properties[$propertyName]['boost'] = $fieldMapping->boost;
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
}
