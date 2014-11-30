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
        $element = $this->getElement($className);
        
        switch ($element['class']) {
            case 'ElasticSearchable':
                if (isset($element['numberOfShards'])) {
                    $metadata->numberOfShards = $element['numberOfShards'];
                }
                if (isset($element['numberOfReplicas'])) {
                    $metadata->numberOfReplicas = $element['numberOfReplicas'];
                }
                if (isset($element['parent'])) {
                    $metadata->parent = $element['parent'];
                }
                if (isset($element['timeToLive'])) {
                    $metadata->timeToLive = $element['timeToLive'];
                }
                if (isset($element['boost'])) {
                    $metadata->boost = $element['boost'];
                }
                if (isset($element['source'])) {
                    $metadata->source = $element['source'];
                }
            case 'Searchable':
                if (isset($element['index'])) {
                    $metadata->index = $element['index'];
                }
                if (isset($element['type'])) {
                    $metadata->type = $element['type'];
                }
                break;
            default:
                throw MappingException::classIsNotAValidDocument($className);
        }
        
        // Evaluate id
        if (isset($element['id'])) {
            $metadata->identifier = $element['id'];
        }
        
        // Evaluate root mappings
        if (isset($element['root'])) {
            foreach ($element['root'] as $rootMapping) {
                $metadata->addRootMapping($this->rootToArray($rootMapping));
            }
        }
        
        // Evaluate field mappings
        if (isset($element['fields'])) {
            foreach ($element['fields'] as $name => $fieldMapping) {
                $mapping = $this->fieldToArray($name, $fieldMapping);
                $metadata->mapField($mapping);
            }
        }
        
        return $metadata;
    }

    private function fieldToArray($name, $fieldMapping)
    {
        if (isset($fieldMapping['name'])) {
            $mapping['fieldName'] = $fieldMapping['name'];
        } else {
            $mapping['fieldName'] = $name;
        }
        
        if (isset($fieldMapping['type'])) {
            $mapping['type'] = $fieldMapping['type'];
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
        
        if ($fieldMapping['type'] == 'multi_field' && isset($fieldMapping['fields'])) {
            foreach ($fieldMapping['fields'] as $name => $fieldMapping) {
                $mapping['fields'][] = $this->fieldToArray($name, $fieldMapping);
            }
        }

        if (in_array($fieldMapping['type'], array('nested', 'object')) && isset($fieldMapping['properties'])) {
            foreach ($fieldMapping['properties'] as $name => $fieldMapping) {
                $mapping['properties'][] = $this->fieldToArray($name, $fieldMapping);
            }
        }
        
        return $mapping;
    }
    
    private function rootToArray($rootMapping)
    {
        if (isset($rootMapping['name'])) {
            $mapping['name'] = $rootMapping['name'];
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
            foreach($rootMapping['mapping'] as $fieldMapping) {
                $field = $this->fieldToArray(null, $fieldMapping);
                if(isset($fieldMapping['name'])) {
                    $field['name'] = $fieldMapping['name'];
                }
                unset($field['fieldName']);
                $mapping['mapping'][] = $field;
            }
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
