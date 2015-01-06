<?php
namespace Tests\Drahak\Restful\Validation;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Validation\IField;
use Drahak\Restful\Validation\IValidator;
use Drahak\Restful\Validation\ValidationException;
use Drahak\Restful\Validation\ValidationScope;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Validation\ValidationScope.
 *
 * @testCase Tests\Drahak\Restful\Validation\ValidationScopeTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Validation
 */
class ValidationScopeTest extends TestCase
{

	/** @var MockInterface */
	private $validator;

	/** @var ValidationScope */
	private $schema;

	protected function setUp()
	{
		parent::setUp();
		$this->validator = $this->mockista->create('Drahak\Restful\Validation\Validator');
		$this->schema = new ValidationScope($this->validator);
	}

	public function testCreateField()
	{
		$field = $this->schema->field('test');
		Assert::true($field instanceof IField);
		Assert::equal($field->getName(), 'test');
		Assert::equal($field->getValidator(), $this->validator);
	}

	public function testValidateArrayData()
	{
		$exception = new ValidationException('test', 'Please add integer');

		$testField = $this->schema->field('test');
		$testField->addRule(IValidator::INTEGER, 'Please add integer');
		$intigerRule = $testField->rules[0];

		$this->validator->expects('validate')
			->once()
			->with('Hello world', $intigerRule)
			->andThrow($exception);

		$errors = $this->schema->validate(array('test' => 'Hello world'));
		Assert::equal($errors[0]->field, 'test');
		Assert::equal($errors[0]->message, 'Please add integer');
	}

	public function testValidateDataUsingDotNotation()
	{
		$exception = new ValidationException('user.age', 'Please provide age as an integer');

		$ageField = $this->schema->field('user.age');
		$ageField->addRule(IValidator::INTEGER, 'Please provide age as an integer');
		$intigerRule = $ageField->rules[0];

		$this->validator->expects('validate')
			->once()
			->with('test', $intigerRule)
			->andThrow($exception);	

		$errors = $this->schema->validate(array('user' => array('age' => 'test')));
		Assert::equal($errors[0]->field, 'user.age');
		Assert::equal($errors[0]->message, 'Please provide age as an integer');			
	}

	public function testValidateMissingValueIfTheFieldIsRequired()
	{
		$exception = new ValidationException('user.name', 'Required field user.name is missing');

		$ageField = $this->schema->field('user.name');
		$ageField->addRule(IValidator::REQUIRED, "Please fill user name");
		$ageField->addRule(IValidator::MIN_LENGTH, "Min 10 chars", 10);
		$requiredRule = $ageField->rules[0];
		$minLengthRule = $ageField->rules[1];

		$this->validator->expects('validate')
			->once()
			->with('Ar', $requiredRule);	

		$this->validator->expects('validate')
			->once()
			->with('Ar', $minLengthRule)
			->andThrow($exception);	

		$errors = $this->schema->validate(array('user' => array('name' => 'Ar')));
		Assert::equal($errors[0]->field, 'user.name');
		Assert::equal($errors[0]->message, 'Required field user.name is missing');			
	}

	public function testValidateInvalidValuesWhenUsingDotNotation()
	{
		$exception = new ValidationException('user.name', 'Required field user.name is missing');

		$ageField = $this->schema->field('user.name');
		$ageField->addRule(IValidator::REQUIRED, "Please fill user name");
		$requiredRule = $ageField->rules[0];

		$this->validator->expects('validate')
			->once()
			->with(NULL, $requiredRule)
			->andThrow($exception);	

		$errors = $this->schema->validate(array('user' => 'tester'));
		Assert::equal($errors[0]->field, 'user.name');
		Assert::equal($errors[0]->message, 'Required field user.name is missing');			
	}

	public function testValidateAllItemsInArray()
	{
		$exception = new ValidationException('user.name', 'Min 10 chars');

		$field = $this->schema->field('user.name');
		$field->addRule(IValidator::INTEGER, 'Min 10 chars');
		$rule = $field->rules[0];

		$this->validator->expects('validate')
			->once()
			->with('Test', $rule)
			->andThrow($exception);
		$this->validator->expects('validate')
			->once()
			->with('Me', $rule)
			->andThrow($exception);

		$errors = $this->schema->validate(array('user' => array(array('name' => 'Test'), array('name' => 'Me'))));
		Assert::equal($errors[0]->field, 'user.name');
		Assert::equal($errors[0]->message, 'Min 10 chars');
		Assert::equal($errors[1]->field, 'user.name');
		Assert::equal($errors[1]->message, 'Min 10 chars');
	}

}
\run(new ValidationScopeTest());
