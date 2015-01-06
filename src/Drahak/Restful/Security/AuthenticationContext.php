<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\Http\IInput;
use Drahak\Restful\Security\Process\AuthenticationProcess;
use Nette\Object;

/**
 * AuthenticationContext determines which authentication process should use
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class AuthenticationContext extends Object
{

	/** @var AuthenticationProcess */
	private $process;

	/**
	 * Set authentication process to use
	 * @param AuthenticationProcess $process
	 * @return AuthenticationContext
	 */
	public function setAuthProcess(AuthenticationProcess $process)
	{
		$this->process = $process;
		return $this;
	}

	/**
	 * Authenticate request with authentication process strategy
	 * @param IInput $input
	 * @return bool
	 *
	 * @throws AuthenticationException
	 * @throws RequestTimeoutException
	 */
	public function authenticate(IInput $input)
	{
		return $this->process->authenticate($input);
	}

}