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

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Search\SearchClientInterface;
use Doctrine\Common\EventManager;
use Doctrine\Search\ElasticSearch\Client;
use Doctrine\Search\Configuration;
use Doctrine\Common\Annotations\AnnotationReader;


/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class SearchManager
{
    /**
     * @var SearchClient
     */
    private $searchClient;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     *
     * @var object
     */
    private $metadataFactory;

    /**
     *
     * @var object
     */
    private $annotationReader;

    /**
     *
     * @param ObjectManager $om
     * @param Configuration $conf
     * @param SearchClientInterface $sc
     */
    public function __construct(Configuration $conf = null,
        SearchClientInterface $sc = null,
        Reader $reader = null)
    {
        $this->configuration = $conf ? : new Configuration();
        $this->searchClient = $sc ? : new Client();
        $this->annotationReader = $reader ? : new AnnotationReader();

        $metadataFactoryClassName = $this->configuration->getClassMetadataFactoryName();
        $this->metadataFactory = new $metadataFactoryClassName();
        $this->metadataFactory->setSearchManager($this);
        $this->metadataFactory->setConfiguration($this->configuration);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return AnnotationReader
     */
    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @return void
     */
    public function setObjectManager(ObjectManager $om)
    {
         $this->objectManager = $om;
    }

    /**
     * @param ReflectionClass $reflectedClass
     * @return void
     */
    public function loadClassMetadata(\ReflectionClass $reflectedClass)
    {
        $this->metadataFactory->loadClassMetadata($reflectedClass);
    }

     /**
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->metadataFactory;
    }


    /**
     *
     * @param String $index
     * @param String $type
     * @param String $query
     */
    public function find($index = null, $type = null, $query = null)
    {
        $this->searchClient->find($index, $type, $query);
    }

    /**
     * Adds the object to the index
     *
     * @param object $object
     *
     * @throws UnexpectedTypeException
     */
    public function persist($object)
    {
        $this->searchClient->createIndex($index, $type, $query);
    }

    /**
     * Remove the object from the index
     *
     * @param object $object
     *
     * @throws UnexpectedTypeException
     */
    public function remove($object)
    {
    }

    /**
     * Bulk action
     *
     * @param object $object
     *
     * @throws UnexpectedTypeException
     */
    public function bulk($object)
    {

    }

    /**
     * Commit all changes
     *
     * @return boolean
     */
    public function commit()
    {
    }
}