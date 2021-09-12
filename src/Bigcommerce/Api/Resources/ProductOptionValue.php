<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ProductOptionValue extends Resource
{

    protected $ignoreOnCreate = array(
        'id',
        'product_id',
        'option_id'
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'option_id'
    );

    public $urls = array(
        "v3" => "/catalog/products"
    );

    public function create($version = "v3")
    {
        return Client::createProductOptionValue($this->product_id, $this->option_id, $this->getCreateFields(), $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductOptionValue($this->product_id, $this->option_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductOptionValue($this->product_id, $this->option_id, $this->id, $version);
    }
}
