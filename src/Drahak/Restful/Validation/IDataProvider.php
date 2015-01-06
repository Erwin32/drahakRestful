<?php
namespace Drahak\Restful\Validation;

/**
 * Validation data provider
 * @package Drahak\Restful\Validation
 * @author Drahomír Hanák
 */
interface IDataProvider
{

	/**
	 * Get validation field
	 * @param string $name
	 * @return IField
	 */
	public function field($name);

	/**
	 * Validate data
	 * @return array
	 */
	public function validate();

	/**
	 * Is input valid
	 * @return bool
	 */
	public function isValid();

	/**
	 * Get validation schema
	 * @return IValidationScope
	 */
	public function getValidationScope();

}