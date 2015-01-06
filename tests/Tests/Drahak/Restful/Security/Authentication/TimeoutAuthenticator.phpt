<?php
namespace Tests\Drahak\Restful\Security\Authentication;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Security\Authentication\TimeoutAuthenticator;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Security\Authentication\TimeoutAuthenticator.
 *
 * @testCase Tests\Drahak\Restful\Security\Authentication\TimeoutAuthenticatorTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Security\Authentication
 */
class TimeoutAuthenticatorTest extends TestCase
{

	/** @var MockInterface */
	private $input;

	/** @var TimeoutAuthenticator */
	private $authenticator;

    protected function setUp()
    {
		parent::setUp();
		$this->input = $this->mockista->create('Drahak\Restful\Http\IInput');
		$this->authenticator = new TimeoutAuthenticator('timestamp', 600);
    }
    
    public function testSuccessfulAuthenticaton()
    {
		$data = array('timestamp' => time());
		$this->input->expects('getData')
			->once()
			->andReturn($data);

		$result = $this->authenticator->authenticate($this->input);
		Assert::true($result);
    }

	public function testRequestTimeoutException()
	{
		$data = array('timestamp' => time()-601);
		$this->input->expects('getData')
			->once()
			->andReturn($data);

		Assert::throws(function() {
			$this->authenticator->authenticate($this->input);
		}, 'Drahak\Restful\Security\RequestTimeoutException');
	}

}
\run(new TimeoutAuthenticatorTest());