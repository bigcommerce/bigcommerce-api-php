<?php

class Bigcommerce_Api_OptionSet extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function options()
    {
        return Bigcommerce_Api::getCollection($this->fields->options->resource, 'OptionSetOption');
    }

    public function create()
    {
        return Bigcommerce_Api::createResource('/optionsets', $this->getCreateFields());
    }

    public function update()
    {
        Bigcommerce_Api::updateResource('/optionsets/' . $this->id, $this->getUpdateFields());
    }

}
