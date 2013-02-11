<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Selectable value of an option.
 */
class OptionValue extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'option_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'option_id',
	);

	public function option()
	{
		return self::getResource('/options/' . $this->option_id, 'Option');
	}

	public function create()
	{
		return Client::createResource('/options/' . $this->option_id . '/values', $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/options/' . $this->option_id . '/values/' . $this->id, $this->getUpdateFields());
	}

}