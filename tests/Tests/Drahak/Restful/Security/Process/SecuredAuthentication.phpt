<?php
namespace Tests\Drahak\Restful\Security\Process;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Security\Process\SecuredAuthentication;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Security\Process\Process\SecuredAuthentication.
 *
 * @testCase Tests\Drahak\Restful\Security\Process\Process\SecuredAuthenticationTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Security\Process
 */
class SecuredAuthenticationTest extends TestCase
{

	/** @var MockInterface */
	private $input;

	/** @var MockInterface */
	private $hashAuth;

	/** @var MockInterface */
	private $timeAuth;

	/** @var SecuredAuthentication */
	private $process;
    
    protected function setUp()
    {
		parent::setUp();
		$this->input = $this->mockista->create('Drahak\Restful\Http\IInput');
		$this->hashAuth = $this->mockista->create('Drahak\Restful\Security\Authentication\HashAuthenticator');
		$this->timeAuth = $this->mockista->create('Drahak\Restful\Security\Authentication\TimeoutAuthenticator');
		$this->process = new SecuredAuthentication($this->hashAuth, $this->timeAuth);
    }
    
    public function testAuthenticateRequest()
    {
		$this->hashAuth->expects('authenticate')
			->once()
			->with($this->input)
			->andReturn(TRUE);

		$this->timeAuth->expects('authenticate')
			->once()
			->with($this->input)
			->andReturn(TRUE);

		$this->process->authenticate($this->input);
	}

}
\run(new SecuredAuthenticationTest());