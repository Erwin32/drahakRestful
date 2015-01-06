<?php
namespace Tests\Drahak\Restful\Application\Events;

require_once __DIR__ . '/../../../../bootstrap.php';

use Drahak\Restful\Application\Events\MethodHandler;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Application\Events\MethodHandler.
 *
 * @testCase Tests\Drahak\Restful\Application\Events\MethodHandlerTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Application\Events
 */
class MethodHandlerTest extends TestCase
{

	/** @var MethodHandler */
	private $methodHandler;

	/** @var MockInterface */
	private $request;

	/** @var MockInterface */
	private $response;

	/** @var MockInterface */
	private $methodOptions;

	/** @var MockInterface */
	private $application;

	/** @var MockInterface */
	private $router;
	
	protected function setUp()
	{
		parent::setUp();
		$this->methodOptions = $this->mockista->create('Drahak\Restful\Application\MethodOptions');
		$this->request = $this->mockista->create('Nette\Http\IRequest');
		$this->response = $this->mockista->create('Nette\Http\IResponse');
		$this->application = $this->mockista->create('Nette\Application\Application');
		$this->router = $this->mockista->create('Nette\Application\IRouter');

		$this->methodHandler = new MethodHandler($this->request, $this->response, $this->methodOptions);
	}

	public function testPassesIfRouterMatchesCurrentRequest()
	{
		$this->application->expects('getRouter')->once()->andReturn($this->router);
		$this->router->expects('match')->once()->with($this->request)->andReturn(TRUE);
		$this->methodHandler->run($this->application);
	}

	public function testPassesIfRouterDoesntMatchButThereAreNoAvailableMethods()
	{
		$url = $this->mockista->create('Nette\Http\UrlScript');
		$this->application->expects('getRouter')->once()->andReturn($this->router);
		$this->router->expects('match')->once()->with($this->request)->andReturn(FALSE);
		$this->request->expects('getUrl')->once()->andReturn($url);
		$this->methodOptions->expects('getOptions')->once()->with($url)->andReturn(array());

		$this->methodHandler->run($this->application);
	}

	public function testThrowsExceptionIfRouteDoesntMatchAndThereAreAvailableMethods()
	{
		$this->application->expects('getRouter')->once()->andReturn($this->router);
		$this->router->expects('match')->once()->with($this->request)->andReturn(FALSE);
		$url = $this->mockista->create('Nette\Http\UrlScript');
		$this->request->expects('getUrl')->once()->andReturn($url);
		$this->methodOptions->expects('getOptions')->once()->with($url)->andReturn(array('GET', 'POST'));
		$this->response->expects('setHeader')->once()->with('Allow', 'GET, POST');

		Assert::exception(function() {
			$this->methodHandler->run($this->application);
		}, 'Drahak\Restful\Application\BadRequestException');
	}

	public function testPassesIfApplicationErrorAppearsButItIsNotBadRequestException()
	{
		$this->methodHandler->error($this->application, new \Exception('Something went wrong.'));
	}

	public function testThrowsExceptionIfBadRequestExceptionAppears()
	{
		$url = $this->mockista->create('Nette\Http\UrlScript');
		$this->request->expects('getUrl')->once()->andReturn($url);
		$this->methodOptions->expects('getOptions')->once()->with($url)->andReturn(array('PATCH', 'PUT'));
		$this->response->expects('setHeader')->once()->with('Allow', 'PATCH, PUT');

		Assert::exception(function() {
			$this->methodHandler->error($this->application, \Drahak\Restful\Application\BadRequestException::notFound('Resource not found'));
		}, 'Drahak\Restful\Application\BadRequestException');
	}

}
\run(new MethodHandlerTest());