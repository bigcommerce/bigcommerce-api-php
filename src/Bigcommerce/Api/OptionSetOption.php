<?php

class Bigcommerce_Api_OptionSetOption extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
        'option_set_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'option_set_id',
        'option_id',
    );

    public function option()
    {
        return Bigcommerce_Api::getCollection($this->fields->option->resource);
    }

    public function create()
    {
        return Bigcommerce_Api::createResource('/optionsets/options', $this->getCreateFields());
    }

    public function update()
    {
        Bigcommerce_Api::updateResource('/optionsets/options/' . $this->id, $this->getUpdateFields());
    }

}
