<?php
namespace Drahak\Restful\Security\Process;

use Drahak\Restful\Http\IInput;
use Drahak\Restful\Security\AuthenticationException;
use Drahak\Restful\Security\RequestTimeoutException;
use Nette\Security\IUserStorage;
use Nette\Security\User;

/**
 * BasicAuthentication
 * @package Drahak\Restful\Security\Process
 * @author Drahomír Hanák
 */
class BasicAuthentication extends AuthenticationProcess
{

	/** @var User */
	private $user;

	/**
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool
	 *
	 * @throws AuthenticationException
	 */
	protected function authRequestData(IInput $input)
	{
		if (!$this->user->isLoggedIn()) {
			throw new AuthenticationException('User not logged in');
		}
	}

	/**
	 * Authenticate request timeout
	 * @param IInput $input
	 * @return bool
	 *
	 * @throws RequestTimeoutException
	 */
	protected function authRequestTimeout(IInput $input)
	{
		if ($this->user->getLogoutReason() === IUserStorage::INACTIVITY) {
			throw new RequestTimeoutException('User session expired');
		}
	}

}