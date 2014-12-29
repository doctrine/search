<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Doctrine\Search;

use Doctrine;
use Doctrine\Common\EventSubscriber;
use Doctrine\Search\Event\LifecycleEventArgs;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SearchableListener implements EventSubscriber
{

	/**
	 * @var SearchManager
	 */
	private $sm;



	public function __construct(SearchManager $sm)
	{
		$this->sm = $sm;
	}



	public function getSubscribedEvents()
	{
		return array(
			Doctrine\ORM\Events::prePersist => 'prePersist',
			Doctrine\ORM\Events::preUpdate => 'prePersist',
			Doctrine\ORM\Events::preRemove => 'preRemove',
			Doctrine\ORM\Events::postFlush => 'postFlush',
		);
	}



	public function prePersist(LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof Searchable) {
			$this->sm->persist($oEntity);
		}
	}



	public function preRemove(LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof Searchable) {
			$this->sm->remove($oEntity);
		}
	}



	public function postFlush()
	{
		$this->sm->flush();
	}

}
