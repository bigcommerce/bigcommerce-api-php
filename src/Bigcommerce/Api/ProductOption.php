<?php

/**
 * Relationship between a product and an option applied from an option set.
 */
class Bigcommerce_Api_ProductOption extends Bigcommerce_Api_Resource
{

    public function option()
    {
        return self::getResource('/options/' . $this->option_id, 'Option');
    }

}
