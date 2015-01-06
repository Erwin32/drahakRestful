<?php
namespace Drahak\Restful\Http;

use ArrayIterator;
use IteratorAggregate;
use Nette\Object;
use Nette\Http;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nette\MemberAccessException;
use Drahak\Restful\Validation\IDataProvider;
use Drahak\Restful\Validation\IField;
use Drahak\Restful\Validation\IValidationScope;
use Drahak\Restful\Validation\IValidationScopeFactory;

/**
 * Request Input parser
 * @package Drahak\Restful\Http
 * @author Drahomír Hanák
 *
 * @property array $data
 */
class Input extends Object implements IteratorAggregate, IInput, IDataProvider
{

	/** @var array */
	private $data;

	/** @var IValidationScope */
	private $validationScope;

	/** @var IValidationScopeFactory */
	private $validationScopeFactory;

	/**
	 * @param IValidationScopeFactory $validationScopeFactory
	 * @param array $data
	 */
	public function __construct(IValidationScopeFactory $validationScopeFactory, array $data = array())
	{
		$this->data = $data;
		$this->validationScopeFactory = $validationScopeFactory;
	}

	/******************** IInput ********************/

	/**
	 * Get parsed input data
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set input data
	 * @param array $data
	 * @return Input
	 */
	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}

	/******************** Magic methods ********************/

	/**
	 * @param string $name
	 * @return mixed
	 *
	 * @throws \Exception|\Nette\MemberAccessException
	 */
	public function &__get($name)
	{
		try {
			return parent::__get($name);
		} catch(MemberAccessException $e) {
			$data = $this->getData();
			if (isset($data[$name])) {
				return $data[$name];
			}
			throw $e;
		}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$isset = parent::__isset($name);
		if ($isset) {
			return TRUE;
		}
		$data = $this->getData();
		return isset($data[$name]);
	}

	/******************** Iterator aggregate interface ********************/

	/**
	 * Get input data iterator
	 * @return InputIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getData());
	}

	/******************** Validation data provider interface ********************/

	/**
	 * Get validation field
	 * @param string $name
	 * @return IField
	 */
	public function field($name)
	{
		return $this->getValidationScope()->field($name);
	}

	/**
	 * Validate input data
	 * @return array
	 */
	public function validate()
	{
		return $this->getValidationScope()->validate($this->getData());
	}

	/**
	 * Is input valid
	 * @return bool
	 */
	public function isValid()
	{
		return !$this->validate();
	}

	/**
	 * Get validation scope
	 * @return IValidationScope
	 */
	public function getValidationScope()
	{
		if (!$this->validationScope) {
			$this->validationScope = $this->validationScopeFactory->create();
		}
		return $this->validationScope;
	}

}
