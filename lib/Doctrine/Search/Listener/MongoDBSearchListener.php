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

    public function __construct(SearchManager $sm = null)
    {
        $this->searchManager = $sm ? : new SearchManager();
    }

     /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventargs
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventargs)
    {
        $this->searchManager->setObjectManager($eventargs->getDocumentManager());
        $reflClass = $eventargs->getClassMetadata()->getReflectionClass();
        $this->searchManager->loadClassMetadata($reflClass);
    }
}