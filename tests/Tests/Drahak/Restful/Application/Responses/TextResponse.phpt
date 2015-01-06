<?php
namespace Tests\Drahak\Restful\Application\Responses;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Responses\TextResponse;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Responses\TextResponse.
 *
 * @testCase Tests\Drahak\Restful\Application\Responses\TextResponseTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Responses
 */
class TextResponseTest extends TestCase
{

	/** @var MockInterface */
	private $mapper;

	/** @var TextResponse */
	private $response;

    protected function setUp()
    {
		parent::setUp();
		$this->mapper = $this->mockista->create('Drahak\Restful\Mapping\IMapper');
		$this->response = new TextResponse(array('hello' => 'world'), $this->mapper, 'application/json');
    }
    
    public function testResponseWithJson()
    {
		$output = '{"hello":"world"}';

		$this->mapper->expects('stringify')
			->once()
			->with(array('hello' => 'world'), TRUE)
			->andReturn($output);

		$httpRequest = $this->mockista->create('Nette\Http\IRequest');
		$httpResponse = $this->mockista->create('Nette\Http\IResponse');

		$httpResponse->expects('setContentType')
			->once()
			->with('application/json', 'UTF-8');

		ob_start();
		$this->response->send($httpRequest, $httpResponse);
		$content = ob_get_clean();

		Assert::same($content, $output);
    }

}
\run(new TextResponseTest());
