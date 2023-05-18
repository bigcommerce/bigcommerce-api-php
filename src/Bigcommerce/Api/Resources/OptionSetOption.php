<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class OptionSetOption extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
        'option_set_id',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'option_set_id',
        'option_id',
    );

    /**
     * @return mixed|Resource|\stdClass|string
     */
    public function option()
    {
        return Client::getResource($this->fields->option->resource, 'Option');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createResource('/optionsets/options', $this->getCreateFields());
    }

    /**
     * @return void
     */
    public function update()
    {
        Client::updateResource('/optionsets/options/' . $this->id, $this->getUpdateFields());
    }
}
