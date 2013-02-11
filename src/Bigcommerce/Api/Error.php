<?php

namespace Bigcommerce\Api;

/**
 * Base class for API exceptions. Used if failOnError is true.
 */
class Error extends \Exception
{

	public function __construct($message, $code)
	{
		if (is_array($message)) {
			$message = $message[0]->message;
		}

		parent::__construct($message, $code);
	}

}