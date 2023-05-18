<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Brand extends Resource
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
     * @return mixed
     */
    public function create()
    {
        return Client::createBrand($this->getCreateFields());
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return Client::updateBrand($this->id, $this->getUpdateFields());
    }
}
