<?php
namespace Drahak\Restful\Security\Process;

use Drahak\Restful\Http\IInput;
use Drahak\Restful\Security\Authentication\HashAuthenticator;
use Drahak\Restful\Security\Authentication\TimeoutAuthenticator;

/**
 * SecuredAuthentication process
 * @package Drahak\Restful\Security\Process
 * @author Drahomír Hanák
 */
class SecuredAuthentication extends AuthenticationProcess
{

	/** @var HashAuthenticator */
	private $hashAuth;

	/** @var TimeoutAuthenticator */
	private $timeAuth;

	public function __construct(HashAuthenticator $hashAuth, TimeoutAuthenticator $timeAuth)
	{
		$this->hashAuth = $hashAuth;
		$this->timeAuth = $timeAuth;
	}

	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestData(IInput $input)
	{
		return $this->hashAuth->authenticate($input);
	}

	/**
	 * Authenticate request time
	 * @param IInput $input
	 * @return bool
	 */
	protected function authRequestTimeout(IInput $input)
	{
		return $this->timeAuth->authenticate($input);
	}


}