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
use Doctrine\Search\Mapping\ClassMetadataFactory;

/**
 * Doctrine SearchManager-
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
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @param Configuration          $config
     * @param SearchClientInterface  $client
     */
    public function __construct(Configuration $config, SearchClientInterface $client)
    {
        $this->configuration = $config;
        $this->searchClient = $client;

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
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     * @return void
     */
    public function setObjectManager(ObjectManager $om)
    {
        $this->objectManager = $om;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param string $className
     * 
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function loadClassMetadata($className)
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