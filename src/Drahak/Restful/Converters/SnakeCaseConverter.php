<?php
namespace Drahak\Restful\Converters;

use Drahak\Restful\Utils\Strings;
use Nette\Object;

/**
 * SnakeCaseConverter
 * @package Drahak\Restful\Converters
 * @author Drahomír Hanák
 */
class SnakeCaseConverter extends Object implements IConverter
{

    /**
     * Converts resource data keys to snake_case
     * @param array $resource
     * @return array
     */
    public function convert(array $resource)
	{
		$this->convertToSnake($resource);
		return $resource;
	}

	/**
	 * Convert array keys to snake case
	 * @param array|\Traversable $array
	 */
	private function convertToSnake(&$array)
	{
		if ($array instanceof \Traversable) {
			$array = iterator_to_array($array);
		}

		foreach (array_keys($array) as $key) {
			$value = &$array[$key];
			unset($array[$key]);

			$transformedKey = Strings::toSnakeCase($key);
			if (is_array($value) || $value instanceof \Traversable) {
				$this->convertToSnake($value);
			}
			$array[$transformedKey] = $value;
			unset($value);
		}
	}

}
