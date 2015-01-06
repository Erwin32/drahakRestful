<?php
namespace Tests\Drahak\Restful\Application\Responses;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Responses\JsonpResponse;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Responses\JsonpResponse.
 *
 * @testCase Tests\Drahak\Restful\Application\Responses\JsonpResponseTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Responses
 */
class JsonpResponseTest extends TestCase
{

	/** @var MockInterface */
	private $httpRequest;

	/** @var MockInterface */
	private $httpResponse;

	/** @var JsonpResponse */
	private $response;

	/** @var MockInterface */
	private $mapper;

    protected function setUp()
    {
		parent::setUp();
		$this->httpRequest = $this->mockista->create('Nette\Http\IRequest');
		$this->httpResponse = $this->mockista->create('Nette\Http\IResponse');
		$this->mapper = $this->mockista->create('Drahak\Restful\Mapping\IMapper');
		$this->response = new JsonpResponse(array('test' => 'JSONP'), $this->mapper);
    }
    
    public function testResponseWithJSONP()
    {
		$output = '{"response":{"test":"JSONP"},"status":200,"headers":{"X-Testing":true}}';
		$headers = array('X-Testing' => true);

		$data = array();
		$data['response'] = array('test' => 'JSONP');
		$data['status'] = 200;
		$data['headers'] = $headers;

		$this->httpResponse->expects('setContentType')
			->once()
			->with('application/javascript', 'UTF-8');
		$this->httpResponse->expects('getCode')
			->once()
			->andReturn(200);
		$this->httpResponse->expects('getHeaders')
			->once()
			->andReturn($headers);
		$this->httpRequest->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn('callbackFn');

		$this->mapper->expects('stringify')
			->once()
			->with($data, TRUE)
			->andReturn($output);

		ob_start();
		$this->response->send($this->httpRequest, $this->httpResponse);
		$content = ob_get_clean();

		Assert::same($content, 'callbackFn(' . $output . ');');
    }

	public function testWebalizeCallbackFunctionNameAndKeepUpperCase()
	{
		$output = '{"response":{"test":"JSONP"},"status":200,"headers":{"X-Testing":true}}';
		$headers = array('X-Testing' => true);

		$data = array();
		$data['response'] = array('test' => 'JSONP');
		$data['status'] = 200;
		$data['headers'] = $headers;

		$this->mapper->expects('stringify')
			->once()
			->with($data, TRUE)
			->andReturn($output);

		$this->httpResponse->expects('setContentType')
			->once()
			->with('application/javascript', 'UTF-8');
		$this->httpResponse->expects('getCode')
			->once()
			->andReturn(200);
		$this->httpResponse->expects('getHeaders')
			->once()
			->andReturn($headers);
		$this->httpRequest->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn('ěščřžýáíéAnd+_-! ?');

		ob_start();
		$this->response->send($this->httpRequest, $this->httpResponse);
		$content = ob_get_contents();
		ob_end_flush();

		Assert::same($content, 'escrzyaieAnd(' . $output . ');');
	}

}
\run(new JsonpResponseTest());
