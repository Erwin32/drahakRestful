<?php
namespace Drahak\Restful\Application;

use Nette\Object;
use Nette\DI\Container;
use Nette\Caching\IStorage;
use Nette\Loaders\RobotLoader;
use Nette\Reflection\Method;
use Nette\Reflection\ClassType;
use Drahak\Restful\Utils\Strings;
use Drahak\Restful\InvalidStateException;
use Drahak\Restful\Application\RouteAnnotation;
use Drahak\Restful\Application\Routes\ResourceRoute;
use Drahak\Restful\Application\Routes\ResourceRouteList;

/**
 * RouteListFactory
 * @package Drahak\Restful\Application\Routes
 * @author Drahomír Hanák
 *
 * @property-write string $module
 * @property-write string $prefix
 */
class RouteListFactory extends Object implements IRouteListFactory
{

	/** @var RobotLoader */
	private $loader;

	/** @var string */
	private $module;

	/** @var string */
	private $prefix;

	/** @var IStorage */
	private $cacheStorage;

	/** @var RouteAnnotation */
	private $routeAnnotation;

	/**
	 * @param string $presentersRoot from where to find presenters
	 * @param IStorage $cacheStorage
	 * @param RouteAnnotation $routeAnnotation
	 */
	public function __construct($presentersRoot, IStorage $cacheStorage, RouteAnnotation $routeAnnotation)
	{
		$loader = new RobotLoader();
		$loader->addDirectory($presentersRoot);
		$loader->setCacheStorage($cacheStorage);
		$loader->tryLoad('Drahak\Restful\Application\IResourcePresenter');

		$this->loader = $loader;
		$this->cacheStorage = $cacheStorage;
		$this->routeAnnotation = $routeAnnotation;
	}

	/**
	 * Set default module of created routes
	 * @param string $module
	 * @return ResourceRoute
	 */
	public function setModule($module)
	{
		$this->module = $module;
		return $this;
	}

	/**
	 * Set default routes URL mask prefix
	 * @param string $prefix
	 * @return RouteListFactory
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
		return $prefix;
	}

	/**
	 * Create route list
	 * @param string|null $module
	 * @return ResourceRouteList
	 */
	public final function create($module = NULL)
	{
		$routeList = new ResourceRouteList($module ? $module : $this->module);
		foreach ($this->loader->getIndexedClasses() as $class => $file) {
			try {
				self::getClassReflection($class);
			} catch (InvalidStateException $e) {
				continue;
			}

			$methods = $this->getClassMethods($class);
			$routeData = $this->parseClassRoutes($methods);
			$this->addRoutes($routeList, $routeData, $class);
		}
		return $routeList;
	}

	/******************** Template methods ********************/

	/**
	 * Add class routes to route list
	 * @param ResourceRouteList $routeList
	 * @param array $routeData
	 * @param string $className
	 * @return ResourceRouteList
	 *
	 * @throws InvalidStateException
	 */
	protected function addRoutes(ResourceRouteList $routeList, array $routeData, $className)
	{
		$presenter = str_replace('Presenter', '', self::getClassReflection($className)->getShortName());
		foreach ($routeData as $mask => $dictionary) {
			$routeList[] = new ResourceRoute($mask, array(
				'presenter' => $presenter,
				'action' => $dictionary
			), IResourceRouter::RESTFUL);
		}
		return $routeList;
	}

	/**
	 * Get class methods
	 * @param string $className
	 * @return Method[]
	 *
	 * @throws InvalidStateException
	 */
	protected function getClassMethods($className)
	{
		return self::getClassReflection($className)->getMethods();
	}

	/**
	 * Parse route annotations on given object methods
	 * @param Method[] $methods
	 * @return array $data[URL mask][request method] = action name e.g. $data['api/v1/articles']['GET'] = 'read'
	 */
	protected function parseClassRoutes($methods)
	{
		$routeData = array();
		foreach ($methods as $method) {
			// Parse annotations only on action methods
			if (!Strings::contains($method->getName(), 'action'))
				continue;

			$annotations = $this->routeAnnotation->parse($method);
			foreach ($annotations as $requestMethod => $mask) {
				$action = str_replace('action', '', $method->getName());
				$action = Strings::firstLower($action);

				$pattern = $this->prefix ?
					$this->prefix . '/' .  $mask :
					$mask;

				$routeData[$pattern][$requestMethod] = $action;
			}
		}
		return $routeData;
	}

	/**
	 * Get class reflection
	 * @param string $className
	 * @return ClassType
	 *
	 * @throws InvalidStateException
	 */
	private static function getClassReflection($className)
	{
		if (!method_exists($className, 'getReflection')) {
			throw new InvalidStateException('Invalid class. Missing getReflection method on ' . $className);
		}

		return $className::getReflection();
	}

}
