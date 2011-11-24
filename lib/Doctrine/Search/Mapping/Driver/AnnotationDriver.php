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

use Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\Common\Annotations\Reader,
    Doctrine\Search\Mapping\Driver\Driver,
    Doctrine\Search\Mapping\Annotations as Search,
    Doctrine\Search\Mapping\ClassMetadata,
    Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 * Ideas copied from the mongodb-odm-project
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class AnnotationDriver implements Driver
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
     * Contains the paths to the to be readed directories
     *
     * @var array $paths
     */
    private $paths;

    /**
     * The annotation reader.
     *
     * @var Reader
     */
    private $reader;

    /**
     * Registers annotation classes to the common registry.
     *
     * This method should be called when bootstrapping your application.
     */
    public static function registerAnnotationClasses()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/DoctrineAnnotations.php');
    }

    /**
     * Initializes a new AnnotationDriver that uses the given Reader for reading
     * docblock annotations.
     *
     * @param $reader Reader The annotation reader to use.
     * @param $paths
     */
    public function __construct(Reader $reader, array $paths)
    {
        $this->reader = $reader;

        if ($paths) {
            $this->addPaths((array) $paths);
        }
    }

    /*
     * Loads the metadata of the given class
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventargs)
    {
       $documentsAnnotations = array();
       $reflClass = $eventargs->getClassMetadata()->getReflectionClass();
       $reflClass = $eventargs->getClassMetadata()->getReflectionMethod();

       $classAnnotations = $this->extractClassAnnotations($reflClass);
       $methodAnnotations = $this->extractMethodAnnotations($reflClass);

       $documentsAnnotations = array_merge($classAnnotations, $methodAnnotations);

       var_dump($documentsAnnotations);
    }

    private function extractClassAnnotations(\ReflectionClass $reflClass)
    {
    	$documentsClassAnnotations = array();
    	foreach ($this->reader->getClassAnnotations($reflClass) as $annotation) {
    		foreach (self::$documentAnnotationClasses as $i => $annotationClass) {
    			if ($annotation instanceof $annotationClass) {
    				$documentsClassAnnotations[$i] = $annotation;
    				continue 2;
    			}
    		}
    	}

    	return $documentsClassAnnotations;
    }

    private function extractMethodAnnotations(\ReflectionClass $reflClass)
    {
    	$documentsMethodAnnotations = array();
    	foreach ($this->reader->getMethodAnnotations($reflClass) as $annotation) {
    		foreach (self::$documentMethodAnnotationClasses as $i => $methodAnnotationClass) {
    			if ($annotation instanceof $methodAnnotationClass) {
    				$documentsMethodAnnotations[$i] = $annotation;
    				continue 2;
    			}
    		}
    	}

    	return $documentsMethodAnnotations;
    }

    /**
     *
     * @param array $paths
     */
    public function addPaths(array $paths)
    {
        $this->paths = $paths;
    }
}
