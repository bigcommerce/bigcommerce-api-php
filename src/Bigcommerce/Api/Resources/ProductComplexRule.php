<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ProductComplexRule extends Resource
{
    protected $ignoreOnCreate = array(
        'id'
    );

    protected $ignoreOnUpdate = array(
        'id'
    );

    public $urls = array(
        "v3" => "/catalog/products"
    );

    public function create($version = "v3")
    {
        return Client::createProductComplexRule($this->product_id, $this->getCreateFields(), $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductComplexRule($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductComplexRule($this->product_id, $this->id, $version);
    }
}