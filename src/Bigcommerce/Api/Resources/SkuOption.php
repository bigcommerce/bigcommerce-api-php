<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A relationship between a product SKU and an option.
 */
class SkuOption extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'sku_id',
    );

    public $urls = array(
        "v2" => "/products"
    );

    public function create()
    {
        return Client::createResource('/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options', $this->getCreateFields(), 'SkuOption', 'v2');
    }

    public function update()
    {
        Client::updateResource('/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options/' . $this->id, $this->getUpdateFields(), 'SkuOption', 'v2');
    }
}
