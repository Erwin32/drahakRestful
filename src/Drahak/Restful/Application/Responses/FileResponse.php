<?php
namespace Drahak\Restful\Application\Responses;

use Drahak\Restful\Mapping\IMapper;
use Drahak\Restful\Resource\Media;
use Nette\Http;

/**
 * FileResponse
 * @package Drahak\Restful\Application\Responses
 * @author Drahomír Hanák
 */
class FileResponse extends BaseResponse
{

	/**
	 * @param Media $data
	 * @param IMapper $mapper
	 * @param string|null $contentType
	 */
	public function __construct(Media $data, IMapper $mapper, $contentType = NULL)
	{
		parent::__construct($mapper, $contentType);
		$this->data = $data;
	}

	/**
	 * Sends response to output
	 * @param Http\IRequest $httpRequest
	 * @param Http\IResponse $httpResponse
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse)
	{
		$httpResponse->setContentType($this->data->contentType);
		echo $this->data;
	}


}
