<?php
namespace Drahak\Restful\Application\Routes;

use Nette\Http;
use Nette\Utils\Strings;
use Drahak\Restful\Application\IResourceRouter;

/**
 * Resource CrudRoute to simple resource creation
 * @package Drahak\Restful\Routes
 * @author Drahomír Hanák
 */
class CrudRoute extends ResourceRoute
{

	/** Presenter action names */
    const ACTION_CREATE = 'create<Relation>';
	const ACTION_READ = 'read<Relation>';
	const ACTION_UPDATE = 'update<Relation>';
	const ACTION_PATCH = 'patch<Relation>';
    const ACTION_DELETE = 'delete<Relation>';

	/**
	 * @param string $mask
	 * @param array|string $metadata
	 * @param int $flags
	 */
	public function __construct($mask, $metadata = array(), $flags = IResourceRouter::CRUD)
    {
		if (is_string($metadata) && count(explode(':', $metadata)) === 1) {
			$metadata .= ':default';
		}
        parent::__construct($mask, $metadata, $flags);
		$this->actionDictionary = array(
			IResourceRouter::POST => self::ACTION_CREATE,
			IResourceRouter::GET => self::ACTION_READ,
			IResourceRouter::PUT => self::ACTION_UPDATE,
			IResourceRouter::PATCH => self::ACTION_PATCH,
			IResourceRouter::DELETE => self::ACTION_DELETE
		);
    }

}
