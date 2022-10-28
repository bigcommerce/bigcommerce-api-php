<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ProductVariantMetafield extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'product_id'
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id'
    );

    public $urls = array(
        "v3" => "/catalog/products"
    );

    public function create($version = "v3")
    {
        return Client::createProductVariantMetafield($this->product_id, $this->resource_id, $this->getCreateFields(), $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductVariantMetafield($this->product_id, $this->resource_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductVariantMetafield($this->product_id, $this->resource_id, $this->id, $version);
    }

}