<?php

class BigCommerce_Api2_Resource
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

class BigCommerce_Api2_Product extends BigCommerce_Api2_Resource
{

	public function brand()
	{
		return BigCommerce_Api2::getResource($this->fields->brand->resource, 'Brand');
	}

	public function images()
	{
		return BigCommerce_Api2::getCollection($this->fields->images->resource, 'ProductImage');
	}

	public function skus()
	{
		return BigCommerce_Api2::getCollection($this->fields->skus->resource, 'Sku');
	}

	public function rules()
	{
		return BigCommerce_Api2::getCollection($this->fields->rules->resource, 'Rule');
	}

	public function videos()
	{
		return BigCommerce_Api2::getCollection($this->fields->videos->resource, 'Video');
	}

	public function custom_fields()
	{
		return BigCommerce_Api2::getCollection($this->fields->custom_fields->resource, 'CustomField');
	}

	public function configurable_fields()
	{
		return BigCommerce_Api2::getCollection($this->fields->configurable_fields->resource, 'ConfigurableField');
	}

	public function discount_rules()
	{
		return BigCommerce_Api2::getCollection($this->fields->discount_rules->resource, 'DiscountRule');
	}

	public function option_set()
	{
		return BigCommerce_Api2::getResource($this->fields->option_set->resource, 'OptionSet');
	}

	public function update()
	{
		return BigCommerce_Api2::updateProduct($this->id, $this->fields);
	}

}

class BigCommerce_Api2_ProductImage extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Sku extends BigCommerce_Api2_Resource
{

	public function options()
	{
		return BigCommerce_Api2::getCollection($this->fields->options->resource, 'Option');
	}

}

class BigCommerce_Api2_Option extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_CustomField extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_ConfigurableField extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_DiscountRule extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Rule extends BigCommerce_Api2_Resource
{

	public function conditions()
	{
		return BigCommerce_Api2::getCollection($this->fields->conditions->resource, 'RuleCondition');
	}

}

class BigCommerce_Api2_Video extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_RuleCondition extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Category extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Order extends BigCommerce_Api2_Resource
{

}

class BigCommerce_Api2_Customer extends BigCommerce_Api2_Resource
{

	public function addresses()
	{
		return BigCommerce_Api2::getCollection($this->fields->addresses->resource, 'Address');
	}

}

class BigCommerce_Api2_Address extends BigCommerce_Api2_Resource
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

class BigCommerce_Api2_OrderStatus extends BigCommerce_Api2_Resource
{

}