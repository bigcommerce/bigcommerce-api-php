<?php

/**
 * An option.
 */
class Bigcommerce_Api_Option extends Bigcommerce_Api_Resource
{

    public function values()
    {
        return Bigcommerce_Api::getCollection($this->fields->values->resource, 'OptionValue');
    }

}
