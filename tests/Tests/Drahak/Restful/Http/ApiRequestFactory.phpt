<?php
namespace Tests\Drahak\Restful\Http;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Http\ApiRequestFactory;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Http\ApiRequestFactory.
 *
 * @testCase Tests\Drahak\Restful\Http\ApiRequestFactoryTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Http
 */
class ApiRequestFactoryTest extends TestCase
{

	/** @var ApiRequestFactory */
	private $apiRequestFactory;

	/** @var RequestFactory */
	private $requestFactory;

	/** @var MockInterface */
	private $request;

	public function setUp()
	{
		parent::setUp();
		$this->request = $this->createRequestMock();
		$this->requestFactory = $this->mockista->create('Nette\Http\RequestFactory');  
		$this->requestFactory->expects('createHttpRequest')->andReturn($this->request);
		$this->apiRequestFactory = new ApiRequestFactory($this->requestFactory);
	}

	public function testCreatesRequestWithMethodServerWasRequested()
	{
		$this->request->expects('getHeader')
			->with(ApiRequestFactory::OVERRIDE_HEADER)
			->andReturn(NULL);

		$this->request->expects('getQuery')
			->with(ApiRequestFactory::OVERRIDE_PARAM)
			->andReturn(NULL);

		$this->request->expects('getMethod')
			->once()
			->andReturn('GET');

		$request = $this->apiRequestFactory->createHttpRequest();
		Assert::equal($request->getMethod(), 'GET');
	}

	public function testCreatesRequestWithMethodThatIsInOverrideHeader() 
	{
		$this->request->expects('getMethod')
			->once()
			->andReturn('POST');
	
		$this->request->expects('getHeader')
			->with(ApiRequestFactory::OVERRIDE_HEADER)
			->andReturn('DELETE');

		$request = $this->apiRequestFactory->createHttpRequest();
		Assert::equal($request->getMethod(), 'DELETE');		
	}

	public function testCreateRequestWithMethodThatIsInQueryParameter()
	{
		$this->request->expects('getMethod')
			->once()
			->andReturn('POST');

		$this->request->expects('getHeader')
			->with(ApiRequestFactory::OVERRIDE_HEADER)
			->andReturn(NULL);

		$this->request->expects('getQuery')
			->with(ApiRequestFactory::OVERRIDE_PARAM)
			->andReturn('DELETE');

		$request = $this->apiRequestFactory->createHttpRequest();
		Assert::equal($request->getMethod(), 'DELETE');
	}

	public function testDoesNotOverrideMethodWithHeaderIfRequestedWithGetMethod()
	{
		$this->request->expects('getMethod')
			->once()
			->andReturn('GET');

		$this->request->expects('getHeader')
			->with(ApiRequestFactory::OVERRIDE_HEADER)
			->andReturn('DELETE');

		$this->request->expects('getQuery')
			->with(ApiRequestFactory::OVERRIDE_PARAM)
			->andReturn(NULL);

		$request = $this->apiRequestFactory->createHttpRequest();
		Assert::equal($request->getMethod(), 'GET');	
	}

	public function testDoesNotOverrideMethodWithQueryParameterIfRequestedWithGetMethod()
	{
		$this->request->expects('getMethod')
			->once()
			->andReturn('GET');

		$this->request->expects('getHeader')
			->with(ApiRequestFactory::OVERRIDE_HEADER)
			->andReturn(NULL);

		$this->request->expects('getQuery')
			->with(ApiRequestFactory::OVERRIDE_PARAM)
			->andReturn('DELETE');

		$request = $this->apiRequestFactory->createHttpRequest();
		Assert::equal($request->getMethod(), 'GET');	
	}

	private function createRequestMock()
	{
		$url = $this->mockista->create('Nette\Http\UrlScript');
		$url->expects('__get')->once()->with('query')->andReturn('');

		$request = $this->mockista->create('Nette\Http\IRequest');
		$request->expects('getUrl')->once()->andReturn($url);
		$request->expects('getQuery')->once()->andReturn(NULL);
		$request->expects('getPost')->once()->andReturn(NULL);
		$request->expects('getFiles')->once()->andReturn(NULL);
		$request->expects('getCookies')->once()->andReturn(NULL);
		$request->expects('getHeaders')->once()->andReturn(NULL);
		$request->expects('getRemoteAddress')->once()->andReturn(NULL);
		$request->expects('getRemoteHost')->once()->andReturn(NULL);
		return $request;
	}

}
\run(new ApiRequestFactoryTest());
