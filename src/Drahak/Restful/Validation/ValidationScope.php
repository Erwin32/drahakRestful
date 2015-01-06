<?php
namespace Drahak\Restful\Validation;

use Nette\Object;
use Nette\Utils\Validators;
use Nette\Utils\Strings;

/**
 * ValidationScope
 * @package Drahak\Restful\Validation
 * @author Drahomír Hanák
 *
 * @property-read IValidator $validator
 */
class ValidationScope extends Object implements IValidationScope
{

	/** @var IValidator */
	private $validator;

	/** @var Field[] */
	private $fields = array();

	/**
	 * @param IValidator $validator
	 */
	public function __construct(IValidator $validator)
	{
		$this->validator = $validator;
	}

	/****************** Validation scope interface ******************/

	/**
	 * Create field or get existing
	 * @param string $name
	 * @return IField
	 */
	public function field($name)
	{
		if (!isset($this->fields[$name])) {
			$this->fields[$name] = $this->createField($name);
		}
		return $this->fields[$name];
	}

	/**
	 * Validate all field in collection
	 * @param array $data
	 * @return Error[]
	 */
	public function validate(array $data)
	{
		$errors = array();
		/** @var IField $field */
		foreach ($this->fields as $field) {
			$fieldErrors = $this->validateDeeply($field, $data, $field->getName());
			$errors = array_merge($errors, $fieldErrors);
		}
		return $errors;
	}

	/**
	 * Create field
	 * @param string $name
	 * @return Field
	 */
	protected function createField($name)
	{
		return new Field($name, $this->getValidator());
	}

	/**
	 * Recursively validate data using dot notation
	 * @param  IField $field 
	 * @param  array  $data 
	 * @param  string $path
	 * @return array
	 */
	protected function validateDeeply(IField $field, $data, $path)
	{
		$errors = array();

        if (Validators::isList($data) && count($data)) { 
            foreach ($data as $item) {
                $newErrors = $this->validateDeeply($field, $item, $path);
                $errors = array_merge($errors, $newErrors);
            }
        } else {
			$keys = explode(".", $path);
			$last = count($keys) - 1;
			foreach ($keys as $index => $key) {
				$isLast = $index == $last;
				$value = isset($data[$key]) ? $data[$key] : NULL;

				if (is_array($value)) {
					$newPath = Strings::replace($path, "~^$key\.~");
					$newErrors = $this->validateDeeply($field, $value, $newPath);
					$errors = array_merge($errors, $newErrors);
					break; // because recursion already handled this path validation
				} else if ($isLast || $value === NULL) {
					$newErrors = $field->validate($value);
					$errors = array_merge($errors, $newErrors);  
					break;
				} 
			}
        }

        return $errors;
	}

	/****************** Getters & setters ******************/

	/**
	 * Get validator
	 * @return mixed
	 */
	public function getValidator()
	{
		return $this->validator;
	}

	/**
	 * Get schema fields
	 * @return IField[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

}
