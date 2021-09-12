<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A custom field on a product.
 */
class ProductCustomField extends Resource
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
        "v2" => "/products",
        "v3" => "/catalog/products"
    );

    public function create($version = null)
    {
        return Client::createProductCustomField($this->fields->product_id, $this->getCreateFields(), $version);
    }

    public function update($version = null)
    {
        Client::updateProductCustomField($this->fields->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = null)
    {
        Client::deleteProductCustomField($this->fields->product_id, $this->id, $version);
    }
}
