<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Page extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public function create()
    {
        return Client::createPage($this->getCreateFields());
    }

    public function update()
    {
        return Client::updatePage($this->id, $this->getUpdateFields());
    }

    public function delete()
    {
        return Client::deletePage($this->id);
    }

    public function getAll()
    {
        return Client::getPages();
    }

    public function get()
    {
        return Client::getPage($this->id);
    }
}
