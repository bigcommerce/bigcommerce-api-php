<?php

class Bigcommerce_Api_Resource
{
	/**
	 *
	 * @var stdclass
	 */
	protected $fields;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $ignoreOnCreate = array();

	/**
	 * @var array
	 */
	protected $ignoreOnUpdate = array();

	/**
	 * @var array
	 */
	protected $ignoreIfZero = array();

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

	public function getCreateFields()
	{
		$resource = $this->fields;

		foreach($this->ignoreOnCreate as $field) {
			if (isset($resource->$field)) unset($resource->$field);
		}

		return $resource;
	}

	public function getUpdateFields()
	{
		$resource = $this->fields;

		foreach($this->ignoreOnUpdate as $field) {
			if (isset($resource->$field)) unset($resource->$field);
		}

		foreach($resource as $field => $value) {
			if ($this->isIgnoredField($field, $value)) unset($resource->$field);
		}

		return $resource;
	}

	private function isIgnoredField($field, $value)
	{
		if ($value === null) return true;

		if ((strpos($field, "date") !== FALSE) && $value === "") return true;

		if (in_array($field, $this->ignoreIfZero) && $value === 0) return true;

		return false;
	}

}

/**
 * Represents a single product.
 */
class Bigcommerce_Api_Product extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'date_created',
		'date_modified',
	);

	/**
	 * @see https://developer.bigcommerce.com/display/API/Products#Products-ReadOnlyFields
	 * @var array
	 */
	protected $ignoreOnUpdate = array(
		'id',
		'rating_total',
		'rating_count',
		'date_created',
		'date_modified',
		'date_last_imported',
		'number_sold',
		'brand',
		'images',
		'discount_rules',
		'configurable_fields',
		'custom_fields',
		'videos',
		'skus',
		'rules',
		'option_set',
		'options',
		'tax_class',
	);

	protected $ignoreIfZero = array(
		'tax_class_id',
	);

	public function brand()
	{
		return Bigcommerce_Api::getResource($this->fields->brand->resource, 'Brand');
	}

	public function images()
	{
		return Bigcommerce_Api::getCollection($this->fields->images->resource, 'ProductImage');
	}

	public function skus()
	{
		return Bigcommerce_Api::getCollection($this->fields->skus->resource, 'Sku');
	}

	public function rules()
	{
		return Bigcommerce_Api::getCollection($this->fields->rules->resource, 'Rule');
	}

	public function videos()
	{
		return Bigcommerce_Api::getCollection($this->fields->videos->resource, 'Video');
	}

	public function custom_fields()
	{
		return Bigcommerce_Api::getCollection($this->fields->custom_fields->resource, 'CustomField');
	}

	public function configurable_fields()
	{
		return Bigcommerce_Api::getCollection($this->fields->configurable_fields->resource, 'ConfigurableField');
	}

	public function discount_rules()
	{
		return Bigcommerce_Api::getCollection($this->fields->discount_rules->resource, 'DiscountRule');
	}

	public function option_set()
	{
		return Bigcommerce_Api::getResource($this->fields->option_set->resource, 'OptionSet');
	}

	public function options()
	{
		return Bigcommerce_Api::getCollection('/products/' . $this->id . '/options', 'ProductOption');
	}

	public function create()
	{
		return Bigcommerce_Api::createProduct($this->getCreateFields());
	}

	public function update()
	{
		return Bigcommerce_Api::updateProduct($this->id, $this->getUpdateFields());
	}

	public function delete()
	{
		return Bigcommerce_Api::deleteProduct($this->id);
	}

}

/**
 * An image which is displayed on the storefront for a product.
 */
class Bigcommerce_Api_ProductImage extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'date_created',
		'product_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'date_created',
		'product_id',
	);

	public function create()
	{
		return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/images' , $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/images/' . $this->id , $this->getUpdateFields());
	}

}

/**
 * A stock keeping unit for a product.
 */
class Bigcommerce_Api_Sku extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'product_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'product_id',
	);

	public function options()
	{
		$options = Bigcommerce_Api::getCollection($this->fields->options->resource, 'SkuOption');

		foreach($options as $option) {
			$option->product_id = $this->product_id;
		}

		return $options;
	}

	public function create()
	{
		return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/skus' , $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/skus/' . $this->id , $this->getUpdateFields());
	}

}

/**
 * A relationship between a product SKU and an option.
 */
class Bigcommerce_Api_SkuOption extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'sku_id',
	);

	public $product_id;

	public function create()
	{
		return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/skus/' . $this->sku_id . '/options' , $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/skus/' . $this->sku_id . '/options/' .$this->id , $this->getUpdateFields());
	}

}

/**
 * Relationship between a product and an option applied from an option set.
 */
class Bigcommerce_Api_ProductOption extends Bigcommerce_Api_Resource
{

	public function option()
	{
		return self::getResource('/options/' . $this->option_id, 'Option');
	}

}

/**
 * An option.
 */
class Bigcommerce_Api_Option extends Bigcommerce_Api_Resource
{

	public function values()
	{
		return Bigcommerce_Api::getCollection($this->fields->values->resource, 'OptionValue');
	}

}

/**
 * Selectable value of an option.
 */
class Bigcommerce_Api_OptionValue extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'option_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'option_id',
	);

	public function option()
	{
		return self::getResource('/options/' . $this->option_id, 'Option');
	}

	public function create()
	{
		return Bigcommerce_Api::createResource('/options/' . $this->option_id . '/values', $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/options/' . $this->option_id . '/values/' . $this->id, $this->getUpdateFields());
	}

}

/**
 * A custom field on a product.
 */
class Bigcommerce_Api_CustomField extends Bigcommerce_Api_Resource
{

}

/**
 * A configurable field on a product.
 */
class Bigcommerce_Api_ConfigurableField extends Bigcommerce_Api_Resource
{

}

/**
 * A bulk discount rule.
 */
class Bigcommerce_Api_DiscountRule extends Bigcommerce_Api_Resource
{

}

/**
 * A product video.
 */
class Bigcommerce_Api_Video extends Bigcommerce_Api_Resource
{

}

/**
 * A product option rule.
 */
class Bigcommerce_Api_Rule extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'product_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'product_id',
	);

	public function conditions()
	{
		$conditions = Bigcommerce_Api::getCollection($this->fields->conditions->resource, 'RuleCondition');

		foreach($conditions as $condition) {
			$condition->product_id = $this->product_id;
		}

		return $conditions;
	}

	public function create()
	{
		return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/rules', $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/rules/' . $this->id, $this->getUpdateFields());
	}

}

/**
 * Conditions that will be applied to a product based on the rule.
 */
class Bigcommerce_Api_RuleCondition extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'rule_id',
	);

	public $product_id;

	public function create()
	{
		return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions' , $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions/' .$this->id , $this->getUpdateFields());
	}
}

class Bigcommerce_Api_Category extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'parent_category_list',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'parent_category_list',
	);

	public function create()
	{
		return Bigcommerce_Api::createCategory($this->getCreateFields());
	}

	public function update()
	{
		return Bigcommerce_Api::updateCategory($this->id, $this->getUpdateFields());
	}

}

class Bigcommerce_Api_Order extends Bigcommerce_Api_Resource
{

	public function shipments()
	{
		return Bigcommerce_Api::getCollection('/orders/'. $this->id . '/shipments', 'Shipment');
	}

	public function products()
	{
		return Bigcommerce_Api::getCollection($this->fields->products->resource, 'OrderProduct');
	}

	public function shipping_addresses()
	{
		return Bigcommerce_Api::getCollection($this->fields->shipping_addresses->resource, 'Address');
	}

	public function coupons()
	{
		return Bigcommerce_Api::getCollection($this->fields->coupons->resource, 'Coupon');
	}

	public function update()
	{
		$order = new stdClass;
		$order->status_id = $this->status_id;
		$order->is_deleted = $this->is_deleted;

		Bigcommerce_Api::updateResource('/orders/' . $this->id, $order);
	}

}

class Bigcommerce_Api_OrderProduct extends Bigcommerce_Api_Resource
{

}

class Bigcommerce_Api_Shipment extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'order_id',
		'date_created',
		'customer_id',
		'shipping_method',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'order_id',
		'date_created',
		'customer_id',
		'shipping_method',
		'items',
	);

	public function create()
	{
		return Bigcommerce_Api::createResource('/orders/' . $this->order_id . '/shipments', $this->getCreateFields());
	}

	public function update()
	{
		return Bigcommerce_Api::createResource('/orders/' . $this->order_id . '/shipments' . $this->id, $this->getCreateFields());
	}

}

class Bigcommerce_Api_Coupon extends Bigcommerce_Api_Resource
{

}

class Bigcommerce_Api_Customer extends Bigcommerce_Api_Resource
{

	public function addresses()
	{
		return Bigcommerce_Api::getCollection($this->fields->addresses->resource, 'Address');
	}

}

class Bigcommerce_Api_Address extends Bigcommerce_Api_Resource
{

}

class Bigcommerce_Api_OptionSet extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
	);

	public function options()
	{
		return Bigcommerce_Api::getCollection($this->fields->options->resource, 'OptionSetOption');
	}

	public function create()
	{
		return Bigcommerce_Api::createResource('/optionsets', $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/optionsets/' . $this->id, $this->getUpdateFields());
	}

}

class Bigcommerce_Api_OptionSetOption extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'option_set_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'option_set_id',
		'option_id',
	);

	public function option()
	{
		return Bigcommerce_Api::getCollection($this->fields->option->resource);
	}

	public function create()
	{
		return Bigcommerce_Api::createResource('/optionsets/options', $this->getCreateFields());
	}

	public function update()
	{
		Bigcommerce_Api::updateResource('/optionsets/options/' . $this->id, $this->getUpdateFields());
	}

}

class Bigcommerce_Api_Brand extends Bigcommerce_Api_Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
	);

	public function create()
	{
		return Bigcommerce_Api::createBrand($this->getCreateFields());
	}

	public function update()
	{
		return Bigcommerce_Api::updateBrand($this->id, $this->getUpdateFields());
	}

}

class Bigcommerce_Api_OrderStatus extends Bigcommerce_Api_Resource
{

}

/**
 * Represents a request to the API.
 */
class Bigcommerce_Api_RequestLog extends Bigcommerce_Api_Resource
{

}