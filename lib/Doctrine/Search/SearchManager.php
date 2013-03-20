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
use Doctrine\Common\Annotations\Reader;
use Doctrine\Search\Exception\UnexpectedTypeException;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Mapping\ClassMetadataFactory;

/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class SearchManager
{
    /**
     * @var SearchClientInterface
     */
    private $searchClient;

    /**
     * @var Configuration $configuration
     */
    private $configuration;

    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * Constructor
     *
     * @param Configuration         $config
     * @param SearchClientInterface $sc
     */
    public function __construct(Configuration $config, SearchClientInterface $sc)
    {
        $this->configuration = $config;
        $this->searchClient = $sc;

        $this->metadataFactory = $this->configuration->getClassMetadataFactory();
        $this->metadataFactory->setSearchManager($this);
        $this->metadataFactory->setConfiguration($this->configuration);
        $this->metadataFactory->setCacheDriver($this->configuration->getMetadataCacheImpl());
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Loads class metadata for the given class
     * 
     * @param string $className
     * 
     * @return ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor((string)$className);
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
     * @param string $index
     * @param string $type
     * @param string $query
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
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        //$this->searchClient->createIndex($index, $type, $query);
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
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }
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
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }
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