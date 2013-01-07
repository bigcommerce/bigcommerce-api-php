<?php

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
