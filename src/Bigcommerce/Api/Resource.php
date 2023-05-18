<?php

namespace Bigcommerce\Api;

class Resource
{
    /**
     * @var \stdClass
     */
    protected $fields;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string[]
     */
    protected $ignoreOnCreate = array();

    /**
     * @var string[]
     */
    protected $ignoreOnUpdate = array();

    /**
     * @var string[]
     */
    protected $ignoreIfZero = array();

    /**
     * @var array<string, string>
     */
    protected $fieldMap = array();

    /**
     * @param \stdClass[]|\stdClass|false $object
     */
    public function __construct($object = false)
    {
        if (is_array($object)) {
            $object = (isset($object[0])) ? $object[0] : false;
        }
        $this->fields = ($object) ? $object : new \stdClass;
        $this->id = ($object && isset($object->id)) ? $object->id : 0;
    }

    /**
     * @param string $field
     * @return null
     */
    public function __get($field)
    {
        // first, find the field we should actually be examining
        $fieldName = $this->fieldMap[$field] ?? $field;
        // then, if a method exists for the specified field and the field we should actually be examining
        // has a value, call the method instead
        if (method_exists($this, $field) && isset($this->fields->$fieldName)) {
            return $this->$field();
        }
        // otherwise, just return the field directly (or null)
        return (isset($this->fields->$field)) ? $this->fields->$field : null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function __set($field, $value)
    {
        $this->fields->$field = $value;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function __isset($field)
    {
        return (isset($this->fields->$field));
    }

    /**
     * @return \stdClass
     */
    public function getCreateFields()
    {
        $resource = clone $this->fields;

        foreach ($this->ignoreOnCreate as $field) {
            unset($resource->$field);
        }

        return $resource;
    }

    /**
     * @return \stdClass
     */
    public function getUpdateFields()
    {
        $resource = clone $this->fields;

        foreach ($this->ignoreOnUpdate as $field) {
            unset($resource->$field);
        }

        foreach ($resource as $field => $value) {
            if ($this->isIgnoredField($field, $value)) {
                unset($resource->$field);
            }
        }

        return $resource;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    private function isIgnoredField($field, $value)
    {
        if ($value === null) {
            return true;
        }

        if ($value === "" && str_contains($field, "date")) {
            return true;
        }

        if ($value === 0 && in_array($field, $this->ignoreIfZero, true)) {
            return true;
        }

        return false;
    }
}
