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
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Search\Mapping\Annotations as Search;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Search\Exception\Driver as DriverException;
use Doctrine\Search\Mapping\DependentMappingDriver;



/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class AnnotationDriver extends AbstractAnnotationDriver implements DependentMappingDriver
{
    /**
     * {@inheritDoc}
     */
    protected $entityAnnotationClasses = array(
        'Doctrine\\Search\\Mapping\\Annotations\\Searchable' => 1,
        'Doctrine\\Search\\Mapping\\Annotations\\ElasticSearchable' => 2,
        'Doctrine\\Search\\Mapping\\Annotations\\ElasticRoot' => 3,
    );

    protected $entityRootAnnotationClass = 'Doctrine\\Search\\Mapping\\Annotations\\ElasticRoot';

    protected $entityIdAnnotationClass = 'Doctrine\\Search\\Mapping\\Annotations\\Id';

    protected $entityParamAnnotationClass = 'Doctrine\\Search\\Mapping\\Annotations\\Parameter';

    /**
     * Document fields annotation classes, ordered by precedence.
     */
    protected $entityFieldAnnotationClasses = array(
        'Doctrine\\Search\\Mapping\\Annotations\\Id',        //Only here for convenience
        'Doctrine\\Search\\Mapping\\Annotations\\Parameter', //Only here for convenience
        'Doctrine\\Search\\Mapping\\Annotations\\Field',
        'Doctrine\\Search\\Mapping\\Annotations\\ElasticField',
        'Doctrine\\Search\\Mapping\\Annotations\\SolrField',
    );

    /**
     * @var MappingDriver
     */
    private $parentDriver;

    /**
     * @param MappingDriver $driver
     */
    public function setParentDriver(MappingDriver $driver)
    {
        $this->parentDriver = $driver;
    }

    /**
     * @param string $className
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata $metadata
     *
     * @throws \ReflectionException
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();

        if (!$reflClass) {
            $reflClass = new \ReflectionClass((string)$className);
        }

        $reflProperties = $reflClass->getProperties();
        $reflMethods = $reflClass->getMethods();

        $this->extractClassAnnotations($reflClass, $metadata);
        $this->extractPropertiesAnnotations($reflProperties, $metadata);
        $this->extractMethodsAnnotations($reflMethods, $metadata);
    }


    /**
     * This function extracts the class annotations for search from the given reflected class and writes
     * them into metadata.
     *
     * @param \ReflectionClass $reflClass
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata    $metadata
     *
     * @return ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata
     *
     * @throws DriverException\ClassIsNotAValidDocumentException|DriverException\PropertyDoesNotExistsInMetadataException
     */
    private function extractClassAnnotations(\ReflectionClass $reflClass, ClassMetadata $metadata)
    {
        $documentsClassAnnotations = array();
        foreach ($this->reader->getClassAnnotations($reflClass) as $annotation) {
            foreach ($this->entityAnnotationClasses as $annotationClass => $index) {
                if ($annotation instanceof $this->entityRootAnnotationClass) {
                    $metadata->addRootMapping($annotation);
                    break;
                } elseif ($annotation instanceof $annotationClass) {
                    $documentsClassAnnotations[$index] = $annotation;
                    break;
                }
            }
        }

        if (!$documentsClassAnnotations) {
            throw new DriverException\ClassIsNotAValidDocumentException($metadata->getName());
        }

        //choose only one (the first one)
        $annotationClass = reset($documentsClassAnnotations);
        $reflClassAnnotations = new \ReflectionClass($annotationClass);
        $metadata = $this->addValuesToMetadata(
            $reflClassAnnotations->getProperties(),
            $metadata,
            $annotationClass
        );

        return $metadata;
    }

    /**
     * Extract the property annotations.
     *
     * @param \ReflectionProperty[] $reflProperties
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata         $metadata
     *
     * @return ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata
     */
    private function extractPropertiesAnnotations(array $reflProperties, ClassMetadata $metadata)
    {
        $documentsFieldAnnotations = array();
        foreach ($reflProperties as $reflProperty) {
            foreach ($this->reader->getPropertyAnnotations($reflProperty) as $annotation) {
                foreach ($this->entityFieldAnnotationClasses as $fieldAnnotationClass) {
                    if ($annotation instanceof $fieldAnnotationClass) {
                        if ($annotation instanceof $this->entityIdAnnotationClass) {
                            $metadata->setIdentifier($reflProperty->name);
                        } elseif ($annotation instanceof $this->entityParamAnnotationClass) {
                            $metadata->addParameterMapping($reflProperty, $annotation);
                        } else {
                            $metadata->addFieldMapping($reflProperty, $annotation);
                        }
                        continue 2;
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Extract the methods annotations.
     *
     * @param \ReflectionMethod[]   $reflMethods
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata         $metadata
     *
     * @return ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata
     */
    private function extractMethodsAnnotations(array $reflMethods, ClassMetadata $metadata)
    {
        $documentsFieldAnnotations = array();
        foreach ($reflMethods as $reflMethod) {
            foreach ($this->reader->getMethodAnnotations($reflMethod) as $annotation) {
                foreach ($this->entityFieldAnnotationClasses as $fieldAnnotationClass) {
                    if ($annotation instanceof $fieldAnnotationClass) {
                        $metadata->addFieldMapping($reflMethod, $annotation);
                        continue 2;
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * @param \ReflectionProperty[] $reflectedClassProperties
     * @param ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata         $metadata
     * @param string                $class
     *
     * @return ClassMetadata|\Doctrine\Search\Mapping\ClassMetadata
     *
     * @throws DriverException\PropertyDoesNotExistsInMetadataException
     */
    private function addValuesToMetadata(array $reflectedClassProperties, ClassMetadata $metadata, $class)
    {
        foreach ($reflectedClassProperties as $reflectedProperty) {
            $propertyName = $reflectedProperty->getName();

            if (false === property_exists($metadata, $propertyName)) {
                throw new DriverException\PropertyDoesNotExistsInMetadataException($reflectedProperty->getName());
            } else {
                if (!is_null($class->$propertyName)) {
                    $metadata->$propertyName = $class->$propertyName;
                }
            }
        }

        return $metadata;
    }

    public function getAllClassNames()
    {
        if ($this->classNames !== NULL) {
            return $this->classNames;
        }

        if ($this->parentDriver === NULL) {
            return parent::getAllClassNames();
        }

        $classes = array();
        foreach ($this->parentDriver->getAllClassNames() as $className) {
            if (!$this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        return $this->classNames = $classes;
    }

}
