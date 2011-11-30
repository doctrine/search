<?php
namespace Doctrine\Search\Listener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;

/**
 * Listener for MongoDB Events
 *
 * @todo: add SearchListenerInterface
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */

class MongoDBSearchListener
{
    private $searchManager;

    private $classMetaData;

    public function __construct(SearchManager $sm = null, ClassMetadata $classMetaData = null)
    {
        $this->searchManager = $sm ? : new SearchManager();
        $this->classMetaData = $classMetaData ? : new ClassMetadata();
    }

     /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventargs
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventargs)
    {
        $omConfiguration = $eventargs->getDocumentManager()->getConfiguration();
        $driver = $omConfiguration->getMetadataDriverImpl();
        $paths = $driver->getPaths();

        $reader = $this->searchManager->getAnnotationReader();
        $annotationDriverName = $this->searchManager->getConfiguration()->getMetadataDriverImpl();
        $annotationDriver = new $annotationDriverName($reader, $paths);
        $annotationDriver->loadClassMetaData($eventargs, $this->classMetaData);
    }
}