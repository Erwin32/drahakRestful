<?php
namespace Drahak\Restful\Security\Process;

use Drahak\Restful\Http\IInput;

/**
 * NullAuthentication for non-secured API requests
 * @package Drahak\Restful\Security\Process
 * @author Drahomír Hanák
 */
class NullAuthentication extends AuthenticationProcess
{
	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestData(IInput $input)
	{
		return TRUE;
	}

	/**
	 * Authenticate request time
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestTimeout(IInput $input)
	{
		return TRUE;
	}


}