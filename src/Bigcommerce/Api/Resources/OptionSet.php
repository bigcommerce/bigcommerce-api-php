<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class OptionSet extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
    );

    /**
     * @return array|mixed|string
     */
    public function options()
    {
        return Client::getCollection($this->fields->options->resource, 'OptionSetOption');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createResource('/optionsets', $this->getCreateFields());
    }

    /**
     * @return void
     */
    public function update()
    {
        Client::updateResource('/optionsets/' . $this->id, $this->getUpdateFields());
    }
}
