<?php

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
