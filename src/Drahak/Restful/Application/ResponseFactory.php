<?php
namespace Drahak\Restful\Application;

use Drahak\Restful\Application\Responses\BaseResponse;
use Drahak\Restful\Application\Responses\NullResponse;
use Drahak\Restful\InvalidArgumentException;
use Drahak\Restful\InvalidStateException;
use Drahak\Restful\IResource;
use Drahak\Restful\Mapping\MapperContext;
use Drahak\Restful\Utils\RequestFilter;
use Nette\Utils\Strings;
use Nette\Http\IResponse;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Nette\Object;

/**
 * REST ResponseFactory
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
class ResponseFactory extends Object implements IResponseFactory
{

	/** @var IResponse */
	private $response;

	/** @var IRequest */
	private $request;

	/** @var MapperContext */
	private $mapperContext;

	/** @var ICacheValidator */
	private $cacheValidator;

	/** @var string JSONP request key */
	private $jsonp;

	/** @var string pretty print key */
	private $prettyPrint = 'prettyPrint';

	/** @var array */
	private $responses = array(
		IResource::JSON => 'Drahak\Restful\Application\Responses\TextResponse',
		IResource::JSONP => 'Drahak\Restful\Application\Responses\JsonpResponse',
		IResource::QUERY => 'Drahak\Restful\Application\Responses\TextResponse',
		IResource::XML => 'Drahak\Restful\Application\Responses\TextResponse',
		IResource::FILE => 'Drahak\Restful\Application\Responses\FileResponse',
		IResource::NULL => 'Drahak\Restful\Application\Responses\NullResponse'
	);

	/**
	 * @param IResponse $response
	 * @param IRequest $request
	 * @param MapperContext $mapperContext
	 */
	public function __construct(IResponse $response, IRequest $request, MapperContext $mapperContext)
	{
		$this->response = $response;
		$this->request = $request;
		$this->mapperContext = $mapperContext;
	}

	/**
	 * Set JSONP key
	 * @param string $jsonp 
	 */
	public function setJsonp($jsonp)
	{
		$this->jsonp = $jsonp;
		return $this;
	}

	/**
	 * Get JSONP key
	 * @return [type] [description]
	 */
	public function getJsonp()
	{
		return $this->jsonp;
	}

	/**
	 * Set pretty print key
	 * @param string $prettyPrint 
	 */
	public function setPrettyPrint($prettyPrint)
	{
		$this->prettyPrint = $prettyPrint;
		return $this;
	}

	/**
	 * Register new response type to factory
	 * @param string $mimeType
	 * @param string $responseClass
	 * @return $this
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerResponse($mimeType, $responseClass)
	{
		if (!class_exists($responseClass)) {
			throw new InvalidArgumentException('Response class does not exist.');
		}

		$this->responses[$mimeType] = $responseClass;
		return $this;
	}

	/**
	 * Unregister API response from factory
	 * @param string $mimeType
	 */
	public function unregisterResponse($mimeType)
	{
		unset($this->responses[$mimeType]);
	}

	/**
	 * Set HTTP response
	 * @param IResponse $response
	 * @return ResponseFactory
	 */
	public function setHttpResponse(IResponse $response)
	{
		$this->response = $response;
		return $this;
	}

	/**
	 * Create new api response
	 * @param IResource $resource
	 * @param string|null $contentType
	 * @return IResponse
	 *
	 * @throws InvalidStateException
	 */
	public function create(IResource $resource, $contentType = NULL)
	{
		if ($contentType === NULL) {
			$contentType = $this->jsonp === FALSE || !$this->request->getQuery($this->jsonp) ?
				$this->getPreferredContentType($this->request->getHeader('Accept')) :
				IResource::JSONP;
		}

		if (!isset($this->responses[$contentType])) {
			throw new InvalidStateException('Unregistered API response for ' . $contentType);
		}

		if (!class_exists($this->responses[$contentType])) {
			throw new InvalidStateException('API response class does not exist.');
		}

		if (!$resource->getData()) {
			$this->response->setCode(204); // No content
		}

		$responseClass = $this->responses[$contentType];
		$response = new $responseClass($resource->getData(), $this->mapperContext->getMapper($contentType), $contentType);
		if ($response instanceof BaseResponse) {
			$response->setPrettyPrint($this->isPrettyPrint());
		}
		return $response;
	}

	/**
	 * Is given content type acceptable for response
	 * @param  string  $contentType 
	 * @return boolean              
	 */
	public function isAcceptable($contentType)
	{
		try {
			$this->getPreferredContentType($this->request->getHeader('Accept'));
			return TRUE;
		} catch (InvalidStateException $e) {
			return FALSE;
		}
	}

	/**
	 * Is pretty print enabled
	 * @param  IRequest $request 
	 * @return boolean           
	 */
	protected function isPrettyPrint()
	{
		$prettyPrint = $this->request->getQuery($this->prettyPrint);
		if ($prettyPrint === 'false') {
			return FALSE;
		}
		return $prettyPrint === NULL ? TRUE : (bool)$prettyPrint;
	}

	/**
	 * Get preferred request content type
	 * @param  string $contentType may be separed with comma
	 * @return string
	 * 
	 * @throws  InvalidStateException If Accept header is unknown
	 */
	protected function getPreferredContentType($contentType)
	{
		$accept = explode(',', $contentType);
		$acceptableTypes = array_keys($this->responses);
		foreach ($accept as $mimeType) {
			if ($mimeType === '*/*') return $acceptableTypes[0];
			foreach ($acceptableTypes as $formatMime) {
				if (Strings::contains($mimeType, $formatMime)) {
					return $formatMime;
				}
			}
		}
		throw new InvalidStateException('Unknown Accept header: ' . $contentType);
	}

}
