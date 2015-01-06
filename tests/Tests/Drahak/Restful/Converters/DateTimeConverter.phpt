<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Converters\DateTimeConverter;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\DateTimeConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\DateTimeConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class DateTimeConverterTest extends TestCase
{

	/** @var DateTimeConverter */
	private $converter;

    protected function setUp()
    {
		parent::setUp();
		$this->converter = new DateTimeConverter('c');
    }
    
    public function testConvertDateTimeObjectsToString()
    {
		$data = array(
			array(
				'date' => new \DateTime('19.1.1996'),
				'modified' => new \DateTime('19.1.1996'),
			)
		);

		$data = $this->converter->convert($data);
		Assert::equal($data[0]['date'], '1996-01-19T00:00:00+01:00');
		Assert::equal($data[0]['modified'], '1996-01-19T00:00:00+01:00');
    }

}
\run(new DateTimeConverterTest());
