<?php
namespace Tests\Drahak\Restful\Mapping;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Mapping\IMapper;
use Drahak\Restful\Mapping\JsonMapper;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Mapping\JsonMapper.
 *
 * @testCase Tests\Drahak\Restful\Mapping\JsonMapperTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Mapping
 */
class JsonMapperTest extends TestCase
{

	/** @var IMapper */
	private $mapper;
    
    protected function setUp()
    {
		parent::setUp();
		$this->mapper = new JsonMapper;
    }
    
    public function testConvertArrayToJson()
    {
		$array = array('node' => 'value');
		$json = $this->mapper->stringify($array, FALSE);
		Assert::equal($json, '{"node":"value"}');
    }

	public function testConvertArrayToJsonWithPrettyPrint()
	{
		$array = array('node' => 'value');
		$json = $this->mapper->stringify($array);
		if (!defined('Nette\\Utils\\Json::PRETTY')) {
			Tester\Environment::skip('Json does not support PRETTY PRINT in this Nette version');
		}
		Assert::equal($json, "{\n    \"node\": \"value\"\n}");
	}

	public function testConvertJsonToArray()
	{
		$array = $this->mapper->parse('{"node":"value"}');
		Assert::equal($array['node'], 'value');
	}

	public function testConvertsJsonRecursivelyToArray()
	{
		$array = $this->mapper->parse('{"user":{"name":"test","phone":500}}');
		Assert::equal($array['user']['name'], 'test');
	}

	public function testThrowsExceptionWhenJsonIsInvalid()
	{
		Assert::throws(function() {
			$this->mapper->parse('{"node: "invalid JSON"}');
		}, 'Drahak\Restful\Mapping\MappingException');
	}

}
\run(new JsonMapperTest());
