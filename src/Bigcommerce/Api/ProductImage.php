<?php

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
