<?php
namespace Drahak\Restful\Utils;

use Nette;

/**
 * Strings util class
 * @package Drahak\Restful\Utils
 * @author Drahomír Hanák
 */
class Strings extends Nette\Utils\Strings
{

	/**
	 * Converts first letter to lower case
	 * @param string $s
	 * @return string
	 */
	public static function firstLower($s)
	{
		return self::lower(self::substring($s, 0, 1)) . self::substring($s, 1);
	}

	/**
	 * Converts string to camelCase
	 * @param string $string
	 * @return string
	 */
	public static function toCamelCase($string)
	{
		$func = function($matches) {
			return self::upper($matches[2]);
		};

		return self::firstLower(self::replace($string, '/(_| |-)([a-zA-Z])/', $func));
	}

	/**
	 * Converts string to PascalCase
	 * @param string $string
	 * @return string
	 */
	public static function toPascalCase($string)
	{
		return self::firstUpper(self::toCamelCase($string));
	}

	/**
	 * Converts string to snake_case
	 * @param string $string
	 * @return string
	 */
	public static function toSnakeCase($string)
	{
		$replace = array(' ', '-');
		return self::trim(
			self::lower(
				str_replace($replace, '_', self::replace(ltrim($string, '!'), '/([^_]+[a-z -]{1})([A-Z])/U', '$1_$2'))
			)
		);
	}

}
