<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A relationship between a product SKU and an option.
 */
class SkuOption extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'sku_id',
    );

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createResource('/products/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options', $this->getCreateFields());
    }

    /**
     * @return void
     */
    public function update()
    {
        Client::updateResource('/products/' . $this->fields->product_id . '/skus/' . $this->fields->sku_id . '/options/' . $this->id, $this->getUpdateFields());
    }
}
