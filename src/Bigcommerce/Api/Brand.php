<?php

class Bigcommerce_Api_Brand extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function create()
    {
        return Bigcommerce_Api::createBrand($this->getCreateFields());
    }

    public function update()
    {
        return Bigcommerce_Api::updateBrand($this->id, $this->getUpdateFields());
    }

}
