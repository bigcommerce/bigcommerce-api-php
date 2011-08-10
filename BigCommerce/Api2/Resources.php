<?php

class BigCommerce_Api2_Resource
{
	protected $fields;
	protected $id;

	public function __construct($object=false)
	{
		$this->fields = ($object) ? json_decode($object) : new stdClass;
		$this->id = ($object) ? $this->fields->id : 0;
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
	
	public function toJson()
	{
		return json_encode($this->fields);
	}
	
}

class BigCommerce_Api2_Product
{
	
	public function update()
	{
		return BigCommerce_Api2::updateProduct($this->id, $this->fields);
	}
	
}

class BigCommerce_Api2_Brand
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