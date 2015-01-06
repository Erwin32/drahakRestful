<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Converters\PascalCaseConverter;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\PascalCaseConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\PascalCaseConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class PascalCaseConverterTest extends TestCase
{

	/** @var PascalCaseConverter */
	private $converter;
    
    protected function setUp()
    {
		parent::setUp();
		$this->converter = new PascalCaseConverter();
    }
    
    public function testConvertsArrayKeysToCamelCase()
    {
		$data = array(
			'nice array-key' => 'value'
		);

		$result = $this->converter->convert($data);
		$keys = array_keys($result);
		Assert::same('NiceArrayKey', $keys[0]);
    }

}
\run(new PascalCaseConverterTest());
