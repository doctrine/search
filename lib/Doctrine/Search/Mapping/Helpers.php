<?php

namespace Doctrine\Search\Mapping;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @see http://php.net/manual/en/function.ucwords.php#92092
 */
class Helpers
{

	/**
	 * underscored to lower-camelcase
	 * e.g. "this_method_name" -> "thisMethodName"
	 *
	 * @param string $string
	 * @return string
	 */
	public function camelCase($string)
	{
		return preg_replace('/_(.?)/e', "strtoupper('$1')", $string);
	}



	/**
	 * camelcase (lower or upper) to underscored
	 * e.g. "thisMethodName" -> "this_method_name"
	 * e.g. "ThisMethodName" -> "this_method_name"
	 *
	 * @param string $string
	 * @return string
	 */
	public function underscore($string)
	{
		return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $string));
	}

}
