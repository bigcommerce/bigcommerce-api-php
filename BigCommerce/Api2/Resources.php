<?php

class BigCommerce_Api2_Resource
{
	protected $fields;
	protected $id;

	public function __construct($object=false)
	{
		$this->fields = ($object) ? $object : new stdClass;
		$this->id = ($object) ? $object->id : 0;
	}

	public function __get($field)
	{
		if (method_exists($this, $field)) {
			return $this->$field();
		}
		return (isset($this->fields->$field)) ? $this->fields->$field : null;
	}

	public function __set($field, $value)
	{
		$this->fields->$field = $value;
	}

}

class BigCommerce_Api2_Product extends BigCommerce_Api2_Resource
{

	public function update()
	{
		return BigCommerce_Api2::updateProduct($this->id, $this->fields);
	}

}

class BigCommerce_Api2_Category extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Order extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Customer extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_OptionSet extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Brand extends BigCommerce_Api2_Resource
{

	public function create()
	{
		return BigCommerce_Api2::createBrand($this->fields);
	}

	public function update()
	{
		return BigCommerce_Api2::updateBrand($this->id, $this->fields);
	}

}