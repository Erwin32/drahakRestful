<?php
namespace Tests\Drahak\Restful\Validation;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Validation\IValidator;
use Drahak\Restful\Validation\Rule;
use Drahak\Restful\Validation\Validator;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Validation\Validator.
 *
 * @testCase Tests\Drahak\Restful\Validation\ValidatorTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Validation
 */
class ValidatorTest extends TestCase
{

	/** @var Rule */
	private $rule;

	/** @var Validator */
	private $validator;

    protected function setUp()
    {
		parent::setUp();
		$this->rule = new Rule;
		$this->validator = new Validator;
    }
    
    public function testValidateRegularExpression()
    {
		$this->rule->expression = IValidator::REGEXP;
		$this->rule->argument = "/[a-z0-9]*/i";
		$this->validator->validate('05das', $this->rule);
    }

	public function testThrowsExceptionWhenRegularExpressionNotMatch()
	{
		$this->rule->expression = IValidator::REGEXP;
		$this->rule->argument = '/[a-z0-9]{5}/i';
		Assert::throws(function() {
			$this->validator->validate('05_as', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testThrowsExceptionWhenRegularExpressionIsNotGiven()
	{
		$this->rule->expression = IValidator::REGEXP;
		$this->rule->argument = NULL;
		Assert::throws(function() {
			$this->validator->validate('05_as', $this->rule);
		}, 'Drahak\Restful\InvalidArgumentException');
	}

	public function testValidateEqualExpression()
	{
		$this->rule->expression = IValidator::EQUAL;
		$this->rule->argument = 10;
		$this->validator->validate('10', $this->rule);
	}

	public function testThrowsExceptionWhenValuesAreNotSame()
	{
		$this->rule->expression = IValidator::EQUAL;
		$this->rule->argument = 10;
		Assert::throws(function() {
			$this->validator->validate('5', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateEmailExpression()
	{
		$this->rule->expression = IValidator::EMAIL;
		$this->validator->validate('test@domain.com', $this->rule);
	}

	public function testThrowsExceptionWhenEmailIsInvalid()
	{
		$this->rule->expression = IValidator::EMAIL;
		Assert::throws(function() {
			$this->validator->validate('invalid', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateUrl()
	{
		$this->rule->expression = IValidator::URL;
		$this->validator->validate('http://www.domain.com', $this->rule);
	}

	public function testThrowsExceptionWhenUrlIsInvalid()
	{
		$this->rule->expression = IValidator::URL;
		Assert::throws(function() {
			$this->validator->validate('domain', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testStringMinimalLength()
	{
		$this->rule->expression = IValidator::MIN_LENGTH;
		$this->rule->argument = 10;
		$this->validator->validate('asdasfdsb515sdvbsbf', $this->rule);
	}

	public function testThrowsExceptionWhenStingLengthIsTooShort()
	{
		$this->rule->expression = IValidator::MIN_LENGTH;
		$this->rule->argument = 10;
		Assert::throws(function() {
			$this->validator->validate('as', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testStringMaximalLength()
	{
		$this->rule->expression = IValidator::MAX_LENGTH;
		$this->rule->argument = 10;
		$this->validator->validate('asdasd', $this->rule);
	}

	public function testIsNumberWithinRange()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(10, 20);
		$this->validator->validate(15, $this->rule);
	}

	public function testIsNumberBiggerThenGiven()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(10, NULL);
		$this->validator->validate(15, $this->rule);
	}

	public function testIsNumberLowerThenGiven()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(NULL, 10);
		$this->validator->validate(5, $this->rule);
	}

	public function testIsRealNumber()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(NULL, NULL);
		$this->validator->validate(5, $this->rule);
	}

	public function testRangeRuleThrowsExceptionIfValueIsNotOfNumericType()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(0, NULL);
		Assert::throws(function() {
			$this->validator->validate('adfa', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testRangeRuleThrowsExceptionIfNumberOfArgumentsIsInvalid()
	{
		$this->rule->expression = IValidator::RANGE;
		$this->rule->argument = array(NULL);
		Assert::throws(function() {
			$this->validator->validate('adfa', $this->rule);
		}, 'Drahak\Restful\InvalidArgumentException');
	}

	public function testThrowsExceptionWhenStringIsTooLong()
	{
		$this->rule->expression = IValidator::MAX_LENGTH;
		$this->rule->argument = 10;
		Assert::throws(function() {
			$this->validator->validate('asad5aa18dvsa8dv49sd', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testStringLength()
	{
		$this->rule->expression = IValidator::LENGTH;
		$this->rule->argument = array(5, 10);
		$this->validator->validate('ad6as46', $this->rule);
	}

	public function testThrowsExceptionWhenStringLegthIsOutOfRange()
	{
		$this->rule->expression = IValidator::LENGTH;
		$this->rule->argument = array(5, 10);
		Assert::throws(function() {
			$this->validator->validate('asad5aa18dvsa8dv49sd', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateIntegerValue()
	{
		$this->rule->expression = IValidator::INTEGER;
		$this->validator->validate(456, $this->rule);
	}

	public function testThrowsExceptionWhenValueIsNotAnInteger()
	{
		$this->rule->expression = IValidator::INTEGER;
		Assert::throws(function() {
			$this->validator->validate('45', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateFloatValue()
	{
		$this->rule->expression = IValidator::FLOAT;
		$this->validator->validate(45.45698, $this->rule);
	}

	public function testThrowsExceptionWhenValueIsNotFloat()
	{
		$this->rule->expression = IValidator::FLOAT;
		Assert::throws(function() {
			$this->validator->validate('45.56494', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateNumericValue()
	{
		$this->rule->expression = IValidator::NUMERIC;
		$this->validator->validate('45.45698', $this->rule);
	}

	public function testThrowsExceptionWhenValueIsNotNumeric()
	{
		$this->rule->expression = IValidator::NUMERIC;
		Assert::throws(function() {
			$this->validator->validate('text', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testValidateUuid()
	{
		$this->rule->expression = IValidator::UUID;
		$this->validator->validate('bfc5b0f9-a33a-4bf5-8745-0701114ce4f3', $this->rule);
	}

	public function testPassRequiredRuleValidationIfFieldIsNotNull()
	{
		$this->rule->expression = IValidator::REQUIRED;
		$this->validator->validate('a', $this->rule);
	}

	public function testPassRequiredRuleValidationIfFieldIsZero()
	{
		$this->rule->expression = IValidator::REQUIRED;
		$this->validator->validate(0, $this->rule);
	}

	public function testThrowsValidationExceptionIfRequiredFiledIsNull()
	{
		$this->rule->expression = IValidator::REQUIRED;
		Assert::throws(function() {
			$this->validator->validate(NULL, $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testThrowsExceptionWhenValueIsNotValidUUID()
	{
		$this->rule->expression = IValidator::UUID;
		Assert::throws(function() {
			$this->validator->validate('bfc5b0f9-a33a-4bf5-8745', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

	public function testThrowsExceptionWhenCallbackToValidationFunctionIsNotCallable()
	{
		$this->validator->handle['test'] = 'Hello wordl!';
		$this->rule->expression = 'test';
		Assert::exception(function() {
			$this->validator->validate('test', $this->rule);
		}, 'Drahak\Restful\InvalidStateException');
	}

	public function testPassCallbackRuleIfItReturnsTrue()
	{
		$this->rule->expression = IValidator::CALLBACK;
		$this->rule->argument = function($value) {
			return true;
		};
		$this->validator->validate('test', $this->rule);
	}

	public function testThrowsValidationExceptionIfCallbackValidatorResurnsFalse()
	{
		$this->rule->expression = IValidator::CALLBACK;
		$this->rule->argument = function($value) {
			return false;
		};
		Assert::exception(function() {
			$this->validator->validate('test', $this->rule);
		}, 'Drahak\Restful\Validation\ValidationException');
	}

}
\run(new ValidatorTest());
