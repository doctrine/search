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

use Doctrine\Search\SearchManager,
    Doctrine\Search\Configuration,
    Doctrine\Search\Mapping\ClassMetadata;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a search backend should be configured.
 *
 * Ideas copied from the mongodb-odm-project
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class ClassMetadataFactory implements \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
{
    /** The SearchManager instance */
    private $sm;

    /** The Configuration instance */
    private $config;

    /** The array of loaded ClassMetadata instances */
    private $loadedMetadata;

    /** The used metadata driver. */
    private $driver;

    /** The event manager instance */
    private $evm;

    /** Whether factory has been lazily initialized yet */
    private $initialized = false;

    private function initialize()
    {
        $this->driver = $this->config->getMetadataDriverImpl();
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
     * Gets the array of loaded ClassMetadata instances.
     *
     * @return array $loadedMetadata The loaded metadata.
     */
    public function getLoadedMetadata()
    {
        return $this->loadedMetadata;
    }

    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        if ( ! $this->initialized) {
            $this->initialize();
        }

        $metadata = array();
        foreach ($this->driver->getAllClassNames() as $className) {
            $metadata[] = $this->getMetadataFor($className);
        }

        return $metadata;
    }

    /**
     * Loads the metadata of the class in question
     *
     * @param ReflectionClass $reflClass The reflected Class to load Searchmetadata for.
    */
    public function loadClassMetadata(\ReflectionClass $reflClass)
    {
        if (false == $this->initialized) {
            $this->initialize();
        }

        $classMetadata = $this->newClassMetadataInstance($reflClass->getName());

        // Invoke driver
        $this->driver->loadMetadataForClass($reflClass, $classMetadata);
        var_dump($reflClass->getName());
        $this->setMetadataFor($reflClass->getName(), $classMetadata);
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     * @return boolean TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @param string $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->loadedMetadata[$className] = $class;
    }

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return Doctrine\Search\Mapping\ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className);
    }

    /**
     * @param $className
     * @return void
     */
    public function isTransient($className)
    {

    }

    /**
     * Get array of parent classes for the given document class
     *
     * @param string $name
     * @return array $parentClasses
     */
    protected function getParentClasses($name)
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = array();
        foreach (array_reverse(class_parents($name)) as $parentClass) {
            if ( ! $this->driver->isTransient($parentClass)) {
                $parentClasses[] = $parentClass;
            }
        }
        return $parentClasses;
    }


    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     * @return Doctrine\Search\Mapping\ClassMetadata
     */
    function getMetadataFor($className)
    {
        return $this->loadedMetadata[$className];
    }
}
