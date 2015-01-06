<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Converters\SnakeCaseConverter;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\SnakeCaseConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\SnakeCaseConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class SnakeCaseConverterTest extends TestCase
{

	/** @var SnakeCaseConverter */
	private $converter;

    protected function setUp()
    {
		parent::setUp();
		$this->converter = new SnakeCaseConverter();
    }
    
    public function testConvertsArrayKeysToSnakeCase()
    {
		$data = array(
			'camelCase' => 'is not so good to read'
		);

		$result = $this->converter->convert($data);
		$keys = array_keys($result);
		Assert::same('camel_case', $keys[0]);
    }


}
\run(new SnakeCaseConverterTest());
