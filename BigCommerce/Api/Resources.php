<?php

class BigCommerce_Api_Resource
{
	protected $fields;
	protected $id;

	public function __construct($object=false)
	{
		if (is_array($object)) {
			$object = (isset($object[0])) ? $object[0] : false;
		}
		$this->fields = ($object) ? $object : new stdClass;
		$this->id = ($object) ? $object->id : 0;
	}

	public function __get($field)
	{
		if (method_exists($this, $field) && isset($this->fields->$field)) {
			return $this->$field();
		}
		return (isset($this->fields->$field)) ? $this->fields->$field : null;
	}

	public function __set($field, $value)
	{
		$this->fields->$field = $value;
	}

}

class BigCommerce_Api_Product extends BigCommerce_Api_Resource
{

	public function brand()
	{
		return BigCommerce_Api::getResource($this->fields->brand->resource, 'Brand');
	}

	public function images()
	{
		return BigCommerce_Api::getCollection($this->fields->images->resource, 'ProductImage');
	}

	public function skus()
	{
		return BigCommerce_Api::getCollection($this->fields->skus->resource, 'Sku');
	}

	public function rules()
	{
		return BigCommerce_Api::getCollection($this->fields->rules->resource, 'Rule');
	}

	public function videos()
	{
		return BigCommerce_Api::getCollection($this->fields->videos->resource, 'Video');
	}

	public function custom_fields()
	{
		return BigCommerce_Api::getCollection($this->fields->custom_fields->resource, 'CustomField');
	}

	public function configurable_fields()
	{
		return BigCommerce_Api::getCollection($this->fields->configurable_fields->resource, 'ConfigurableField');
	}

	public function discount_rules()
	{
		return BigCommerce_Api::getCollection($this->fields->discount_rules->resource, 'DiscountRule');
	}

	public function option_set()
	{
		return BigCommerce_Api::getResource($this->fields->option_set->resource, 'OptionSet');
	}

	public function update()
	{
		return BigCommerce_Api::updateProduct($this->id, $this->fields);
	}

}

class BigCommerce_Api_ProductImage extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Sku extends BigCommerce_Api_Resource
{

	public function options()
	{
		return BigCommerce_Api::getCollection($this->fields->options->resource, 'Option');
	}

}

class BigCommerce_Api_Option extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_CustomField extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_ConfigurableField extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_DiscountRule extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Rule extends BigCommerce_Api_Resource
{

	public function conditions()
	{
		return BigCommerce_Api::getCollection($this->fields->conditions->resource, 'RuleCondition');
	}

}

class BigCommerce_Api_Video extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_RuleCondition extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Category extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Order extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Customer extends BigCommerce_Api_Resource
{

	public function addresses()
	{
		return BigCommerce_Api::getCollection($this->fields->addresses->resource, 'Address');
	}

}

class BigCommerce_Api_Address extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_OptionSet extends BigCommerce_Api_Resource
{

}

class BigCommerce_Api_Brand extends BigCommerce_Api_Resource
{

	public function create()
	{
		return BigCommerce_Api::createBrand($this->fields);
	}

	public function update()
	{
		return BigCommerce_Api::updateBrand($this->id, $this->fields);
	}

}

class BigCommerce_Api_OrderStatus extends BigCommerce_Api_Resource
{

}