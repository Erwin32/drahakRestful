<?php
namespace Tests\Drahak\Restful\Application;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Application\ResponseFactory;
use Drahak\Restful\Application\Responses\JsonpResponse;
use Drahak\Restful\Application\Responses\TextResponse;
use Drahak\Restful\IResource;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\ResponseFactory.
 *
 * @testCase Tests\Drahak\Restful\Application\ResponseFactoryTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application
 */
class ResponseFactoryTest extends TestCase
{
	/** @var ResponseFactory */
	private $factory;

	/** @var MockInterface */
	private $resource;

	/** @var MockInterface */
	private $mapper;

	/** @var MockInterface */
	private $request;

	/** @var MockInterface */
	private $response;

	/** @var MockInterface */
	private $mapperContext;

	/** @var MockInterface */
	private $cacheValidator;

    protected function setUp()
    {
		parent::setUp();
		$this->response = $this->mockista->create('Nette\Http\IResponse');
		$this->request = $this->mockista->create('Nette\Http\IRequest');
		$this->mapperContext = $this->mockista->create('Drahak\Restful\Mapping\MapperContext');
		$this->factory = new ResponseFactory($this->response, $this->request, $this->mapperContext);
		$this->factory->setJsonp('jsonp');
		$this->resource = $this->mockista->create('Drahak\Restful\Resource');
		$this->mapper = $this->mockista->create('Drahak\Restful\Mapping\IMapper');
	}

	public function testCreateResponse()
	{
		$this->response->expects('setCode')
			->once()
			->with(204);

		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn(IResource::JSON);

		$this->resource->expects('getData')
			->once()
			->andReturn(array());

		$this->mapperContext->expects('getMapper')
			->once()
			->with(IResource::JSON)
			->andReturn($this->mapper);

		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(FALSE);

		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);

		$response = $this->factory->create($this->resource);
		Assert::true($response instanceof TextResponse);
	}

	public function testCreateCustomResponse()
	{
		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn('text');

		$this->resource->expects('getData')
			->once()
			->andReturn('test');

		$this->mapperContext->expects('getMapper')
			->once()
			->with('text')
			->andReturn($this->mapper);

		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(FALSE);

		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);

		$this->factory->registerResponse('text', 'Nette\Application\Responses\TextResponse');
		$response = $this->factory->create($this->resource);

		Assert::true($response instanceof Nette\Application\Responses\TextResponse);
	}

	public function testCreateJsonpResponseWhenJsonpIsActive()
	{
		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn('text');
		$this->resource->expects('getData')
			->once()
			->andReturn('test');
		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn('callback');
		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);
		$this->mapperContext->expects('getMapper')
			->once()
			->with(IResource::JSONP)
			->andReturn($this->mapper);

		$this->factory->registerResponse('text', 'Nette\Application\Responses\TextResponse');
		$response = $this->factory->create($this->resource);

		Assert::true($response instanceof JsonpResponse);
	}

	public function testThrowsExceptionWhenResponseTypeIsNotFound()
	{
		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn('drahak/test');
		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(FALSE);
		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);

		Assert::throws(function() {
			$this->factory->create($this->resource);
		}, 'Drahak\Restful\InvalidStateException');
	}

    public function testThrowsExceptionWhenResponseClassNotExists()
    {
		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(FALSE);
		$factory = $this->factory;
		Assert::throws(function() use($factory) {
			$factory->registerResponse('test/plain', 'Drahak\TestResponse');
		}, 'Drahak\Restful\InvalidArgumentException');
    }

    public function testCreateResponseBasedOnRequestedContentTypeIfJsonpIsDisabled()
    {
    	$this->factory->setJsonp(FALSE);

		$this->response->expects('setCode')
			->once()
			->with(204);

		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn(IResource::JSON);

		$this->resource->expects('getData')
			->once()
			->andReturn(array());

		$this->mapperContext->expects('getMapper')
			->once()
			->with(IResource::JSON)
			->andReturn($this->mapper);

		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn('callback');

		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);

		$response = $this->factory->create($this->resource);
		Assert::true($response instanceof TextResponse);
    }

    public function testSelectsFirstContentTypeIfAcceptHeaderAcceptsAll()
    {	
		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn('*/*');
		$this->resource->expects('getData')
			->once()
			->andReturn('test');
		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(NULL);
		$this->request->expects('getQuery')
			->once()
			->with('prettyPrint')
			->andReturn(FALSE);
		$this->mapperContext->expects('getMapper')
			->once()
			->with(IResource::JSON)
			->andReturn($this->mapper);

		$response = $this->factory->create($this->resource);

		Assert::true($response instanceof TextResponse);
    }

    public function testUseCustomPrettyPrintKeyName()
    {

		$this->response->expects('setCode')
			->once()
			->with(204);

		$this->request->expects('getHeader')
			->once()
			->with('Accept')
			->andReturn(IResource::JSON);

		$this->resource->expects('getData')
			->once()
			->andReturn(array());

		$this->mapperContext->expects('getMapper')
			->once()
			->with(IResource::JSON)
			->andReturn($this->mapper);

		$this->request->expects('getQuery')
			->once()
			->with('jsonp')
			->andReturn(FALSE);

		$this->request->expects('getQuery')
			->once()
			->with('pretty')
			->andReturn(FALSE);

		$this->factory->prettyPrint = 'pretty';
		$this->factory->create($this->resource);
    }

}
\run(new ResponseFactoryTest());
