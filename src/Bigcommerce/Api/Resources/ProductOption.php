<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Relationship between a product and an option applied from an option set.
 */
class ProductOption extends Resource
{
    protected $ignoreOnCreate = array(
        'id'
    );

    protected $ignoreOnUpdate = array(
        'id'
    );

    protected $fieldMap = array(
        'option' => 'option_id'
    );

    public $urls = array(
        "v2" => "/products",
        "v3" => "/catalog/products"
    );

    public function option()
    {
        return Client::getResource('/options/' . $this->fields->option_id, 'Option', "v2");
    }

    public function values($id = null, $filter = array(), $version = "v3")
    {
        if (is_null($id)) {
            return Client::getProductOptionValues($this->product_id, $this->id, $filter, $version);
        } else {
            return Client::getProductOptionValue($this->product_id, $this->id, $id, $filter, $version);
        }
    }

    public function create($version = "v3")
    {
        return Client::createProductOption($this->product_id, $this->getCreateFields(), $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductOption($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductOption($this->product_id, $this->id, $version);
    }
}
