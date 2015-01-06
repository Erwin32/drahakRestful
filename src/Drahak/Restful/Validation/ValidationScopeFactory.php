<?php
namespace Drahak\Restful\Validation;

use Nette\Object;

/**
 * ValidationScopeFactory
 * @package Drahak\Restful\Validation
 * @author Drahomír Hanák
 */
class ValidationScopeFactory extends Object implements IValidationScopeFactory
{

	/** @var IValidator */
	private $validator;

	/**
	 * @param IValidator $validator
	 */
	public function __construct(IValidator $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * Validation schema factory
	 * @return \Drahak\Restful\Validation\IValidationScope
	 */
	public function create()
	{
		return new ValidationScope($this->validator);
	}


}
