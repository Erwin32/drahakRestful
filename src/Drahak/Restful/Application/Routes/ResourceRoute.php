<?php
namespace Drahak\Restful\Application\Routes;

use Drahak\Restful;
use Drahak\Restful\Application\IResourceRouter;
use Nette\Http;
use Nette\Application;
use Nette\Utils\Strings;
use Nette\Application\Routers\Route;

/**
 * ResourceRoute
 * @package Drahak\Restful\Routes
 * @author Drahomír Hanák
 *
 * @property array $actionDictionary
 */
class ResourceRoute extends Route implements IResourceRouter
{

	/** @var array */
	protected $actionDictionary;

	/** @var array */
	private $methodDictionary = array(
		Http\IRequest::GET => self::GET,
		Http\IRequest::POST => self::POST,
		Http\IRequest::PUT => self::PUT,
		Http\IRequest::HEAD => self::HEAD,
		Http\IRequest::DELETE => self::DELETE,
		'PATCH' => self::PATCH
	);

	/**
	 * @param string $mask
	 * @param array|string $metadata
	 * @param int $flags
	 */
	public function __construct($mask, $metadata = array(), $flags = IResourceRouter::GET)
	{
		$this->actionDictionary = array();
		if (isset($metadata['action']) && is_array($metadata['action'])) {
			$this->actionDictionary = $metadata['action'];
			$metadata['action'] = 'default';  
		} else {
			$action = isset($metadata['action']) ? $metadata['action'] : 'default'; 
			if (is_string($metadata)) {
				$metadataParts = explode(':', $metadata);
				$action = end($metadataParts);
			}
			foreach ($this->methodDictionary as $methodName => $methodFlag) {
				if (($this->flags & $methodFlag) == $methodFlag) {
					$this->actionDictionary[$methodFlag] = $action;
				}
			}
		}

		parent::__construct($mask, $metadata, $flags);
	}

	/**
	 * Is this route mapped to given method
	 * @param int $method
	 * @return bool
	 */
	public function isMethod($method)
	{
		$common = array(self::CRUD, self::RESTFUL);
		$isActionDefined = $this->actionDictionary && !in_array($method, $common) ?
			isset($this->actionDictionary[$method]) :
			TRUE;
		return ($this->flags & $method) == $method && $isActionDefined;
	}

	/**
	 * Get request method flag
	 * @param Http\IRequest $httpRequest
	 * @return string|null
	 */
	public function getMethod(Http\IRequest $httpRequest)
	{
		$method = $httpRequest->getMethod();
		if (!isset($this->methodDictionary[$method])) {
			return NULL;
		}
		return $this->methodDictionary[$method];
	}

	/**
	 * Get action dictionary
	 * @return array|NULL
	 */
	public function getActionDictionary()
	{
		return $this->actionDictionary;
	}

	/**
	 * Set action dictionary
	 * @param array|NULL
	 * @return $this
	 */
	public function setActionDictionary($actionDictionary)
	{
		$this->actionDictionary = $actionDictionary;
		return $this;
	}

	/**
	 * @param Http\IRequest $httpRequest
	 * @return Application\Request|NULL
	 */
	public function match(Http\IRequest $httpRequest)
	{
		$appRequest = parent::match($httpRequest);
		if (!$appRequest) {
			return NULL;
		}

		// Check requested method
		$methodFlag = $this->getMethod($httpRequest);
		if (!$this->isMethod($methodFlag)) {
			return NULL;
		}

		// If there is action dictionary, set method
		if ($this->actionDictionary) {
			$parameters = $appRequest->getParameters();
			$parameters['action'] = $this->actionDictionary[$methodFlag];
			$parameters['action'] = self::formatActionName($this->actionDictionary[$methodFlag], $parameters);
			$appRequest->setParameters($parameters);
		}

		return $appRequest;
	}

	/**
	 * @param Application\Request $appRequest
	 * @param Http\Url $refUrl
	 * @return NULL|string
	 */
	public function constructUrl(Application\Request $appRequest, Http\Url $refUrl)
	{
		if (count($this->actionDictionary) > 0) {
			$appRequest = clone $appRequest;
			$params = $appRequest->getParameters();
			$params['action'] = 'default'; // so the request matches with route with action dictionary
			$appRequest->setParameters($params);
		}

		$url = parent::constructUrl($appRequest, $refUrl);
		if ($url === NULL) {
			return NULL;
		}

		$httpUrl = new Http\Url($url);
		$httpUrl->query = Strings::replace($httpUrl->query, '/action=([a-zA-Z0-9_+%-]*)/i', '');
		return $httpUrl->getBasePath() . $httpUrl->getRelativeUrl();
	}

	/**
	 * Format action name
	 * @param string $action
	 * @param array $parameters
	 * @return string
	 */
	protected static function formatActionName($action, array $parameters)
	{
		return Strings::replace($action, "@\<([0-9a-zA-Z_-]+)\>@i", function($m) use($parameters) {
			$key = strtolower($m[1]);
			return isset($parameters[$key]) ? $parameters[$key] : '';
		});
	}

}
