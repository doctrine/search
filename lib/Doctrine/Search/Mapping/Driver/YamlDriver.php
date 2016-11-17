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

namespace Doctrine\Search\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Search\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\MappingException as CommonMappingException;
use Symfony\Component\Yaml\Yaml;

/**
 * The YamlDriver reads the mapping metadata from yaml schema files.
 *
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class YamlDriver extends FileDriver
{
    const DEFAULT_FILE_EXTENSION = '.dcm.yml';

    /**
     * {@inheritDoc}
     */
    protected $entityAnnotationClasses = array(
        'Doctrine\Search\Mapping\Annotations\Searchable' => 1,
        'Doctrine\Search\Mapping\Annotations\ElasticSearchable' => 2
    );
    
    /**
     * {@inheritDoc}
     */
    public function __construct($locator, $fileExtension = self::DEFAULT_FILE_EXTENSION)
    {
        parent::__construct($locator, $fileExtension);
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        /* @var $metadata \Doctrine\Search\Mapping\ClassMetadata */
        $hierarchy = array_merge(array($className), class_parents($className));
        
        // Look for mappings in the class heirarchy and merge
        $element = array();
        foreach (array_reverse($hierarchy) as $subClassName) {
            try {
                $element = array_merge($element, $this->getElement($subClassName));
            } catch (CommonMappingException $e) {
            }
        }
        
        if (empty($element)) {
            throw MappingException::mappingFileNotFound($className);
        }

        $this->annotateClassMetadata($element, $metadata);
        
        // Evaluate root mappings
        if (isset($element['root'])) {
            foreach ($element['root'] as $rootMapping) {
                $metadata->mapRoot($this->rootToArray($rootMapping));
            }
        }

        // Evaluate id
        if (isset($element['id'])) {
            $metadata->identifier = $element['id'];
        }
        
        // Evaluate field mappings
        if (isset($element['fields'])) {
            foreach ($element['fields'] as $name => $fieldMapping) {
                $mapping = $this->fieldToArray($name, $fieldMapping);
                $metadata->mapField($mapping);
            }
        }
        
        // Evaluate parameter mappings
        if (isset($element['parameters'])) {
            foreach ($element['parameters'] as $name => $parameterMapping) {
                $mapping = $this->parameterToArray($name, $parameterMapping);
                $metadata->mapParameter($mapping);
            }
        }
    }
    
    private function annotateClassMetadata($classMapping, $metadata)
    {
        $className = $classMapping['class'];
        switch ($className) {
            case 'ElasticSearchable':
                if (isset($classMapping['numberOfShards'])) {
                    $metadata->numberOfShards = $classMapping['numberOfShards'];
                }
                if (isset($classMapping['numberOfReplicas'])) {
                    $metadata->numberOfReplicas = $classMapping['numberOfReplicas'];
                }
                if (isset($classMapping['parent'])) {
                    $metadata->parent = $classMapping['parent'];
                }
                if (isset($classMapping['timeToLive'])) {
                    $metadata->timeToLive = $classMapping['timeToLive'];
                }
                if (isset($classMapping['boost'])) {
                    $metadata->boost = $classMapping['boost'];
                }
                if (isset($classMapping['source'])) {
                    $metadata->source = $classMapping['source'];
                }
                // no break
            case 'Searchable':
                if (isset($classMapping['index'])) {
                    $metadata->index = $classMapping['index'];
                }
                if (isset($classMapping['type'])) {
                    $metadata->type = $classMapping['type'];
                }
                break;
            default:
                throw MappingException::classIsNotAValidDocument($className);
        }
    }
    
    private function fieldToArray($name, $fieldMapping)
    {
        $mapping = array();
        if (isset($fieldMapping['name'])) {
            $mapping['fieldName'] = $fieldMapping['name'];
        } else {
            $mapping['fieldName'] = $name;
        }
        
        if (isset($fieldMapping['type'])) {
            $mapping['type'] = $fieldMapping['type'];
            
            if ($fieldMapping['type'] == 'multi_field' && isset($fieldMapping['fields'])) {
                foreach ($fieldMapping['fields'] as $name => $subFieldMapping) {
                    $subFieldMapping = (array) $subFieldMapping;
                    $mapping['fields'][] = $this->fieldToArray($name, $subFieldMapping);
                }
            }
            
            if (in_array($fieldMapping['type'], array('nested', 'object')) && isset($fieldMapping['properties'])) {
                foreach ($fieldMapping['properties'] as $name => $subFieldMapping) {
                    $subFieldMapping = (array) $subFieldMapping;
                    $mapping['properties'][] = $this->fieldToArray($name, $subFieldMapping);
                }
            }
        }
        if (isset($fieldMapping['boost'])) {
            $mapping['boost'] = $fieldMapping['boost'];
        }
        if (isset($fieldMapping['includeInAll'])) {
            $mapping['includeInAll'] = (bool) $fieldMapping['includeInAll'];
        }
        if (isset($fieldMapping['index'])) {
            $mapping['index'] = $fieldMapping['index'];
        }
        if (isset($fieldMapping['analyzer'])) {
            $mapping['analyzer'] = $fieldMapping['analyzer'];
        }
        if (isset($fieldMapping['path'])) {
            $mapping['path'] = $fieldMapping['path'];
        }
        if (isset($fieldMapping['indexName'])) {
            $mapping['indexName'] = $fieldMapping['indexName'];
        }
        if (isset($fieldMapping['store'])) {
            $mapping['store'] = (bool) $fieldMapping['store'];
        }
        if (isset($fieldMapping['nullValue'])) {
            $mapping['nullValue'] = $fieldMapping['nullValue'];
        }
        if (isset($fieldMapping['geohash'])) {
            $mapping['geohash'] = (bool) $fieldMapping['geohash'];
        }
        if (isset($fieldMapping['geohash_precision'])) {
            $mapping['geohash_precision'] = $fieldMapping['geohash_precision'];
        }
        if (isset($fieldMapping['geohash_prefix'])) {
            $mapping['geohash_prefix'] = (bool) $fieldMapping['geohash_prefix'];
        }
        
        return $mapping;
    }
    
    private function rootToArray($rootMapping)
    {
        $mapping = array();
        if (isset($rootMapping['name'])) {
            $mapping['fieldName'] = $rootMapping['name'];
        }
        if (isset($rootMapping['id'])) {
            $mapping['id'] = $rootMapping['id'];
        }
        if (isset($rootMapping['match'])) {
            $mapping['match'] = $rootMapping['match'];
        }
        if (isset($rootMapping['unmatch'])) {
            $mapping['unmatch'] = $rootMapping['unmatch'];
        }
        if (isset($rootMapping['pathMatch'])) {
            $mapping['pathMatch'] = $rootMapping['pathMatch'];
        }
        if (isset($rootMapping['pathUnmatch'])) {
            $mapping['pathUnmatch'] = $rootMapping['pathUnmatch'];
        }
        if (isset($rootMapping['matchPattern'])) {
            $mapping['matchPattern'] = $rootMapping['matchPattern'];
        }
        if (isset($rootMapping['matchMappingType'])) {
            $mapping['matchMappingType'] = $rootMapping['matchMappingType'];
        }
        if (isset($rootMapping['value'])) {
            $mapping['value'] = $rootMapping['value'];
        }
        if (isset($rootMapping['mapping'])) {
            $subFieldMapping = (array) $rootMapping['mapping'];
            $field = $this->fieldToArray(null, $subFieldMapping);
            unset($field['fieldName']);
            $mapping['mapping'] = $field;
        }
        
        return $mapping;
    }
    
    private function parameterToArray($name, $parameterMapping)
    {
        $mapping = array();
        if (isset($parameterMapping['name'])) {
            $mapping['parameterName'] = $parameterMapping['name'];
        } else {
            $mapping['parameterName'] = $name;
        }
        
        if (isset($parameterMapping['type'])) {
            $mapping['type'] = $parameterMapping['type'];
        }
        
        return $mapping;
    }
    
    /**
     * {@inheritDoc}
     */
    protected function loadMappingFile($file)
    {
        return Yaml::parse($file);
    }
}
