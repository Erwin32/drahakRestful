<?php
namespace Drahak\Restful\Converters;

use Nette\Object;
use Traversable;
use DateTime;

/**
 * DateTimeConverter
 * @package Drahak\Restful\Converters
 * @author Drahomír Hanák
 */
class DateTimeConverter extends Object implements IConverter
{

	/** DateTime format */
	private $format = 'c';

	/**
	 * @param string $format of date time
	 */
	public function __construct($format = 'c')
	{
		$this->format = $format;
	}

    /**
     * Converts DateTime objects in resource to string
     * @param array $resource
     * @return array
     */
	public function convert(array $resource)
	{
		$data = $this->parseDateTimeToString($resource);
		return $data;
	}

	/**
	 * @param $array
	 * @return array
	 */
	private function parseDateTimeToString($array)
	{
		if (!is_array($array)) {
			if ($array instanceof DateTime || interface_exists('DateTimeInterface') && $array instanceof \DateTimeInterface) {
				return $array->format($this->format);
			}
			return $array;
		}

		foreach ($array as $key => $value) {
			if ($value instanceof Traversable || is_array($array)) {
				$array[$key] = $this->parseDateTimeToString($value);
			}
		}
		return $array;
	}

}
