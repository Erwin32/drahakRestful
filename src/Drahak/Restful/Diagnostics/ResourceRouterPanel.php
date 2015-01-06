<?php
namespace Drahak\Restful\Diagnostics;

use Traversable;
use Drahak\Restful\Application\IResourceRouter;
use Nette\Application\IRouter;
use Nette\Templating\Helpers;
use Nette\Diagnostics\IBarPanel;
use Nette\Object;
use Nette\Utils\Html;

/**
 * ResourceRouterPanel to see REST API resource routes
 * @package Drahak\Restful\Diagnostics
 * @author Drahomír Hanák
 */
class ResourceRouterPanel extends Object implements IBarPanel
{

	/** @var IRouter */
	private $router;

	/** @var string */
	private $secretKey;

	/** @var string */
	private $requestTimeKey;

	public function __construct($secretKey, $requestTimeKey, IRouter $router)
	{
		$this->secretKey = $secretKey;
		$this->requestTimeKey = $requestTimeKey;
		$this->router = $router;
	}

	/**
	 * @param $routeList
	 * @return array
	 */
	private function getResourceRoutes($routeList)
	{
		static $resourceRoutes = array();
		foreach ($routeList as $route) {
			if ($route instanceof Traversable)
				$this->getResourceRoutes($route);
			if ($route instanceof IResourceRouter)
				$resourceRoutes[] = $route;
		}
		return $resourceRoutes;
	}

	/**
	 * Renders HTML code for custom tab.
	 * @return string
	 */
	public function getTab()
	{
		$icon = Html::el('img')
			->src(Helpers::dataStream(file_get_contents(__DIR__ . '/icon.png')))
			->height('16px');
		return '<span class="REST API resource routes">'  .$icon . 'API resources</span>';
	}

	/**
	 * Renders HTML code for custom panel.
	 * @return string
	 */
	public function getPanel()
	{
		ob_start();
		$esc = callback('Nette\Templating\Helpers::escapeHtml');
		$routes = $this->getResourceRoutes($this->router);
		$methods = array(
			IResourceRouter::GET => 'GET',
			IResourceRouter::POST => 'POST',
			IResourceRouter::PUT => 'PUT',
			IResourceRouter::DELETE => 'DELETE',
			IResourceRouter::HEAD => 'HEAD',
			IResourceRouter::PATCH => 'PATCH'
		);
		$privateKey = $this->secretKey;
		$requestTimeKey = $this->requestTimeKey;

		require_once __DIR__ . '/panel.phtml';
		return ob_get_clean();

	}

}
