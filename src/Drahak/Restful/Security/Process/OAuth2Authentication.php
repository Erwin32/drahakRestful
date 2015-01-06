<?php
namespace Drahak\Restful\Security\Process;

use Drahak\OAuth2;
use Drahak\OAuth2\Storage\AccessTokens\AccessTokenFacade;
use Drahak\OAuth2\Storage\InvalidAccessTokenException;
use Drahak\Restful\Http\IInput;
use Drahak\Restful\Security\AuthenticationException;

/**
 * OAuth2Authentication
 * @package Drahak\Restful\Security\Process
 * @author Drahomír Hanák
 */
class OAuth2Authentication extends AuthenticationProcess
{

	/** @var AccessTokenFacade */
	private $storage;

	/** @var OAuth2\Http\IInput */
	private $oauthInput;

	public function __construct(AccessTokenFacade $storage, OAuth2\Http\IInput $oauthInput)
	{
		$this->storage = $storage;
		$this->oauthInput = $oauthInput;
	}

	/**
	 * Get access token
	 * @return OAuth2\Storage\AccessTokens\IAccessToken|NULL
	 *
	 * @throws InvalidAccessTokenException
	 */
	public function getAccessToken()
	{
		$token = $this->oauthInput->getAuthorization();
		return $this->storage->getEntity($token);
	}

	/**
	 * Authenticate request data
	 * @param IInput $input
	 * @return bool|void
	 *
	 * @throws AuthenticationException
	 */
	protected function authRequestData(IInput $input)
	{
		$token = $this->oauthInput->getAuthorization();
		if (!$token) {
			throw new AuthenticationException('Token was not found.');
		}
	}

	/**
	 * Authenticate request timeout
	 * @param IInput $input
	 * @return bool|void
	 *
	 * @throws AuthenticationException
	 */
	protected function authRequestTimeout(IInput $input)
	{
		try {
			$this->getAccessToken();
		} catch (InvalidAccessTokenException $e) {
			throw new AuthenticationException('Invalid or expired access token.', 0, $e);
		}
	}


}