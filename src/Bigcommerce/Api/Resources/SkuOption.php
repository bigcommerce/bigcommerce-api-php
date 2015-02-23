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

    public function create()
    {
        return Client::createResource('/products/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options', $this->getCreateFields());
    }

    public function update()
    {
        Client::updateResource('/products/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options/' . $this->id, $this->getUpdateFields());
    }
}
