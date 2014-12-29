<?php

namespace Doctrine\Search\Mapping;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface TypeMetadataFactory
{

	/**
	 * @param string $className
	 * @return TypeMetadata
	 */
	public function createTypeMetadata($className);

}
