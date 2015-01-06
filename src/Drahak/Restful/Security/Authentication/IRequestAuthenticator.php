<?php
namespace Drahak\Restful\Security\Authentication;

use Drahak\Restful\Http\IInput;

/**
 * IRequestAuthenticator
 * @package Drahak\Restful\Security\Authentication
 * @author Drahomír Hanák
 */
interface IRequestAuthenticator
{

	/**
	 * Authenticate request
	 * @param IInput $input
	 * @return bool
	 */
	public function authenticate(IInput $input);

}