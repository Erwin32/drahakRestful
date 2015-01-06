<?php
namespace Drahak\Restful\Converters;

use Nette\Object;
use Drahak\Restful\Utils\Strings;

/**
 * CamelCaseConverter
 * @package Drahak\Restful\Converters
 * @author Drahomír Hanák
 */
class CamelCaseConverter extends Object implements IConverter
{

    /**
     * Converts resource data keys to camelCase
     * @param array $resource
     * @return array
     */
	public function convert(array $resource)
	{
		$this->convertToCamel($resource);
		return $resource;
	}

	/**
	 * Convert array keys to camel case
	 * @param array|\Traversable $array
	 */
	private function convertToCamel(&$array)
	{
		if ($array instanceof \Traversable) {
			$array = iterator_to_array($array);
		}

		foreach (array_keys($array) as $key) {
			$value = &$array[$key];
			unset($array[$key]);

			$transformedKey = Strings::toCamelCase($key);
			if (is_array($value) || $value instanceof \Traversable) {
				$this->convertToCamel($value);
			}
			$array[$transformedKey] = $value;
			unset($value);
		}
	}

}
