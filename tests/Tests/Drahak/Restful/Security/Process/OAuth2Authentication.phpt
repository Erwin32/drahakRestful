<?php
namespace Tests\Drahak\Restful\Security\Process;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\OAuth2\Storage\InvalidAccessTokenException;
use Drahak\Restful\Security\Process\OAuth2Authentication;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Security\Process\OAuth2Authentication.
 *
 * @testCase Tests\Drahak\Restful\Security\Process\OAuth2AuthenticationTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Security\Process
 */
class OAuth2AuthenticationTest extends TestCase
{

	/** @var MockInterface */
	private $token;

	/** @var MockInterface */
	private $input;

	/** @var MockInterface */
	private $inputFake;

	/** @var OAuth2Authentication */
	private $process;

    protected function setUp()
    {
		parent::setUp();
		$this->token = $this->mockista->create('Drahak\OAuth2\Storage\AccessTokens\AccessTokenFacade');
		$this->input = $this->mockista->create('Drahak\OAuth2\Http\IInput');
		$this->inputFake = $this->mockista->create('Drahak\Restful\Http\IInput');
		$this->process = new OAuth2Authentication($this->token, $this->input);
    }
    
    public function testSuccessfullyAuthenticateAccessToken()
    {
		$token = '54a6f2ewq86f25rgr6n8r58hr28tj6vd';

		$this->input->expects('getAuthorization')
			->once()
			->andReturn($token);

		$this->token->expects('getEntity')
			->once()
			->with($token)
			->andReturn(array('access_token' => $token));

		$this->process->authenticate($this->inputFake);
    }

	public function testThrowsExceptionWhenTokenIsNotFoundOnInput()
	{
		$this->input->expects('getAuthorization')
			->once()
			->andReturn(NULL);

		Assert::throws(function() {
			$this->process->authenticate($this->inputFake);
		}, 'Drahak\Restful\Security\AuthenticationException');
	}

	public function testThrowsExceptionWhenTokenIsExpiredOrInvalid()
	{
		$token = '54a6f2ewq86f25rgr6n8r58hr28tj6vd';
		$invalidTokenException = new InvalidAccessTokenException;

		$this->input->expects('getAuthorization')
			->once()
			->andReturn($token);

		$this->token->expects('getEntity')
			->once()
			->with($token)
			->andReturn(NULL)
			->andThrow($invalidTokenException);

		Assert::throws(function() {
			$this->process->authenticate($this->inputFake);
		}, 'Drahak\Restful\Security\AuthenticationException');
	}

}
\run(new OAuth2AuthenticationTest());