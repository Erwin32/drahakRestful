<?php
namespace Tests\Drahak\Restful\Resource;

require_once __DIR__ . '/../../../bootstrap.php';

use Drahak\Restful\Resource\Media;
use Nette;
use Tester;
use Tester\Assert;
use Tests\TestCase;

/**
 * Test: Tests\Drahak\Restful\Resource\Media.
 *
 * @testCase Tests\Drahak\Restful\Resource\MediaTest
 * @author Drahomír Hanák
 * @package Tests\Drahak\Restful\Resource
 */
class MediaTest extends TestCase
{

	/** @var Media */
	private $media;

    public function setUp()
    {
		parent::setUp();
		$this->media = new Media('Test file', 'text/plain');
    }
    
    public function testDetermineMediaMimeTypeIfNotSet()
    {
		$type = $this->media->getContentType();
		Assert::equal($type, 'text/plain');
    }

	public function testGetMediaContent()
	{
		$content = $this->media->getContent();
		$magic = (string)$this->media;

		Assert::equal($content, 'Test file');
		Assert::same($content, $magic);
	}

	public function testCreateMediaFromFile()
	{
		$media = Media::fromFile(__DIR__ . '/Media.data.txt', 'text/plain');
		Assert::equal(Nette\Utils\Strings::trim($media->getContent()), 'Test file');
		Assert::equal($media->getContentType(), 'text/plain');
	}
    
}
\run(new MediaTest());
