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

use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Search\Mapping\MappingException;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class AnnotationDriver extends AbstractAnnotationDriver
{
    /**
     * {@inheritDoc}
     */
    protected $entityAnnotationClasses = array(
        'Doctrine\Search\Mapping\Annotations\Searchable' => 1,
        'Doctrine\Search\Mapping\Annotations\ElasticSearchable' => 2
    );

    /**
     * Registers annotation classes to the common registry.
     * This method should be called when bootstrapping your application.
     */
    public static function registerAnnotationClasses()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/DoctrineAnnotations.php');
    }

    /**
     * @param string                                               $className
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata $metadata
     *
     * @throws \ReflectionException
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $class = $metadata->getReflectionClass();

        if (!$class) {
            $class = new \ReflectionClass((string)$className);
        }

        $classAnnotations = $this->reader->getClassAnnotations($class);

        $classMapping = array();
        $validMapping = false;
        foreach ($classAnnotations as $annotation) {
            switch (get_class($annotation)) {
                case 'Doctrine\Search\Mapping\Annotations\ElasticSearchable':
                    $classMapping = (array)$annotation;
                    $classMapping['class'] = 'ElasticSearchable';
                    $validMapping = true;
                    break;
                case 'Doctrine\Search\Mapping\Annotations\Searchable':
                    $classMapping = (array)$annotation;
                    $classMapping['class'] = 'Searchable';
                    $validMapping = true;
                    break;
                case 'Doctrine\Search\Mapping\Annotations\ElasticRoot':
                    $rootMapping = (array)$annotation;
                    $metadata->mapRoot($this->rootToArray($rootMapping));
                    break;
            }
        }

        if (!$validMapping) {
            throw MappingException::classIsNotAValidDocument($className);
        }

        $this->annotateClassMetadata($classMapping, $metadata);

        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($property);
            foreach ($propertyAnnotations as $annotation) {
                switch (get_class($annotation)) {
                    case 'Doctrine\Search\Mapping\Annotations\Id':
                        $metadata->identifier = $property->getName();
                        break;
                    case 'Doctrine\Search\Mapping\Annotations\Parameter':
                        $mapping = $this->parameterToArray($property->getName(), (array)$annotation);
                        $metadata->mapParameter($mapping);
                        break;
                    case 'Doctrine\Search\Mapping\Annotations\Field':
                    case 'Doctrine\Search\Mapping\Annotations\ElasticField':
                    case 'Doctrine\Search\Mapping\Annotations\SolrField':
                        $mapping = $this->fieldToArray($property->getName(), (array)$annotation);
                        $metadata->mapField($mapping);
                        break;
                }
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

            if (in_array($fieldMapping['type'], array('multi_field', 'text')) && isset($fieldMapping['fields'])) {
                foreach ($fieldMapping['fields'] as $name => $subFieldMapping) {
                    $subFieldMapping = (array)$subFieldMapping;
                    $mapping['fields'][] = $this->fieldToArray($name, $subFieldMapping);
                }
            }

            if (in_array($fieldMapping['type'], array('nested', 'object')) && isset($fieldMapping['properties'])) {
                foreach ($fieldMapping['properties'] as $name => $subFieldMapping) {
                    $subFieldMapping = (array)$subFieldMapping;
                    $mapping['properties'][] = $this->fieldToArray($name, $subFieldMapping);
                }
            }
        }
        if (isset($fieldMapping['boost'])) {
            $mapping['boost'] = $fieldMapping['boost'];
        }
        if (isset($fieldMapping['includeInAll'])) {
            $mapping['includeInAll'] = (bool)$fieldMapping['includeInAll'];
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
            $mapping['store'] = (bool)$fieldMapping['store'];
        }
        if (isset($fieldMapping['nullValue'])) {
            $mapping['nullValue'] = $fieldMapping['nullValue'];
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
            $subFieldMapping = (array)$rootMapping['mapping'];
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
}
