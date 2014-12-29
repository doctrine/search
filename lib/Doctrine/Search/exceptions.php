<?php

namespace Doctrine\Search;



interface Exception
{

}



class DefaultSerializerNotProvidedException extends \LogicException implements Exception
{

}



class DoctrineSearchException extends \RuntimeException implements Exception
{

}



class NotImplementedException extends \LogicException implements Exception
{

}


class InvalidMetadataException extends \LogicException implements Exception
{

}


class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



class InvalidStateException extends \RuntimeException implements Exception
{

}



class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}



/**
 * Exception thrown when an search query unexpectedly does not return any results.
 *
 * @author robo
 * @since 2.0
 */
class NoResultException extends \RuntimeException implements Exception
{

	public function __construct($message = NULL)
	{
		parent::__construct($message ?: 'No result was found for query although at least one row was expected.');
	}
}



class UnexpectedTypeException extends \LogicException implements Exception
{

	public function __construct($value, $expected)
	{
		parent::__construct(
			sprintf(
				'Expected argument of type "%s", "%s" given',
				$expected,
				(is_object($value) ? get_class($value) : gettype($value))
			)
		);
	}
}
