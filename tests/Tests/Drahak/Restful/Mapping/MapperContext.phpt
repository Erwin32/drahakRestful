<?php
namespace Tests\Drahak\Restful\Mapping;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\IResource;
use Drahak\Restful\Mapping\MapperContext;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Mapping\MapperContext.
 *
 * @testCase Tests\Drahak\Restful\Mapping\MapperContextTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Mapping
 */
class MapperContextTest extends TestCase
{

	/** @var MockInterface */
	private $json;

	/** @var MockInterface */
	private $xml;

	/** @var MapperContext */
	private $context;

    protected function setUp()
    {
		parent::setUp();
		$this->json = $this->mockista->create('Drahak\Restful\Mapping\JsonMapper');
		$this->xml = $this->mockista->create('Drahak\Restful\Mapping\XmlMapper');

		$this->context = new MapperContext;
		$this->context->addMapper(IResource::JSON, $this->json);
    }
    
    public function testSelectMapperByContentType()
    {
		$this->context->addMapper(IResource::XML, $this->xml);
		$mapper = $this->context->getMapper(IResource::XML);

		Assert::same($mapper, $this->xml);
    }

	public function testThrowsExceptionWhenContentTypeIsUnknown()
	{
		Assert::throws(function() {
			$this->context->getMapper(IResource::DATA_URL);
		}, 'Drahak\Restful\InvalidStateException');
	}

	public function testGetMapperFromFullContentTypeSpecification()
	{
		$mapper = $this->context->getMapper('application/json; charset=utf8');
		Assert::same($mapper, $this->json);
	}

}
\run(new MapperContextTest());