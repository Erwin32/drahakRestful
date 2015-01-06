<?php
namespace Drahak\Restful;

/**
 * Determines usage error
 */
class LogicException extends \LogicException
{
}

/**
 * Thrown when invalid argumnet given to method, function or constructor
 */
class InvalidArgumentException extends LogicException
{
}

/**
 * When requested feature is not implemented
 */
class NotImplementedException extends LogicException
{
}

/**
 * Determines runtime error
 */
class RuntimeException extends \RuntimeException
{
}

/**
 * Thrown when invalid state happend
 */
class InvalidStateException extends RuntimeException
{
}