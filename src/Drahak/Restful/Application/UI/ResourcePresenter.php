<?php
namespace Drahak\Restful\Application\UI;

use Drahak\Restful\Application\BadRequestException;
use Drahak\Restful\Application\IResourcePresenter;
use Drahak\Restful\Application\IResponseFactory;
use Drahak\Restful\Application\Responses\ErrorResponse;
use Drahak\Restful\Http\IInput;
use Drahak\Restful\Http\InputFactory;
use Drahak\Restful\Http\Request;
use Drahak\Restful\IResource;
use Drahak\Restful\IResourceFactory;
use Drahak\Restful\InvalidStateException;
use Drahak\Restful\Converters;
use Drahak\Restful\Resource\Link;
use Drahak\Restful\Security\AuthenticationContext;
use Drahak\Restful\Security\SecurityException;
use Drahak\Restful\Utils\RequestFilter;
use Drahak\Restful\Validation\IDataProvider;
use Drahak\Restful\Validation\ValidationException;
use Nette\Application;
use Nette\Application\UI;
use Nette\Application\IResponse;
use Nette\Http;

/**
 * Base presenter for REST API presenters
 * @package Drahak\Restful\Application
 * @author Drahomír Hanák
 */
abstract class ResourcePresenter extends UI\Presenter implements IResourcePresenter
{

	/** @internal */
	const VALIDATE_ACTION_PREFIX = 'validate';

	/** @var IResource */
	protected $resource;

	/** @var RequestFilter */
	protected $requestFilter;

	/** @var IResourceFactory */
	protected $resourceFactory;

	/** @var IResponseFactory */
	protected $responseFactory;

	/** @var AuthenticationContext */
	protected $authentication;

	/** @var IInput|IDataProvider */
	private $input;

	/** InputFactory */
	private $inputFactory;

	/**
	 * Inject Drahak Restful
	 * @param IResponseFactory $responseFactory
	 * @param IResourceFactory $resourceFactory
	 * @param AuthenticationContext $authentication
	 * @param IInput $input
	 * @param RequestFilter $requestFilter
	 */
	public final function injectDrahakRestful(
		IResponseFactory $responseFactory, IResourceFactory $resourceFactory,
		AuthenticationContext $authentication, InputFactory $inputFactory, RequestFilter $requestFilter)
	{
		$this->responseFactory = $responseFactory;
		$this->resourceFactory = $resourceFactory;
		$this->authentication = $authentication;
		$this->requestFilter = $requestFilter;
		$this->inputFactory = $inputFactory;
	}

	/**
	 * Get input
	 * @return IInput 
	 */
	public function getInput()
	{
		if (!$this->input) {
			try {
				$this->input = $this->inputFactory->create();
			} catch(BadRequestException $e) {
				$this->sendErrorResource($e);
			}
		}
		return $this->input;
	}

	/**
	 * Presenter startup
	 *
	 * @throws BadRequestException
	 */
	protected function startup()
	{
		parent::startup();
		$this->autoCanonicalize = FALSE;

		try {
			// Create resource object
			$this->resource = $this->resourceFactory->create();

			// Check if Accept header is invalid
			$accept = $this->getHttpRequest()->getHeader('Accept');
			if (!$this->responseFactory->isAcceptable($accept)) {
				throw new InvalidStateException('Cannot determine response with content type ' . $accept, 406);
			}

			// calls $this->validate<Action>()
			$validationProcessed = $this->tryCall($this->formatValidateMethod($this->action), $this->params);

			// Check if input is validate
			if (!$this->getInput()->isValid() && $validationProcessed === TRUE) {
				$errors = $this->getInput()->validate();
				throw BadRequestException::unprocessableEntity($errors, 'Validation Failed: ' . $errors[0]->message);
			}
		} catch (BadRequestException $e) {
			if ($e->getCode() === 422) {
				$this->sendErrorResource($e);
				return;
			}
			throw $e;
		} catch (InvalidStateException $e) {
			$this->sendErrorResource($e);
		}
	}

	/**
	 * Check security and other presenter requirements
	 * @param $element
	 */
	public function checkRequirements($element)
	{
		try {
			parent::checkRequirements($element);
		} catch (Application\ForbiddenRequestException $e) {
			$this->sendErrorResource($e);
		}

		// Try to authenticate client
		try {
			$this->authentication->authenticate($this->getInput());
		} catch (SecurityException $e) {
			$this->sendErrorResource($e);
		}
	}

	/**
	 * On before render
	 */
	protected function beforeRender()
	{
		parent::beforeRender();
		$this->sendResource();
	}

	/**
	 * Get REST API response
	 * @param string $contentType
	 * @return IResponse
	 *
	 * @throws InvalidStateException
	 */
	public function sendResource($contentType = NULL)
	{
		if (!($this->resource instanceof IResource)) {
			$this->resource = $this->resourceFactory->create($this->resource);
		}

		try {
			$response = $this->responseFactory->create($this->resource, $contentType);
			$this->sendResponse($response);
		} catch (InvalidStateException $e) {
			$this->sendErrorResource(BadRequestException::unsupportedMediaType($e->getMessage(), $e));
		}
	}

	/**
	 * Send error resource to output
	 * @param \Exception $e
	 */
	protected function sendErrorResource(\Exception $e)
	{
		/** @var Request $request */
		$request = $this->getHttpRequest();
		$code = $e->getCode() ? $e->getCode() : 500;
		if ($code < 100 || $code > 599) {
			$code = 400;
		}

		$error = array(
			'code' => $code,
			'status' => 'error',
			'message' => $e->getMessage()
		);
		$this->resource = $this->resourceFactory->create($error);
		
		if (isset($e->errors) && $e->errors) {
			$this->resource->errors = $e->errors;
		}

		$accept = $request->getHeader('Accept');
		$contentType = !$accept || !$this->responseFactory->isAcceptable($accept) ? IResource::JSON : NULL;
		try {
			$response = $this->responseFactory->create($this->resource, $contentType);
			$response = new ErrorResponse($response, $code);
			$this->sendResponse($response);
		} catch (InvalidStateException $e) {
			$this->sendErrorResource(BadRequestException::unsupportedMediaType($e->getMessage(), $e));
		}
	}

	/**
	 * Create resource link representation object
	 * @param string $destination
	 * @param array $args
	 * @param string $rel
	 * @return Link
	 */
	public function link($destination, $args = array(), $rel = Link::SELF)
	{
		$href = parent::link($destination, $args);
		return new Link($href, $rel);
	}


	/****************** Format methods ******************/

	/**
	 * Validate action method
	 */
	public static function formatValidateMethod($action)
	{
		return self::VALIDATE_ACTION_PREFIX . $action;
	}

}
