<?php
namespace Drahak\Restful\Validation;

/**
 * IValidationScopeFactory
 * @package Drahak\Restful\Validation
 * @author Drahomír Hanák
 */
interface IValidationScopeFactory
{

	/**
	 * Validation schema factory
	 * @return \Drahak\Restful\Validation\IValidationScope
	 */
	public function create();

}
