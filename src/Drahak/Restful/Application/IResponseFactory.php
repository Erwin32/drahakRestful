<?php
namespace Drahak\Restful\Application;

use Drahak\Restful\IResource;
use Nette\Application\IResponse;

/**
 * IResponseFactory
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IResponseFactory
{

	/**
	 * Create new API response
	 * @param IResource $resource
	 * @return IResponse
	 */
	public function create(IResource $resource);

}
