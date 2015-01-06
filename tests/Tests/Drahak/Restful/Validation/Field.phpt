<?php
namespace Tests\Drahak\Restful\Validation;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Validation\Field;
use Drahak\Restful\Validation\IValidator;
use Drahak\Restful\Validation\ValidationException;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Validation\Field.
 *
 * @testCase Tests\Drahak\Restful\Validation\FieldTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Validation
 */
class FieldTest extends TestCase
{

	/** @var MockInterface */
	private $validator;

	/** @var Field */
	private $field;

    protected function setUp()
    {
		parent::setUp();
		$this->validator = $this->mockista->create('Drahak\Restful\Validation\Validator');
		$this->field = new Field('test', $this->validator);
    }
    
    public function testAddRuleToField()
    {
		$this->field->addRule(IValidator::MAX_LENGTH, 'Please enter a value of at least %d characters.', 100);
		$rules = $this->field->getRules();
		Assert::equal($rules[0]->field, 'test');
		Assert::equal($rules[0]->message, 'Please enter a value of at least %d characters.');
		Assert::equal($rules[0]->argument, array(100));
		Assert::equal($rules[0]->expression, IValidator::MAX_LENGTH);
    }

	public function testValidateFieldValue()
	{
		$this->field->addRule(IValidator::MAX_LENGTH, 'Please enter a value of at least %d characters.', 100);
		$rules = $this->field->getRules();

		$this->validator->expects('validate')
			->once()
			->with('hello world', $rules[0])
			->andReturn(NULL);

		$result = $this->field->validate('hello world');

		Assert::same($result, array());
	}

	public function testProvideErrorListWhenValidationFails()
	{
		$exception = new ValidationException('test', 'Please enter a value of at least 3 characters.');
		$this->field->addRule(IValidator::MAX_LENGTH, 'Please enter a value of at least %d characters.', 3);
		$rules = $this->field->getRules();

		$this->validator->expects('validate')
			->once()
			->with('hello world', $rules[0])
			->andThrow($exception);

		$result = $this->field->validate('hello world');

		Assert::same($result[0]->field, 'test');
		Assert::same($result[0]->message, 'Please enter a value of at least 3 characters.');
		Assert::equal($result[0]->code, 0);
	}

	public function testSkipOptionalFieldIfIsNotSet()
	{
		$this->field->addRule(IValidator::EMAIL);

		$result = $this->field->validate(NULL);
		Assert::equal($result, array());
	}

	public function testSetValidationRuleCode()
	{
		$this->field->addRule(IValidator::EMAIL, 'Please enter valid email address', NULL, 4025);
		$rule = $this->field->getRules()[0];
		Assert::equal($rule->code, 4025);
	}

	public function testFiledIsRequiredIfItHasRequiredRule()
	{
		$this->field->addRule(IValidator::MAX_LENGTH);
		$this->field->addRule(IValidator::REQUIRED);
		$required = $this->field->isRequired();
		Assert::true($required);
	}

	public function testFiledIsNotRequiredIfItHasNotRequiredRule()
	{
		$this->field->addRule(IValidator::MAX_LENGTH);
		$this->field->addRule(IValidator::MIN_LENGTH);
		$required = $this->field->isRequired();
		Assert::false($required);
	}

}
\run(new FieldTest());
