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

namespace Doctrine\Search\Mapping;

use Doctrine\Search\SearchManager;
use Doctrine\Search\Configuration;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Search\Events;
use Doctrine\Search\Event\LoadClassMetadataEventArgs;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a search backend should be configured.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class ClassMetadataFactory extends AbstractClassMetadataFactory
{
    /**
     * @var SearchManager
     */
    private $sm;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var MappingDriver
     */
    private $driver;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $evm;
    
    /**
     * {@inheritDoc}
     */
    protected function initialize()
    {
        $this->driver = $this->config->getMetadataDriverImpl();
        $this->evm = $this->sm->getEventManager();
        $this->initialized = true;
    }

    /**
     * Sets the SearchManager instance for this class.
     *
     * @param SearchManager $sm The SearchManager instance
     */
    public function setSearchManager(SearchManager $sm)
    {
        $this->sm = $sm;
    }

    /**
     * Sets the Configuration instance
     *
     * @param Configuration $config
     */
    public function setConfiguration(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Get the fully qualified class-name from the namespace alias.
     *
     * @param string $namespaceAlias
     * @param string $simpleClassName
     *
     * @return string
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        // TODO: Implement getFqcnFromAlias() method.
    }

    /**
     * Return the mapping driver implementation.
     *
     * @return MappingDriver
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * Actually load the metadata from the underlying metadata
     *
     * @param ClassMetadata $class
     * @param ClassMetadata $parent
     * @param bool $rootEntityFound
     *
     * @return void
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {
        //Manipulates $classMetadata;
        $this->driver->loadMetadataForClass($class->getName(), $class);
        
        if ($this->evm->hasListeners(Events::loadClassMetadata)) {
            $eventArgs = new LoadClassMetadataEventArgs($class, $this->sm);
            $this->evm->dispatchEvent(Events::loadClassMetadata, $eventArgs);
        }
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

    /**
     * Wakeup reflection after ClassMetadata gets unserialized from cache.
     *
     * @param BaseClassMetadata $class
     * @param ReflectionService $reflService
     *
     * @return void
     */
    protected function wakeupReflection(BaseClassMetadata $class, ReflectionService $reflService)
    {
        // TODO: Implement wakeupReflection() method.
    }

    /**
     * Initialize Reflection after ClassMetadata was constructed.
     *
     * @param ClassMetadata $class
     * @param ReflectionService $reflService
     * @return void
     */
    protected function initializeReflection(BaseClassMetadata $class, ReflectionService $reflService)
    {
        // TODO: Implement initializeReflection() method.
    }

    protected function isEntity(BaseClassMetadata $class)
    {

    }
}
