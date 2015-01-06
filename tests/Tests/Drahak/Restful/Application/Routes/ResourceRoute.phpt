<?php
namespace Tests\Drahak\Restful\Application\Routes;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Routes\ResourceRoute;
use Drahak\Restful\Application\IResourceRouter;
use Drahak\Restful\Http\Request;
use Mockista\MockInterface;
use Nette;
use Nette\Http\IRequest;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Routes\ResourceRoute.
 *
 * @testCase Tests\Drahak\Restful\Application\Routes\ResourceRouteTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Routes
 */
class ResourceRouteTest extends TestCase
{

	/** @var ResourceRoute */
	private $route;

	/** @var MockInterface */
	private $httpRequest;

    protected function setUp()
    {
		parent::setUp();
		$this->route = new ResourceRoute('resources/test', array(
			'module' => 'Resources',
			'presenter' => 'Test',
			'action' => array(
				IResourceRouter::GET => 'read',
				IResourceRouter::POST => 'create',
				IResourceRouter::PUT => 'update',
				IResourceRouter::DELETE => 'delete',
			)
		), IResourceRouter::CRUD);
		$this->httpRequest = $this->mockista->create('Nette\Http\IRequest');
	}
    
    public function testRouteListeningOnCrudRequestMethods()
    {
		Assert::true($this->route->isMethod(IResourceRouter::GET));
		Assert::true($this->route->isMethod(IResourceRouter::PUT));
		Assert::true($this->route->isMethod(IResourceRouter::POST));
		Assert::true($this->route->isMethod(IResourceRouter::DELETE));
		Assert::true($this->route->isMethod(IResourceRouter::CRUD));
    }

	public function testOtherRequestMethods()
	{
		Assert::false($this->route->isMethod(IResourceRouter::HEAD));
		Assert::false($this->route->isMethod(IResourceRouter::PATCH));
	}

	public function testActionDictionary()
	{
		$array = $this->route->actionDictionary;
		Assert::equal(count($array), 4);
		Assert::equal($array[IResourceRouter::GET], 'read');
	}

	public function testDoesNotMatchMask()
	{
		$this->httpRequest->expects('getUrl')
			->once()
			->andReturn($this->createRequestUrlMock('resources/path'));

		$appRequest = $this->route->match($this->httpRequest);
		Assert::null($appRequest);
	}

	public function testDoesNotMatchRequestMethod()
	{
		$this->setupRequestMock();
		$this->httpRequest->expects('getMethod')
			->atLeastOnce()
			->andReturn(IRequest::HEAD);

		$this->httpRequest->expects('getHeader')
			->once()
			->andReturn(NULL);

		$appRequest = $this->route->match($this->httpRequest);
		Assert::null($appRequest);
	}

	public function testMatchRoute()
	{
		$this->setupRequestMock();
		$this->httpRequest->expects('getMethod')
			->once()
			->andReturn(IRequest::GET);

		$this->httpRequest->expects('getHeader')
			->once()
			->andReturn(NULL);

		$appRequest = $this->route->match($this->httpRequest);
		$params = $appRequest->getParameters();
		Assert::true($appRequest instanceof Nette\Application\Request);
		Assert::equal($params['action'], 'read');
	}

	public function testOverrideRequestMethod()
	{
		$this->setupRequestMock();
		$this->httpRequest->expects('getMethod')
			->once()
			->andReturn(IRequest::GET);

		$appRequest = $this->route->match($this->httpRequest);
		$params = $appRequest->getParameters();
		Assert::true($appRequest instanceof Nette\Application\Request);
		Assert::equal($params['action'], 'read');
	}

	/**
	 * @return void
	 */
	private function setupRequestMock()
	{
		$this->httpRequest->expects('getUrl')
			->once()
			->andReturn($this->createRequestUrlMock());

		$this->httpRequest->expects('getQuery')->once()->andReturn(array());
		$this->httpRequest->expects('getPost')->once()->andReturn(array());
		$this->httpRequest->expects('getFiles')->once()->andReturn(array());
		$this->httpRequest->expects('isSecured')->once()->andReturn(FALSE);
	}

	/**
	 * @param string $path
	 * @return MockInterface
	 */
	private function createRequestUrlMock($path = 'resources/test')
	{
		$url = $this->mockista->create('Nette\Http\Url');
		$url->expects('getHost')->once()->andReturn('host.test');
		$url->expects('getPath')->once()->andReturn($path);
		$url->expects('getBasePath')->once()->andReturn('');
		return $url;
	}

}
\run(new ResourceRouteTest());
