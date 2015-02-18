<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Product;

/*
Because the Resource class is abstract, test it through one of its' children.
We've picked Product here because it exhibits all behaviors of the Resource object.
*/
class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCreateFieldsReturnsAllFieldsNotMarkedIgnore()
    {
        $product = new Product((object)array(
            'date_created' => 'abc123',
            'id' => 1
        ));

        $this->assertEquals((object)array('id' => 1), $product->getCreateFields());
    }

    public function testGetUpdateFieldsReturnsAllFieldsNotMarkedIgnore()
    {
        $product = new Product((object)array(
            'id' => 1,
            'custom_url' => '/whatever/'
        ));

        $this->assertEquals((object)array('custom_url' => '/whatever/'), $product->getUpdateFields());
    }

    public function testConstructorUnwrapsArrays()
    {
        $product = new Product(array((object)array(
            'id' => 1
        )));

        $this->assertSame(1, $product->id);
    }

    public function testMagicGetReturnsAssignedValue()
    {
        $product = new Product((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertSame('/whatever/', $product->custom_url);
    }

    public function testMagicSetUpdatesAssignedValue()
    {
        $product = new Product((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertSame('/whatever/', $product->custom_url);
        $product->custom_url = '/other';
        $this->assertSame('/other', $product->custom_url);
    }

    public function testMagicIssetCorrectlyFindsFields()
    {
        $product = new Product((object)array(
            'custom_url' => '/whatever/'
        ));

        $this->assertTrue(isset($product->custom_url));
        $this->assertFalse(isset($product->id));
    }
}
