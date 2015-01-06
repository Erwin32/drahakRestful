<?php
namespace Tests\Drahak\Restful\Http;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Http\ResponseFactory;
use Drahak\Restful\Http\ResponseProxy;
use Drahak\Restful\InvalidStateException;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Http\ResponseFactory.
 *
 * @testCase Tests\Drahak\Restful\Http\ResponseFactoryTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Http
 */
class ResponseFactoryTest extends TestCase
{

	/** @var ResponseFactory */
	private $factory;

	/** @var MockInterface */
	private $request;

	/** @var MockInterface */
	private $response;

	/** @var MockInterface */
	private $filter;

	/** @var MockInterface */
	private $url;

	public function setUp()
	{
		parent::setUp();
		$this->filter = $this->mockista->create('Drahak\Restful\Utils\RequestFilter');
		$this->request = $this->mockista->create('Nette\Http\IRequest');
		$this->response = $this->mockista->create('Nette\Http\IResponse');
		$this->url = new Nette\Http\UrlScript('http://resource/');
		$this->factory = new ResponseFactory($this->request, $this->filter);
		$this->factory->setResponse($this->response);
	}
	
	public function testCreateHttpResponseWithGivenStatusCode()
	{
		$exception = new InvalidStateException;
		$this->filter->expects('getPaginator')
			->once()
			->andThrow($exception);

		$this->response->expects('setCode')
			->once()
			->with(422);

		$this->request->expects('getUrl')->once()->andReturn($this->url);
		$this->response->expects('setHeader')->once()->with('Allow', '');

		$response = $this->factory->createHttpResponse(422);
		Assert::true($response instanceof \Nette\Http\IResponse);
		Assert::same($response, $this->response);
	}

	public function testCreateHttpResponseWithPaginator()
	{
		$paginator = $this->createPaginatorMock();
		$this->filter->expects('getPaginator')
			->once()
			->andReturn($paginator);

		$this->response->expects('setCode')
			->once()
			->with(200);

		$this->request->expects('getUrl')->twice()->andReturn($this->url);

		$this->response->expects('setHeader')
			->once()
			->with('Link', '<http://resource/?offset=10&limit=10>;rel="next", <http://resource/?offset=90&limit=10>;rel="last"');
		$this->response->expects('setHeader')
			->once()
			->with('X-Total-Count', 100);

		$this->factory->createHttpResponse(200);
	}

	public function testCreateHttpResponseWithAllowedMethods()
	{
		$exception = new InvalidStateException;
		$this->filter->expects('getPaginator')
			->once()
			->andThrow($exception);

		$this->response->expects('setCode')
			->once()
			->with(200);

		$this->request->expects('getUrl')->once()->andReturn($this->url);

		$response = $this->factory->createHttpResponse(200);
		Assert::true($response instanceof \Nette\Http\IResponse);
		Assert::same($response, $this->response);
	}

	public function testCreateHttpResponseWithDefaultStatusCodeDeterminedFromRequestMethod()
	{
		$exception = new InvalidStateException;
		$this->filter->expects('getPaginator')
			->once()
			->andThrow($exception);

		$this->response->expects('setCode')
			->once()
			->with(201);

		$this->request->expects('getMethod')->once()->andReturn('POST');
		$this->request->expects('getUrl')->once()->andReturn($this->url);

		$response = $this->factory->createHttpResponse();
		Assert::true($response instanceof \Nette\Http\IResponse);
		Assert::same($response, $this->response);
	}

	/**
	 * Create paginator mock
	 * @return MockInterface
	 */
	private function createPaginatorMock()
	{
		$paginator = $this->mockista->create('Nette\Utils\Paginator');
		$paginator->expects('getPage')->atLeastOnce()->andReturn(1);
		$paginator->expects('getLastPage')->atLeastOnce()->andReturn(10);
		$paginator->expects('getItemsPerPage')->atLeastOnce()->andReturn(10);
		$paginator->expects('getOffset')->atLeastOnce()->andReturn(10);
		$paginator->expects('getItemCount')->atLeastOnce()->andReturn(100);
		$paginator->expects('setPage')->atLeastOnce();
		return $paginator;
	}
	
}
\run(new ResponseFactoryTest());
