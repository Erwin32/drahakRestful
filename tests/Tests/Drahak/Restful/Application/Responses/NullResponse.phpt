<?php
namespace Tests\Drahak\Restful\Application\Responses;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Responses\NullResponse;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Responses\NullResponse.
 *
 * @testCase Tests\Drahak\Restful\Application\Responses\NullResponseTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Responses
 */
class NullResponseTest extends TestCase
{
	/** @var NullResponse */
	private $response;
    
    protected function setUp()
    {
		parent::setUp();
		$this->response = new NullResponse;
    }
    
    public function testDoNotSendResponse()
    {
		$httpRequest = $this->mockista->create('Nette\Http\IRequest');
		$httpResponse = $this->mockista->create('Nette\Http\IResponse');

		ob_start();
		$result = $this->response->send($httpRequest, $httpResponse);
		$content = ob_get_contents();
		ob_end_clean();

		Assert::equal($content, '');
		Assert::null($result);
    }
}
\run(new NullResponseTest());