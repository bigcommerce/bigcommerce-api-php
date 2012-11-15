<?php

require_once dirname(__FILE__).'/../Bigcommerce/Api/Filter.php';

class Bigcommerce_Api_FilterTest extends PHPUnit_Framework_TestCase
{

	public function testFactoryWithNoParam()
	{
		$filter = Bigcommerce_Api_Filter::create();
		$this->assertEquals("", $filter->toQuery());
	}

	public function testFactoryWithEmptyParam()
	{
		$filter = Bigcommerce_Api_Filter::create(array());
		$this->assertEquals("", $filter->toQuery());
	}

	public function testFactoryWithSingleParam()
	{
		$filter = Bigcommerce_Api_Filter::create(array("key"=>"value"));
		$this->assertEquals("?key=value", $filter->toQuery());
	}

	public function testFactoryWithMultipleParams()
	{
		$filter = Bigcommerce_Api_Filter::create(array("one"=>"1", "two"=>"2"));
		$this->assertEquals("?one=1&two=2", $filter->toQuery());
	}

}