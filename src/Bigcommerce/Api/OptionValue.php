<?php

/**
 * Selectable value of an option.
 */
class Bigcommerce_Api_OptionValue extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
        'option_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'option_id',
    );

    public function option()
    {
        return self::getResource('/options/' . $this->option_id, 'Option');
    }

    public function create()
    {
        return Bigcommerce_Api::createResource('/options/' . $this->option_id . '/values', $this->getCreateFields());
    }

    public function update()
    {
        Bigcommerce_Api::updateResource('/options/' . $this->option_id . '/values/' . $this->id, $this->getUpdateFields());
    }

}
