<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Category extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
        'parent_category_list',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'parent_category_list',
    );

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createCategory($this->getCreateFields());
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return Client::updateCategory($this->id, $this->getUpdateFields());
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        return Client::deleteCategory($this->id);
    }
}
