<?php
namespace Drahak\Restful\Converters;

use Nette\Object;

/**
 * ResourceConverter
 * @package Drahak\Restful\Converters
 *
 * @property-read IConverter[] $converters
 */
class ResourceConverter extends Object
{

	/** @var IConverter[] */
	private $converters = array();

	/**
	 * Get converters
	 * @return IConverter[]
	 */
	public function getConverters()
	{
		return $this->converters;
	}

	/**
	 * Add resource data converter to list
	 * @param IConverter $converter
	 * @return ResourceConverter
	 */
	public function addConverter(IConverter $converter)
	{
		$this->converters[] = $converter;
		return $this;
	}

	/**
	 * Converts data from resource using converters
	 * @param array $data
	 * @return array
	 */
	public function convert(array $data)
	{
		/** @var IConverter $converter */
		foreach ($this->converters as $converter) {
			$data = $converter->convert($data);
		}

		return $data;
	}

}
