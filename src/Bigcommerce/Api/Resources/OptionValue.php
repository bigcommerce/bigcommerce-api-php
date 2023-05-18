<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Selectable value of an option.
 */
class OptionValue extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
        'option_id',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'option_id',
    );

    /** @var string[] */
    protected $fieldMap = array(
        'option' => 'option_id'
    );

    /**
     * @return mixed|Resource|\stdClass|string
     */
    public function option()
    {
        return Client::getResource('/options/' . $this->fields->option_id, 'Option');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createResource('/options/' . $this->fields->option_id . '/values', $this->getCreateFields());
    }

    /**
     * @return void
     */
    public function update()
    {
        Client::updateResource('/options/' . $this->fields->option_id . '/values/' . $this->id, $this->getUpdateFields());
    }
}
