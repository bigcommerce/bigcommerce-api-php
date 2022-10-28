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

    public $urls = array(
        "v2" => "/products",
        "v3" => "/catalog/products"
    );

    public function create($version = null)
    {
        return Client::createProductImage($this->product_id, $this->getCreateFields(), $version);
    }

    public function update($version = null)
    {
        return Client::updateProductImage($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = null)
    {
        return Client::deleteProductImage($this->product_id, $this->id, $version);
    }
}
