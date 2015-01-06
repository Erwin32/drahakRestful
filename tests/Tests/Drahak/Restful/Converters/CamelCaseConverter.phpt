<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Converters\CamelCaseConverter;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\CamelCaseConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\CamelCaseConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class CamelCaseConverterTest extends TestCase
{

	/** @var CamelCaseConverter */
	private $converter;
    
    protected function setUp()
    {
		parent::setUp();
		$this->converter = new CamelCaseConverter();
    }
    
    public function testConvertsArrayKeysToCamelCase()
    {
		$data = array(
			'nice array-key' => 'value'
		);

		$result = $this->converter->convert($data);
		$keys = array_keys($result);
		Assert::same('niceArrayKey', $keys[0]);
    }

}
\run(new CamelCaseConverterTest());
