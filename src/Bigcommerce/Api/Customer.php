<?php

class Bigcommerce_Api_Customer extends Bigcommerce_Api_Resource
{

    public function addresses()
    {
        return Bigcommerce_Api::getCollection($this->fields->addresses->resource, 'Address');
    }

}
