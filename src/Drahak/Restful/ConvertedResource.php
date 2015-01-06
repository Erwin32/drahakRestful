<?php
namespace Drahak\Restful;

use Drahak\Restful\Converters\ResourceConverter;

/**
 * ConvertedResource
 * @package Drahak\Restful
 */
class ConvertedResource extends Resource
{

	/** @var ResourceConverter */
	private $resourceConverter;

	/**
	 * @param ResourceConverter $resourceConverter
	 * @param array $data
	 */
	public function __construct(ResourceConverter $resourceConverter, array $data = array())
	{
		parent::__construct($data);
		$this->resourceConverter = $resourceConverter;
	}

	/**
	 * Get parsed resource
	 * @return array
	 */
	public function getData()
	{
		$data = parent::getData();
		return $this->resourceConverter->convert($data);
	}

}
