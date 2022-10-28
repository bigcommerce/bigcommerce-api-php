<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A product video.
 */
class ProductVideo extends Resource
{
    protected $ignoreOnCreate = array(
        'product_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'length'
    );

    public $urls = array(
        "v2" => "/products",
        "v3" => "/catalog/products"
    );

    public function create($version = null)
    {
        return Client::createProductVideo($this->product_id, $this->getCreateFields(), $version);
    }

    public function update($version = null)
    {
        return Client::updateProductVideo($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = null)
    {
        return Client::deleteProductVideo($this->product_id, $this->id, $version);
    }
}
