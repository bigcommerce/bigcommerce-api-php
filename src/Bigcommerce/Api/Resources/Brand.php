<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Brand extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public $urls = array(
        "v2" => "/brands",
        "v3" => "/catalog/brands"
    );

    public function create($version = null)
    {
        return Client::createBrand($this->getCreateFields(), $version);
    }

    public function update($version = null)
    {
        return Client::updateBrand($this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = null)
    {
        return Client::deleteBrand($this->id, $version);
    }

    public function toJson()
    {
        return parent::toJson();
    }
}
