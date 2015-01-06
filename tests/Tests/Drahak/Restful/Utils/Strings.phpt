<?php
namespace Tests\Drahak\Restful\Utils;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Utils\Strings;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Utils\Strings.
 *
 * @testCase Tests\Drahak\Restful\Utils\StringsTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Utils
 */
class StringsTest extends TestCase
{

	public function testConvertsFirstLetterToLowerCase()
	{
		$string = Strings::firstLower('LOWER');
		Assert::equal($string, 'lOWER');
	}

    public function testConvertsStringToCamelCase()
    {
		$camel = Strings::toCamelCase('I really_do not_like_WhenPeople do not_comply WithStandards');
		Assert::equal($camel, 'iReallyDoNotLikeWhenPeopleDoNotComplyWithStandards');
    }

	public function testConvertsStringToSnakeCase()
	{
		$snake = Strings::toSnakeCase('I really_do not_like_WhenPeople do not_comply WithStandards');
		Assert::equal($snake, 'i_really_do_not_like_when_people_do_not_comply_with_standards');
	}

	public function testConvertsStringToPascalCase()
	{
		$pascal = Strings::toPascalCase('I really_do not_like_WhenPeople do not_comply WithStandards');
		Assert::equal($pascal, 'IReallyDoNotLikeWhenPeopleDoNotComplyWithStandards');
	}

}
\run(new StringsTest());