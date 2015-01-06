<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use ArrayIterator;
use Drahak\Restful\Converters\ObjectConverter;
use Drahak\Restful\Resource\Link;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\ObjectConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\ObjectConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class ObjectConverterTest extends TestCase
{

	/** @var ObjectConverter */
	private $converter;

    public function setUp()
    {
		parent::setUp();
		$this->converter = new ObjectConverter;
    }
    
    public function testConvertStdClassToArray()
	{
		$expected = array(
			'stdClass' => array(
				'hello' => 'world'
			)
		);
		$data = array(
			'stdClass' => (object)array('hello' => 'world')
		);

		$result = $this->converter->convert($data);
		Assert::equal($result, $expected);
    }

	public function testConvertTraversableObjectToArrayWithKeys()
	{
		$expected = array(
			'traversable' => array(
				'hello' => 'world'
			)
		);

		$collection = new ArrayIterator(array(
			'hello' => 'world'
		));
		$data = array(
			'traversable' => $collection
		);

		$result = $this->converter->convert($data);
		Assert::same($result, $expected);
	}

	public function testConvertsResourceElementToAnArray()
	{
		$expected = array(
			array('href' => 'http://resource', 'rel' => 'self')
		);
		$data = array();
		$data[] = new Link('http://resource');
		$result = $this->converter->convert($data);
		Assert::same($result, $expected);
	}
    
}
\run(new ObjectConverterTest());
