<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A stock keeping unit for a product.
 */
class Sku extends Resource
{
    protected $ignoreOnCreate = array(
        'product_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
    );

    public function options()
    {
        $options = array();

        if (!isset($this->fields->options)) {
            return $options;
        }

        foreach ($this->fields->options as $option) {
            $options[] = new SkuOption((object)$option);
        }

        return $options;
    }

    public function create()
    {
        return Client::createResource('/products/' . $this->product_id . '/skus', $this->getCreateFields());
    }

    public function update()
    {
        Client::updateResource('/products/' . $this->product_id . '/skus/' . $this->id, $this->getUpdateFields());
    }
}
