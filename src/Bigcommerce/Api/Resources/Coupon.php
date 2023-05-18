<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Coupon extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
        'num_uses',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
        'num_uses',
    );

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createCoupon($this->getCreateFields());
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return Client::updateCoupon($this->id, $this->getUpdateFields());
    }
}
