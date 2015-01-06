<?php
namespace Tests\Drahak\Restful;

require_once __DIR__ . '/../../bootstrap.php';

use Drahak\Restful\IResource;
use Drahak\Restful\Resource;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;


/**
 * Test: Tests\Drahak\Restful\Resource.
 *
 * @testCase Tests\Drahak\Restful\ResourceTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful
 */
class ResourceTest extends TestCase
{

	/** @var Resource */
	private $resource;

    protected function setUp()
    {
		parent::setUp();
		$this->resource = new Resource();
    }

    public function testAddingDataThroughArrayAccess()
    {
		$this->resource['name'] = 'Test';
		$data = $this->resource->getData();
		Assert::equal($data['name'], 'Test');
    }

	public function testAddingArrayListThroughArrayAccess()
	{
		$this->resource[] = 'hello';
		$this->resource[] = 'world';
		$data = $this->resource->getData();
		Assert::equal($data[0], 'hello');
		Assert::equal($data[1], 'world');
	}

	public function testAddingDataThroughMagicMethods()
	{
		$this->resource->name = 'Test';
		Assert::equal($this->resource->name, 'Test');
	}

}
\run(new ResourceTest());
