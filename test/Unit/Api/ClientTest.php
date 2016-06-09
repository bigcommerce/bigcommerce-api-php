<?php

namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Connection;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;
    private $basePath = '';

    public function setUp()
    {
        $methods = array(
            'useXml',
            'failOnError',
            'authenticate',
            'setTimeout',
            'useProxy',
            'verifyPeer',
            'addHeader',
            'getLastError',
            'get',
            'post',
            'head',
            'put',
            'delete',
            'getStatus',
            'getStatusMessage',
            'getBody',
            'getHeader',
            'getHeaders',
            '__destruct'
        );
        $this->basePath = $this->getStaticAttribute('Bigcommerce\\Api\\Client', 'api_path');
        $this->connection = $this->getMockBuilder('Bigcommerce\\Api\\Connection')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
        Client::setConnection($this->connection);
    }

    public function tearDown()
    {
        Client::configure(array('username' => '', 'api_key' => '', 'store_url' => ''));
        unset($this->connection);
    }

    public function testConfigureRequiresStoreUrl()
    {
        $this->setExpectedException('\\Exception', "'store_url' must be provided");
        Client::configure(array('username' => 'whatever', 'api_key' => 'whatever'));
    }

    public function testConfigureRequiresUsername()
    {
        $this->setExpectedException('\\Exception', "'username' must be provided");
        Client::configure(array('store_url' => 'whatever', 'api_key' => 'whatever'));
    }

    public function testConfigureRequiresApiKey()
    {
        $this->setExpectedException('\\Exception', "'api_key' must be provided");
        Client::configure(array('username' => 'whatever', 'store_url' => 'whatever'));
    }

    public function testFailOnErrorPassesThroughToConnection()
    {
        $this->connection->expects($this->exactly(2))
            ->method('failOnError')
            ->withConsecutive(
                array(true),
                array(false)
            );
        Client::failOnError(true);
        Client::failOnError(false);
    }

    public function testUseXmlPassesThroughToConnection()
    {
        $this->connection->expects($this->once())
            ->method('useXml');

        Client::useXml();
    }

    public function testVerifyPeerPassesThroughToConnection()
    {
        $this->connection->expects($this->exactly(2))
            ->method('verifyPeer')
            ->withConsecutive(
                array(true),
                array(false)
            );
        Client::verifyPeer(true);
        Client::verifyPeer(false);
    }

    public function testUseProxyPassesThroughToConnection()
    {
        $this->connection->expects($this->once())
            ->method('useProxy')
            ->with('127.0.0.1', 6559);

        Client::useProxy('127.0.0.1', 6559);
    }

    public function testGetLastErrorGetsErrorFromConnection()
    {
        $this->connection->expects($this->once())
            ->method('getLastError')
            ->will($this->returnValue(5));

        $this->assertSame(5, Client::getLastError());
    }

    public function testGetResourceReturnsSpecifiedType()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue(array(array())));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $resource = Client::getResource('/whatever');
        $this->assertInstanceOf('Bigcommerce\\Api\\Resource', $resource);
    }

    public function testGetCountReturnsSpecifiedCount()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue((object)array('count' => 5)));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $count = Client::getCount('/whatever');
        $this->assertSame(5, $count);
    }

    public function testGetCollectionReturnsCollectionOfSpecifiedTypes()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue(array(array(), array())));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $resources = Client::getCollection('/whatever');
        $this->assertInternalType('array', $resources);
        foreach ($resources as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resource', $resource);
        }
    }

    public function testCreateResourcePostsToTheRightPlace()
    {
        $new = array(rand() => rand());
        $this->connection->expects($this->once())
            ->method('post')
            ->with('http://storeurl' . $this->basePath . '/whatever', (object)$new)
            ->will($this->returnValue($new));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $result = Client::createResource('/whatever', $new);
        $this->assertSame($new, $result);
    }

    public function testUpdateResourcePutsToTheRightPlace()
    {
        $update = array(rand() => rand());
        $this->connection->expects($this->once())
            ->method('put')
            ->with('http://storeurl' . $this->basePath . '/whatever', (object)$update)
            ->will($this->returnValue($update));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $result = Client::updateResource('/whatever', $update);
        $this->assertSame($update, $result);
    }

    public function testDeleteResourceDeletesToTheRightPlace()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with('http://storeurl' . $this->basePath . '/whatever')
            ->will($this->returnValue("Successfully deleted"));

        Client::configure(array('store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever'));
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $result = Client::deleteResource('/whatever');
        $this->assertSame("Successfully deleted", $result);
    }

    public function testGetTimeReturnsTheExpectedTime()
    {
        $now = new \DateTime();
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/time', false)
            ->will($this->returnValue((object)array('time' => $now->format('U'))));

        $this->assertEquals($now, Client::getTime());
    }

    public function testGetStoreReturnsTheResultBodyDirectly()
    {
        $body = array(rand() => rand());
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/store')
            ->will($this->returnValue($body));

        $this->assertSame($body, Client::getStore());
    }

    public function testGetRequestsRemainingReturnsTheValueFromTheLastHeader()
    {
        $this->connection->expects($this->once())
            ->method('getHeader')
            ->will($this->returnValue('12345'));

        $this->assertSame(12345, Client::getRequestsRemaining());
    }

    public function testGetRequestsRemainingRequestsTimeWhenNoValueAvailable()
    {
        $this->connection->expects($this->exactly(2))
            ->method('getHeader')
            ->will($this->onConsecutiveCalls(false, '12345'));

        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/time', false)
            ->will($this->returnValue((object)array('time' => time())));

        $this->assertSame(12345, Client::getRequestsRemaining());
    }

    public function collections()
    {
        return array(
            //      path           function             classname
            array('products', 'getProducts', 'Product'),
            array('brands', 'getBrands', 'Brand'),
            array('orders', 'getOrders', 'Order'),
            array('customers', 'getCustomers', 'Customer'),
            array('coupons', 'getCoupons', 'Coupon'),
            array('order_statuses', 'getOrderStatuses', 'OrderStatus'),
            array('categories', 'getCategories', 'Category'),
            array('options', 'getOptions', 'Option'),
            array('optionsets', 'getOptionSets', 'OptionSet'),
            array('products/skus', 'getSkus', 'Sku'),
            array('requestlogs', 'getRequestLogs', 'RequestLog'),
            array('currencies', 'getCurrencies', 'Currency'),
        );
    }

    /**
     * @dataProvider collections
     */
    public function testGettingASpecificResourceReturnsACollectionOfThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path, false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::$fnName();
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $class, $resource);
        }
    }

    /**
     * @dataProvider collections
     */
    public function testGettingTheCountOfACollectionReturnsThatCollectionsCount($path, $fnName, $class)
    {
        if (in_array($path, array('order_statuses', 'requestlogs'))) {
            //$this->markTestSkipped(sprintf('The API does not currently support getting the count of %s', $path));
            return;
        }

        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path . '/count', false)
            ->will($this->returnValue((object)array('count' => 7)));

        $fnName .= 'Count';
        $count = Client::$fnName();
        $this->assertSame(7, $count);
    }

    public function resources()
    {
        return array(
            //    path            function        classname
            array('products',     '%sProduct',    'Product'),
            array('brands',       '%sBrand',      'Brand'),
            array('orders',       '%sOrder',      'Order'),
            array('customers',    '%sCustomer',   'Customer'),
            array('categories',   '%sCategory',   'Category'),
            array('options',      '%sOption',     'Option'),
            array('optionsets',   '%sOptionSet',  'OptionSet'),
            array('coupons',      '%sCoupon',     'Coupon'),
            array('currencies',   '%sCurrency',   'Currency'),
        );
    }

    /**
     * @dataProvider resources
     */
    public function testGettingASpecificResourceReturnsThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path . '/1', false)
            ->will($this->returnValue(array(array(), array())));

        $fnName = sprintf($fnName, 'get');
        $resource = Client::$fnName(1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $class, $resource);
    }

    /**
     * @dataProvider resources
     */
    public function testCreatingASpecificResourcePostsToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/' . $path, (object)array());

        $fnName = sprintf($fnName, 'create');
        Client::$fnName(array());
    }

    /**
     * @dataProvider resources
     */
    public function testDeletingASpecificResourceDeletesToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/' . $path . '/1');

        $fnName = sprintf($fnName, 'delete');
        Client::$fnName(1);
    }

    /**
     * @dataProvider resources
     */
    public function testUpdatingASpecificResourcePutsToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/' . $path . '/1');

        $fnName = sprintf($fnName, 'update');
        Client::$fnName(1, array());
    }

    // hand-test the Sku resource because of the wonky urls
    public function testCreatingASkuPostsToTheSkuResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/product/skus', (object)array());

        Client::createSku(array());
    }

    public function testUpdatingASkuPutsToTheSkuResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/product/skus/1', (object)array());

        Client::updateSku(1, array());
    }

    public function testGettingProductImagesReturnsCollectionOfProductImages()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/images/', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getProductImages(1);
        $this->assertInternalType('array', $collection);
        $this->assertContainsOnlyInstancesOf('Bigcommerce\\Api\\Resources\\ProductImage', $collection);
    }

    public function testGettingProductCustomFieldsReturnsCollectionOfProductCustomFields()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/customfields/', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getProductCustomFields(1);
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\ProductCustomField', $resource);
        }
    }

    public function testGettingASpecifiedProductImageReturnsThatProductImage()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/images/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getProductImage(1, 1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\ProductImage', $resource);
    }

    public function testGettingASpecifiedProductCustomFieldReturnsThatProductCustomField()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/customfields/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getProductCustomField(1, 1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\ProductCustomField', $resource);
    }

    public function testGettingASpecifiedOptionValueReturnsThatOptionValue()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/1/values/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getOptionValue(1, 1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\OptionValue', $resource);
    }

    public function testGettingCustomerAddressesReturnsCollectionOfCustomerAddresses()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/customers/1/addresses', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getCustomerAddresses(1);
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Address', $resource);
        }
    }

    public function testGettingOptionValuesReturnsCollectionOfOptionValues()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/values', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getOptionValues();
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\OptionValue', $resource);
        }
    }

    public function testCreatingAnOptionSetPostsToTheOptionSetsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/optionsets', (object)array());

        Client::createOptionSet(array());
    }

    public function testCreatingAnOptionPostsToTheOptionResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/options', (object)array());

        Client::createOption(array());
    }

    public function testCreatingAnOptionSetOptionPostsToTheOptionSetsOptionsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/optionsets/1/options', (object)array());

        Client::createOptionSetOption(array(), 1);
    }

    public function testCreatingAProductImagePostsToTheProductImageResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/images', (object)array());

        Client::createProductImage(1, array());
    }

    public function testCreatingAProductCustomFieldPostsToTheProductCustomFieldResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/customfields', (object)array());

        Client::createProductCustomField(1, array());
    }

    public function testUpdatingAProductImagePutsToTheProductImageResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/images/1', (object)array());

        Client::updateProductImage(1, 1, array());
    }

    public function testUpdatingAProductCustomFieldPutsToTheProductCustomFieldResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/customfields/1', (object)array());

        Client::updateProductCustomField(1, 1, array());
    }

    public function testDeletingAProductImageDeletesToTheProductImageResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1/images/1');

        Client::deleteProductImage(1, 1);
    }

    public function testDeletingAProductCustomFieldDeletesToTheProductCustomFieldResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1/customfields/1');

        Client::deleteProductCustomField(1, 1);
    }

    public function testDeletingACustomerDeletesToTheCustomerResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/customers');

        Client::deleteCustomers();
    }

    public function testDeletingAllCouponsDeletesToTheCouponResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/coupons');

        Client::deleteAllCoupons();
    }

    public function testDeletingACouponDeletesToTheCouponResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/coupons/1');

        Client::deleteCoupon(1);
    }

    public function testGettingASpecifiedCouponReturnsThatCoupon()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/coupons/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getCoupon(1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Coupon', $resource);
    }

    public function testGettingASpecifiedOrderStatusReturnsThatOrderStatus()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/order_statuses/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getOrderStatus(1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\OrderStatus', $resource);
    }

    public function testDeletingAllOrdersDeletesToTheOrderResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/orders');

        Client::deleteAllOrders();
    }

    public function testDeletingAllBrandsDeletesToTheBrandsResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/brands');

        Client::deleteAllBrands();
    }

    public function testDeletingAllCategoriesDeletesToTheCategoriesResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/categories');

        Client::deleteAllCategories();
    }

    public function testDeletingAllProductsDeletesToTheProductsResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products');

        Client::deleteAllProducts();
    }

    public function testGettingOrderProductsCountCountsToTheOrderProductsResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/products/count', false)
            ->will($this->returnValue((object)array('count' => 7)));

        $count = Client::getOrderProductsCount(1);
        $this->assertSame(7, $count);
    }

    public function testGettingOrderShipmentReturnsTheOrderShipmentResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getShipment(1, 1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Shipment', $resource);
    }

    public function testGettingOrderProductsReturnsTheOrderProductsCollection()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/products', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getOrderProducts(1);
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\OrderProduct', $resource);
        }
    }

    public function testGettingOrderShipmentsReturnsTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getShipments(1);
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Shipment', $resource);
        }
    }

    public function testCreatingOrderShipmentsPostsToTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/orders/1/shipments', (object)array());

        Client::createShipment(1, array());
    }

    public function testUpdatingOrderShipmentsPutsToTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/orders/1/shipments/1', (object)array());

        Client::updateShipment(1, 1, array());
    }

    public function testDeletingAllOrderShipmentsDeletesToTheOrderShipmentResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/orders/1/shipments');

        Client::deleteAllShipmentsForOrder(1);
    }

    public function testDeletingAnOrderShipmentDeletesToTheOrderShipmentResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/orders/1/shipments/1');

        Client::deleteShipment(1, 1);
    }

    public function testGettingOrderShippingAddressReturnsTheAddressResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipping_addresses/1', false)
            ->will($this->returnValue(array(array(), array())));

        $resource = Client::getOrderShippingAddress(1, 1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Address', $resource);
    }

    public function testGettingOrderShippingAddressesReturnsTheAddressResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipping_addresses', false)
            ->will($this->returnValue(array(array(), array())));

        $collection = Client::getOrderShippingAddresses(1);
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Address', $resource);
        }
    }
}
