<?php
namespace Drahak\Restful\Http;

/**
 * REST client request Input interface
 * @package Drahak\Restful\Http
 * @author Drahomír Hanák
 */
interface IInput
{

	/**
	 * Get parsed input data
	 * @return array
	 */
	public function getData();

}