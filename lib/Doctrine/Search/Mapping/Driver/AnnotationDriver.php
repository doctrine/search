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

namespace Doctrine\Search\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as BaseAnnotationDriver,
Doctrine\Search\Mapping\Annotations as Search,
Doctrine\Search\Mapping\ClassMetadata as SearchMetadata,
Doctrine\Common\Persistence\Mapping\ClassMetadata,
Doctrine\Search\Exception\Driver as DriverException;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class AnnotationDriver extends BaseAnnotationDriver
{
    /**
     * Document annotation classes, ordered by precedence.
     */
    static private $documentAnnotationClasses = array(
        'Doctrine\\Search\\Mapping\\Annotations\\Searchable',
        'Doctrine\\Search\\Mapping\\Annotations\\ElasticSearchable',
    );

    /**
     * Document fields annotation classes, ordered by precedence.
     */
    static private $documentFieldAnnotationClasses = array(
        'Doctrine\\Search\\Mapping\\Annotations\\Field',
        'Doctrine\\Search\\Mapping\\Annotations\\ElasticField',
        'Doctrine\\Search\\Mapping\\Annotations\\SolrField',
    );


    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();

        if (!$reflClass) {
            $reflClass = new \ReflectionClass((string)$className);
        }

        $reflProperties = $reflClass->getProperties();

        $this->extractClassAnnotations($reflClass, $metadata);
        $this->extractPropertiesAnnotations($reflProperties, $metadata);

    }


    /**
     * This function extracts the class annotations for search from the given reflected class and writes
     * them into metadata.
     *
     * @param \ReflectionClass $reflClass
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     * @throws \Doctrine\Search\Exception\Driver\ClassIsNotAValidDocumentException|\Doctrine\Search\Exception\Driver\PropertyDoesNotExistsInMetadataException
     */
    private function extractClassAnnotations(\ReflectionClass $reflClass, ClassMetadata $metadata)
    {
        $documentsClassAnnotations = array();
        foreach ($this->reader->getClassAnnotations($reflClass) as $annotation) {
            foreach (self::$documentAnnotationClasses as $i => $annotationClass) {
                if ($annotation instanceof $annotationClass) {
                    $documentsClassAnnotations[$i] = $annotation;
                    break 2;
                }
            }
        }

        if (!$documentsClassAnnotations) {
            throw new DriverException\ClassIsNotAValidDocumentException($metadata->getName());
        }

        //choose only one (the first one)
        $annotationClass = $documentsClassAnnotations[0];
        $reflClassAnnotations = new \ReflectionClass($annotationClass);
        $metadata = $this->addValuesToMetdata($reflClassAnnotations->getProperties(),
            $metadata,
            $annotationClass);

        return $metadata;
    }

    /**
     * @param array $reflProperties
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata|mixed
     */
    private function extractPropertiesAnnotations(array $reflProperties, ClassMetadata $metadata)
    {
        $documentsFieldAnnotations = array();
        foreach ($reflProperties as $reflProperty) {
            foreach ($this->reader->getPropertyAnnotations($reflProperty) as $annotation) {
                foreach (self::$documentFieldAnnotationClasses as $i => $fieldAnnotationClass) {
                    if ($annotation instanceof $fieldAnnotationClass) {
                        $documentsFieldAnnotations[$i] = $annotation;
                        continue 2;
                    }
                }
            }
        }

        foreach ($documentsFieldAnnotations as $documentsFieldAnnotation) {

            $reflFieldAnnotations = new \ReflectionClass($documentsFieldAnnotation);
            $metadata = $this->addValuesToMetdata($reflFieldAnnotations->getProperties(),
                $metadata,
                $documentsFieldAnnotation);

        }

        return $metadata;
    }

    /**
     * @param array $reflectedClassProperties
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param $class
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     * @throws \Doctrine\Search\Exception\Driver\PropertyDoesNotExistsInMetadataException
     */
    private function addValuesToMetdata(array $reflectedClassProperties, ClassMetadata $metadata, $class)
    {
        foreach ($reflectedClassProperties as $reflectedProperty) {
            $propertyName = $reflectedProperty->getName();

            if (false === property_exists($metadata, $propertyName)) {
                throw new DriverException\PropertyDoesNotExistsInMetadataException($reflectedProperty->getName());
            } else {
                $metadata->$propertyName = $class->$propertyName;
                /*I am not sure if that is needed
                 * $metadata->addField($reflectedProperty);
                $metadata->addFieldMapping($reflectedProperty);*/
            }
        }

        return $metadata;
    }
}
