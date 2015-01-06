<?php
namespace Drahak\Restful\Security\Authentication;

use Drahak\Restful\Http\IInput;
use Drahak\Restful\Security\IAuthTokenCalculator;
use Drahak\Restful\Security\AuthenticationException;
use Nette\Http\IRequest;
use Nette\Object;

/**
 * Verify request hashing data and comparing the results
 * @package Drahak\Restful\Security\Authentication
 * @author Drahomír Hanák
 */
class HashAuthenticator extends Object implements IRequestAuthenticator
{

	/** Auth token request header name */
	const AUTH_HEADER = 'X-HTTP-AUTH-TOKEN';

	/** @var array */
	private $privateKey;

	/** @var IRequest */
	protected $request;

	/** @var IAuthTokenCalculator */
	protected $calculator;

	/**
	 * @param string $privateKey
	 * @param IRequest $request
	 * @param IAuthTokenCalculator $calculator
	 */
	public function __construct($privateKey, IRequest $request, IAuthTokenCalculator $calculator)
	{
		$this->privateKey = $privateKey;
		$this->request = $request;
		$this->calculator = $calculator;
	}

	/**
	 * @param IInput $input
	 * @return bool
	 * @throws AuthenticationException
	 */
	public function authenticate(IInput $input)
	{
		$requested = $this->getRequestedHash();
		if (!$requested) {
			throw new AuthenticationException('Authentication header not found.');
		}

		$expected = $this->getExpectedHash($input);
		if ($requested !== $expected) {
			throw new AuthenticationException('Authentication tokens do not match.');
		}
		return TRUE;
	}


	/**
	 * Get request hash
	 * @return string
	 */
	protected function getRequestedHash()
	{
		return $this->request->getHeader(self::AUTH_HEADER);
	}

	/**
	 * Get expected hash
	 * @param IInput $input
	 * @return string
	 */
	protected function getExpectedHash(IInput $input)
	{
		return $this->calculator->calculate($input);
	}

}
