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

    /**
     * @var array
     */
    protected $fieldMap = array();

    public function __construct($object = false)
    {
        if (is_array($object)) {
            $object = (isset($object[0])) ? $object[0] : false;
        }
        $this->fields = ($object) ? $object : new \stdClass;
        $this->id = ($object && isset($object->id)) ? $object->id : 0;
    }

    public function __get($field)
    {
        // first, find the field we should actually be examining
        $fieldName = isset($this->fieldMap[$field]) ? $this->fieldMap[$field] : $field;
        // then, if a method exists for the specified field and the field we should actually be examining
        // has a value, call the method instead
        if (method_exists($this, $field) && isset($this->fields->$fieldName)) {
            return $this->$field();
        }
        // otherwise, just return the field directly (or null)
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
        $resource = clone $this->fields;

        foreach ($this->ignoreOnCreate as $field) {
            unset($resource->$field);
        }

        return $resource;
    }

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

    private function isIgnoredField($field, $value)
    {
        if ($value === null) {
            return true;
        }

        if ($value === "" && strpos($field, "date") !== false) {
            return true;
        }

        if ($value === 0 && in_array($field, $this->ignoreIfZero, true)) {
            return true;
        }

        return false;
    }
}
