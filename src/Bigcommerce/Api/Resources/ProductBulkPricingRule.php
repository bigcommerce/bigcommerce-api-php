<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ProductBulkPricingRule extends Resource
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

    public function create($filter = array(), $version = "v3")
    {
        return Client::createProductBulkPricingRule($this->product_id, $this->getCreateFields(), $filter, $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductBulkPricingRule($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductBulkPricingRule($this->product_id, $this->id, $version);
    }
}
