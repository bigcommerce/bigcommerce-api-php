<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * An image which is displayed on the storefront for a product.
 */
class ProductImage extends Resource
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
        return Client::createProductImage($this->product_id, $this->getCreateFields());
    }

    public function update()
    {
        return Client::updateProductImage($this->product_id, $this->id, $this->getUpdateFields());
    }

    public function delete()
    {
        return Client::deleteProductImage($this->product_id, $this->id);
    }
}
