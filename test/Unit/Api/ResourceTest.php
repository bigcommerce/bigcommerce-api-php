<?php
namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\Resource;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCreateFieldsReturnsAllFieldsNotMarkedIgnore()
    {
        $product = new Resource((object)array(
            'date_created' => 'abc123',
            'id' => 1
        ));

        $this->setProtectedProperty($product, 'ignoreOnCreate', array('date_created'));

        $this->assertEquals((object)array('id' => 1), $product->getCreateFields());
    }

    public function testGetUpdateFieldsReturnsAllFieldsNotMarkedIgnore()
    {
        $product = new Resource((object)array(
            'id' => 1,
            'custom_url' => '/whatever/'
        ));

        $this->setProtectedProperty($product, 'ignoreOnUpdate', array('id'));

        $this->assertEquals((object)array('custom_url' => '/whatever/'), $product->getUpdateFields());
    }

    public function testGetUpdateFieldsIgnoresNullFields()
    {
        $product = new Resource((object)array(
            'id' => 1,
            'custom_url' => '/whatever/',
            'ignored' => null,
        ));

        $this->setProtectedProperty($product, 'ignoreOnUpdate', array('id'));

        $this->assertEquals((object)array('custom_url' => '/whatever/'), $product->getUpdateFields());
    }

    public function testGetUpdateFieldsIgnoresEmptyDateFields()
    {
        $product = new Resource((object)array(
            'id' => 1,
            'date_created' => '2015',
            'date_updated' => '',
        ));

        $this->setProtectedProperty($product, 'ignoreOnUpdate', array('id'));

        $this->assertEquals((object)array('date_created' => '2015'), $product->getUpdateFields());
    }

    public function testGetUpdateFieldsIgnoresFieldsThatAreForbiddenToBeZeroWhenZero()
    {
        $product = new Resource((object)array(
            'id' => 1,
            'custom_url' => '/whatever/',
            'cannot_be_zero' => 0,
        ));

        $this->setProtectedProperty($product, 'ignoreOnUpdate', array('id'));
        $this->setProtectedProperty($product, 'ignoreIfZero', array('cannot_be_zero'));

        $this->assertEquals((object)array('custom_url' => '/whatever/'), $product->getUpdateFields());
    }

    public function testConstructorUnwrapsArrays()
    {
        $product = new Resource(array((object)array(
            'id' => 1
        )));

        $this->assertSame(1, $product->id);
    }

    public function testMagicGetReturnsAssignedValue()
    {
        $product = new Resource((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertSame('/whatever/', $product->custom_url);
    }

    public function testMagicSetUpdatesAssignedValue()
    {
        $product = new Resource((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertSame('/whatever/', $product->custom_url);
        $product->custom_url = '/other';
        $this->assertSame('/other', $product->custom_url);
    }

    public function testMagicIssetCorrectlyFindsFields()
    {
        $product = new Resource((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertTrue(isset($product->custom_url));
        $this->assertFalse(isset($product->id));
    }

    protected function setProtectedProperty($object, $property, $value)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
