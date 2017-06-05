<?php
namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class GiftCertificate extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'order_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'order_id',
    );

    public function create()
    {
        return Client::createGiftCertificate($this->getCreateFields());
    }

    public function update()
    {
        return Client::updateGiftCertificate($this->id, $this->getUpdateFields());
    }

    public function delete()
    {
        return Client::deleteGiftCertificate($this->id);
    }
}
