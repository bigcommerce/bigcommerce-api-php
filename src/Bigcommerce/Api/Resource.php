<?php

namespace Bigcommerce\Api;

class Resource
{
	/**
	 *
	 * @var stdclass
	 */
	protected $fields;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $ignoreOnCreate = array();

	/**
	 * @var array
	 */
	protected $ignoreOnUpdate = array();

	/**
	 * @var array
	 */
	protected $ignoreIfZero = array();

	public function __construct($object=false)
	{
		if (is_array($object)) {
			$object = (isset($object[0])) ? $object[0] : false;
		}
		$this->fields = ($object) ? $object : new \stdClass;
		$this->id = ($object && isset($object->id)) ? $object->id : 0;
	}

	public function __get($field)
	{
		if (method_exists($this, $field) && isset($this->fields->$field)) {
			return $this->$field();
		}
		return (isset($this->fields->$field)) ? $this->fields->$field : null;
	}

	public function __set($field, $value)
	{
		$this->fields->$field = $value;
	}

	public function __isset($field)
	{
		return (isset($this->fields->$field));
	}

	public function getCreateFields()
	{
		$resource = $this->fields;

		foreach($this->ignoreOnCreate as $field) {
			if (isset($resource->$field)) unset($resource->$field);
		}

		return $resource;
	}

	public function getUpdateFields()
	{
		$resource = $this->fields;

		foreach($this->ignoreOnUpdate as $field) {
			if (isset($resource->$field)) unset($resource->$field);
		}

		foreach($resource as $field => $value) {
			if ($this->isIgnoredField($field, $value)) unset($resource->$field);
		}

		return $resource;
	}

	private function isIgnoredField($field, $value)
	{
		if ($value === null) return true;

		if ((strpos($field, "date") !== FALSE) && $value === "") return true;

		if (in_array($field, $this->ignoreIfZero) && $value === 0) return true;

		return false;
	}

}