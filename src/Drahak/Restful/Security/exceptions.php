<?php
namespace Drahak\Restful\Security;

use Drahak\Restful\RuntimeException;

/**
 * SecurityException is thrown when error in security appears
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class SecurityException extends RuntimeException
{
}

/**
 * UnauthorizedRequestException
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class UnauthorizedRequestException extends SecurityException
{
}

/**
 * AuthenticationException is thrown when request authentication is wrong
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class AuthenticationException extends UnauthorizedRequestException
{
}

/**
 * RequestTimeoutException is thrown when request time is not valid
 * @package Drahak\Restful\Security
 * @author Drahomír Hanák
 */
class RequestTimeoutException extends UnauthorizedRequestException
{
}