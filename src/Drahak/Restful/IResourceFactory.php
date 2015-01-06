<?php
namespace Drahak\Restful;

/**
 * IResourceFactory
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IResourceFactory
{

	/**
	 * Create new API resource
	 * @param array $data
	 * @return IResource
	 */
	public function create(array $data = array());

}
