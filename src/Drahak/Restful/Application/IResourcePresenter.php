<?php
namespace Drahak\Restful\Application;

use Nette\Application\IPresenter;

/**
 * REST API ResourcePresenter
 * @package Drahak\Restful
 * @author Drahomír Hanák
 */
interface IResourcePresenter extends IPresenter
{

	/**
	 * Set API resource
	 * @return void
	 */
	public function sendResource();

}