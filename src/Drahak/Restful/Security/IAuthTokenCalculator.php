<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\Http\IInput;

/**
 * Input fingerprint hash Calculator interface
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IAuthTokenCalculator
{

	/**
	 * Set hash private key
	 * @param string $key
	 * @return IAuthTokenCalculator
	 */
	public function setPrivateKey($key);

	/**
	 * Calculate fingerprint hash
	 * @param IInput $input
	 * @return string
	 */
	public function calculate(IInput $input);

}