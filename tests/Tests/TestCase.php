<?php
namespace Tests;

use Mockista\Registry;
use Tester;

/**
 * TestCase
 * @package Tests
 * @author Drahomír Hanák
 */
class TestCase extends Tester\TestCase
{

    /** @var Registry */
    protected $mockista;

    protected function setUp()
    {
        $this->mockista = new Registry;
    }

}