<?php
namespace Drahak\Restful\Application;

use Drahak\Restful\Application\Routes\ResourceRouteList;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\Finder;

/**
 * CachedRouteListFactory
 * @package Drahak\Restful\Application\Routes
 * @author Drahomír Hanák
 */
final class CachedRouteListFactory extends Object implements IRouteListFactory
{

	/** Cache name */
	const CACHE_NAME = 'resourceRouteList';

	/** @var Cache */
	private $cache;

	/** @var string */
	private $presentersRoot;

	/** @var IRouteListFactory */
	private $routeListFactory;

	/**
	 * @param string $presentersRoot
	 * @param IRouteListFactory $routeListFactory
	 * @param IStorage $storage
	 */
	public function __construct($presentersRoot, IRouteListFactory $routeListFactory, IStorage $storage)
	{
		$this->presentersRoot = $presentersRoot;
		$this->routeListFactory = $routeListFactory;
		$this->cache = new Cache($storage, get_class($this));
	}

	/**
	 * Create cached route list
	 * @param null $module
	 * @return ResourceRouteList
	 */
	private function createCached($module = NULL)
	{
		$files = array();
		$presenterFiles = Finder::findFiles('*Presenter.php')->from($this->presentersRoot);
		foreach ($presenterFiles as $path => $splFile) {
			$files[] = $path;
		}

		$routeList = $this->routeListFactory->create($module);
		$this->cache->save(self::CACHE_NAME, $routeList, array(
			Cache::FILES => $files
		));
		return $routeList;
	}

	/******************** Route list factory ********************/

	/**
	 * Create resources route list
	 * @param string|null $module
	 * @return ResourceRouteList
	 */
	public function create($module = NULL)
	{
		$routeList = $this->cache->load(self::CACHE_NAME);
		if ($routeList !== NULL) {
			return $routeList;
		}
		return $this->createCached($module);
	}

}
