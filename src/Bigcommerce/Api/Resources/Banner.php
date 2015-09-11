<?php
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Banner extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function create()
    {
        return Client::createBanner($this->getCreateFields());
    }

    public function update()
    {
        return Client::updateBanner($this->id, $this->getUpdateFields());
    }

    public function delete()
    {
        return Client::deleteBanner($this->id);
    }
}