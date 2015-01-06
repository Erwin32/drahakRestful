<?php
namespace Tests\Drahak\Restful\Mapping;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Mapping\DataUrlMapper;
use Mockista\MockInterface;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Mapping\DataUrlMapper.
 *
 * @testCase Tests\Drahak\Restful\Mapping\DataUrlMapperTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Mapping
 */
class DataUrlMapperTest extends TestCase
{

	/** @var DataUrlMapper */
	private $mapper;

	/** @var MockInterface */
	private $media;

    protected function setUp()
    {
		parent::setUp();
		$this->media = $this->mockista->create('Drahak\Restful\Resource\Media');
		$this->mapper = new DataUrlMapper;
    }
    
    public function testEncodeContentToBase64WithMimeTypeFromMediaObject()
    {
		$this->media->expects('__toString')
			->once()
			->andReturn('Hello world');
		$this->media->expects('getContentType')
			->once()
			->andReturn('text/plain');

		$encoded = $this->mapper->stringify($this->media);
		Assert::equal($encoded, 'data:text/plain;base64,SGVsbG8gd29ybGQ=');
    }

	public function testDecodeBase64DataToMediaObject()
	{
		$result = $this->mapper->parse('data:text/plain;base64,SGVsbG8gd29ybGQ=');
		Assert::equal($result->getContent(), 'Hello world');
		Assert::equal($result->getContentType(), 'text/plain');
	}

}
\run(new DataUrlMapperTest());
