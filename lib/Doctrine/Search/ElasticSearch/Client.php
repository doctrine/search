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
use Elastica\Client as Elastica_Client;
use Elastica\Type\Mapping;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query\MatchAll;

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
     * @param Elastica_Client $client
     */
    public function __construct(Elastica_Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function addDocuments($index, $type, array $documents)
    {
        $index = $this->client->getIndex($index);
        $type = $index->getType($type);
        
        $batch = array();
        foreach($documents as $id => $document)
        {
            $batch[] = new Document($id, $document);
        }
        
        $type->addDocuments($batch);
    }
    
    /**
     * {@inheritDoc}
     */
    public function removeDocuments($index, $type, array $documents)
    {
        $index = $this->client->getIndex($index);
        $type = $index->getType($type);
        $type->deleteIds(array_keys($documents));
    }
    
    /**
     * Remove all documents of a given type from the specified index
     * without deleting the index itself
     *
     * @param string $index
     * @param string $type
     */
    public function removeAll($index, $type)
    {
    	$index = $this->client->getIndex($index);
    	$type = $index->getType($type);
    	$type->deleteByQuery(new MatchAll());
    }    
    
    /**
     * {@inheritDoc}
     */
    public function find($index, $type, $query)
    {
        $index = $this->client->getIndex($index);
        return iterator_to_array($index->search($query));
    }

    /**
     * {@inheritDoc}
     */
    public function createIndex($name, array $config = array())
    {
        $index = $this->client->getIndex($name);
        $index->create($config, true);
        return $index;
    }
    
    /**
     * Create a document type mapping in the specified index
     * 
     * @param Elastica\Index $index
     * @param Doctrine\Search\Mapping\ClassMetadata $metadata
     */
    public function createType(Index $index, ClassMetadata $metadata)
    {
        $type = $index->getType($metadata->type);
        $properties = $this->getMapping($metadata->fieldMappings);

        $mapping = new Mapping($type, $properties);
        $mapping->disableSource($metadata->source);
        $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => $metadata->boost));
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
    	
        foreach($fieldMapping as $propertyName => $fieldMapping)
        {
	         if(isset($fieldMapping->name)) $propertyName = $fieldMapping->name;
	         $properties[$propertyName]['type'] = $fieldMapping->type;
	         if(isset($fieldMapping->includeInAll)) $properties[$propertyName]['include_in_all'] = $fieldMapping->includeInAll;
	         if(isset($fieldMapping->index)) $properties[$propertyName]['index'] = $fieldMapping->index;
	         if(isset($fieldMapping->boost)) $properties[$propertyName]['boost'] = $fieldMapping->boost;
	         
	         if($fieldMapping->type == 'multi_field' && isset($fieldMapping->fields)) 
	         {
	         	$properties[$propertyName]['fields'] = $this->getMapping($fieldMapping->fields);
	         }
	         
	         if(in_array($fieldMapping->type, array('nested', 'object')) && isset($fieldMapping->properties)) 
	         {
	             $properties[$propertyName]['properties'] = $this->getMapping($fieldMapping->properties);
	         }
        }
    	
        return $properties;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteIndex($index)
    {
        $index = $this->client->getIndex($index);
        $index->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkSearch(array $data)
    {
    }
}