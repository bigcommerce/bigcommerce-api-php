<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Relationship between a product and an option applied from an option set.
 */
class ProductOption extends Resource
{
    protected $fieldMap = array(
        'option' => 'option_id'
    );

    public function option()
    {
        return Client::getResource('/options/' . $this->fields->option_id, 'Option');
    }
}
