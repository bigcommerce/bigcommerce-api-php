<?php

class Bigcommerce_Api_Category extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
        'parent_category_list',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'parent_category_list',
    );

    public function create()
    {
        return Bigcommerce_Api::createCategory($this->getCreateFields());
    }

    public function update()
    {
        return Bigcommerce_Api::updateCategory($this->id, $this->getUpdateFields());
    }

}
