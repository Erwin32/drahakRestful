<?php
namespace Drahak\Restful\Application\Events;

use Drahak\Restful\Application\MethodOptions;
use Drahak\Restful\Application\BadRequestException;
use Drahak\Restful\Application\Routes\ResourceRoute;
use Drahak\Restful\Http\Request;
use Nette\Application\Application;
use Nette\Application\BadRequestException as NetteBadRequestException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Object;

/**
 * MethodHandler
 * @package Drahak\Restful\Application
 * @author Drahomír Hanák
 */
class MethodHandler extends Object
{

	/** @var IRequest */
	private $request;

	/** @var IResponse */
	private $response;

	/** @var MethodOptions */
	private $methods;

	/**
	 * @param IRequest $request
	 * @param MethodOptions $methods
	 */
	public function __construct(IRequest $request, IResponse $response, MethodOptions $methods)
	{
		$this->request = $request;
		$this->response = $response;
		$this->methods = $methods;
	}

	/**
	 * On application run
	 * @param Application $application
	 *
	 * @throws BadRequestException
	 */
	public function run(Application $application)
	{
		$router = $application->getRouter();
		$appRequest = $router->match($this->request);
		if (!$appRequest) {
			$this->checkAllowedMethods();
		}
	}

	/**
	 * On application error
	 * @param  Application $application 
	 * @param  Exception   $e           
	 */
	public function error(Application $application, \Exception $e)
	{
		if ($e instanceof NetteBadRequestException && $e->getCode() === 404) {
			$this->checkAllowedMethods();
		}
	}

	/**
	 * Check allowed methods
	 *
	 * @throws BadRequestException If method is not supported but another one can be used
	 */
	protected function checkAllowedMethods()
	{
		$methods = $this->methods->getOptions($this->request->getUrl());
		if (!$methods) return;

		$allow = implode(', ', $methods);
		$this->response->setHeader('Allow', $allow);
		throw BadRequestException::methodNotSupported(
			'Method not supported. Available methods: ' . $allow);
	}

}
