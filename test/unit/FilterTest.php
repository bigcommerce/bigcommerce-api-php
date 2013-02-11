<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Bigcommerce\Api\Filter;

class FilterTest extends PHPUnit_Framework_TestCase
{
	public function testFactoryWithNoParam()
	{
		$filter = Filter::create();
		$this->assertEquals("", $filter->toQuery());
	}

	public function testFactoryWithEmptyParam()
	{
		$filter = Filter::create(array());
		$this->assertEquals("", $filter->toQuery());
	}

	public function testFactoryWithSingleParam()
	{
		$filter = Filter::create(array("key"=>"value"));
		$this->assertEquals("?key=value", $filter->toQuery());
	}

	public function testFactoryWithMultipleParams()
	{
		$filter = Filter::create(array("one"=>"1", "two"=>"2"));
		$this->assertEquals("?one=1&two=2", $filter->toQuery());
	}

}