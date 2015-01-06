<?php
namespace Drahak\Restful\Converters;

/**
 * Converts resource or input data to some format or stringify objects
 * @package Drahak\Restful\Converters
 */
interface IConverter
{

    /**
     * Converts data from resource to output
     * @param array $resource
     * @return array
     */
    public function convert(array $resource);

}
