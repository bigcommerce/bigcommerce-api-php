<?php

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

        foreach ($options as $option) {
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
