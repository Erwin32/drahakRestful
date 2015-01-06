<?php
namespace Tests\Drahak\Restful\Converters;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Converters\ResourceConverter;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Converters\ResourceConverter.
 *
 * @testCase Tests\Drahak\Restful\Converters\ResourceConverterTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Converters
 */
class ResourceConverterTest extends TestCase
{

	/** @var MockInterface */
	private $converter;

	/** @var ResourceConverter */
	private $resourceConverter;

    public function setUp()
    {
		parent::setUp();
		$this->converter = $this->mockista->create('Drahak\Restful\Converters\IConverter');
		$this->resourceConverter = new ResourceConverter;
    }
    
    public function testConvertsResourceUsingGivenConverters()
    {
		$data = array('test' => 'resource');
		$converted = array('test' => 'resource converted');

		$this->resourceConverter->addConverter($this->converter);
		$this->converter->expects('convert')
			->once()
			->with($data)
			->andReturn($converted);

		$result = $this->resourceConverter->convert($data);
		Assert::same($result, $converted);
    }

}
\run(new ResourceConverterTest());
