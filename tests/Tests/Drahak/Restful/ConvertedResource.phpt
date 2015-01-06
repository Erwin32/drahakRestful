<?php
namespace Tests\Drahak\Restful;

require_once __DIR__ . '/../../bootstrap.php';

use Drahak\Restful\ConvertedResource;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\ConvertedResource.
 *
 * @testCase Tests\Drahak\Restful\ConvertedResourceTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful
 */
class ConvertedResourceTest extends TestCase
{

	/** @var array */
	private $data;

	/** @var MockInterface */
	private $resourceConverter;

	/** @var ConvertedResource */
	private $resource;

    public function setUp()
    {
		parent::setUp();
		$this->data = array(
			'I really_do not_like_WhenPeople do not_comply WithStandards' => 'Hello'
		);
		$this->resourceConverter = $this->mockista->create('Drahak\Restful\Converters\ResourceConverter');
		$this->resource = new ConvertedResource($this->resourceConverter, $this->data);
    }
    
    public function testGetConvertedDataUsingResourceConverter()
    {
		$converted = array(
			'i_really_do_not_like__when_people_do_not_comply__with_standards' => 'Hello'
		);

		$this->resourceConverter->expects('convert')
			->once()
			->with($this->data)
			->andReturn($converted);

		$data = $this->resource->getData();
		Assert::equal($data, $converted);
    }

    
}
\run(new ConvertedResourceTest());
