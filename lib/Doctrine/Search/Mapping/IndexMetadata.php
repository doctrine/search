<?php

namespace Doctrine\Search\Mapping;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class IndexMetadata
{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $numberOfShards = 1;

	/**
	 * @var int
	 */
	public $numberOfReplicas = 1;

	/**
	 * @var array
	 */
	public $charFilter = array();

	/**
	 * @var array
	 */
	public $filter = array();

	/**
	 * @var array
	 */
	public $analyzer = array();

}
