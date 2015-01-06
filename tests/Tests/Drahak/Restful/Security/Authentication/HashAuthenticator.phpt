<?php
namespace Tests\Drahak\Restful\Security\Authentication;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Security\Authentication\HashAuthenticator;
use Drahak\Restful\Security\HashCalculator;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Security\Authentication\HashAuthenticator.
 *
 * @testCase Tests\Drahak\Restful\Security\Authentication\HashAuthenticatorTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Security\Authentication
 */
class HashAuthenticatorTest extends TestCase
{

	/** @var MockInterface */
	private $input;

	/** @var MockInterface */
	private $request;

	/** @var MockInterface */
	private $calculator;

	/** @var HashAuthenticator */
	private $authenticator;
    
    protected function setUp()
    {
		parent::setUp();
		$this->input = $this->mockista->create('Drahak\Restful\Http\IInput');
		$this->calculator = $this->mockista->create('Drahak\Restful\Security\IAuthTokenCalculator');
		$this->request = $this->mockista->create('Nette\Http\IRequest');
		$this->authenticator = new HashAuthenticator('topSecretKey', $this->request, $this->calculator);
    }
    
    public function testSuccessfulAuthentication()
    {
		$dataString = 'message=Testing+hash&sender=%40drahomir_hanak';

		$this->request->expects('getHeader')
			->once()
			->with(HashAuthenticator::AUTH_HEADER)
			->andReturn(hash_hmac(HashCalculator::HASH, $dataString, 'topSecretKey'));

		$this->calculator->expects('calculate')
			->once()
			->with($this->input)
			->andReturn(hash_hmac(HashCalculator::HASH, $dataString, 'topSecretKey'));

		$result = $this->authenticator->authenticate($this->input);
		Assert::true($result);
    }

	public function testWrongAuthenticationHash()
	{
		$dataString = 'message=Testing+hash&sender=%40drahomir_hanak';

		$this->request->expects('getHeader')
			->once()
			->with(HashAuthenticator::AUTH_HEADER)
			->andReturn('totaly wrong hash');

		$this->calculator->expects('calculate')
			->once()
			->with($this->input)
			->andReturn(hash_hmac(HashCalculator::HASH, $dataString, 'topSecretKey'));

		Assert::throws(function() {
			$this->authenticator->authenticate($this->input);
		}, 'Drahak\Restful\Security\AuthenticationException');
	}

	public function testMissingAuthenticationHeader()
	{
		$this->request->expects('getHeader')
			->once()
			->with(HashAuthenticator::AUTH_HEADER)
			->andReturn(NULL);

		Assert::throws(function() {
			$this->authenticator->authenticate($this->input);
		}, 'Drahak\Restful\Security\AuthenticationException');
	}

}
\run(new HashAuthenticatorTest());