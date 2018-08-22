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

    public function testGetCustomerLoginTokenReturnsValidLoginToken()
    {
        Client::configureOAuth(array(
            'client_id' => '123',
            'auth_token' => 'def',
            'store_hash' => 'abc',
            'client_secret' => 'zyx'
        ));
        $expectedPayload = array(
            'iss' => '123',
            'operation' => 'customer_login',
            'store_hash' => 'abc',
            'customer_id' => 1,
        );
        $token = Client::getCustomerLoginToken(1);
        $actualPayload = (array)\Firebase\JWT\JWT::decode($token, 'zyx', array('HS256'));
        $this->assertArraySubset($expectedPayload, $actualPayload);
    }

    public function testGetCustomerLoginTokenThrowsIfNoClientSecret()
    {
        Client::configureOAuth(array(
            'client_id' => '123',
            'auth_token' => 'def',
            'store_hash' => 'abc'
        ));
        $this->setExpectedException('\Exception', 'Cannot sign customer login tokens without a client secret');
        Client::getCustomerLoginToken(1);
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

        $this->assertEquals($now->format('U'), Client::getTime()->format('U'));
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
            array('pages', 'getPages', 'Page'),
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
        if (in_array($path, array('order_statuses', 'requestlogs', 'pages'))) {
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
            array('pages',        '%sPage',       'Page'),
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
            ->with($this->basePath . '/products/1/skus', (object)array());

        Client::createSku(1, array());
    }

    public function testUpdatingASkuPutsToTheSkuResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/skus/1', (object)array());

        Client::updateSku(1, array());
    }

    public function testGettingProductGoogleProductSearch()
    {
      $this->connection->expects($this->once())
          ->method('get')
          ->with($this->basePath . '/products/1/googleproductsearch')
          ->will($this->returnValue((object)array()));

      $resource = Client::getGoogleProductSearch(1);
      $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\ProductGoogleProductSearch', $resource);
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

    public function testCreatingGiftCertificatePostsToTheGiftCertificateResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/gift_certificates', (object)array());

        Client::createGiftCertificate(array());
    }

    public function testGettingSpecifiedGiftCertificateReturnsTheSpecifiedGiftCertificate()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/gift_certificates/1', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getGiftCertificate(1);
    }

    public function testGettingGiftCertificatesReturnsTheGiftCertificates()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/gift_certificates', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getGiftCertificates();
    }

    public function testUpdatingSpecifiedGiftCertificatePutsToTheSpecifiedGiftCertificateResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/gift_certificates/1', (object)array());

        Client::updateGiftCertificate(1, array());
    }

    public function testDeletingSpecifiedGiftCertificateDeletesToTheSpecifiedGiftCertificateResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/gift_certificates/1');

        Client::deleteGiftCertificate(1);
    }

    public function testDeletingAllGiftCertificatesDeletesToTheAllGiftCertificatesResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/gift_certificates');

        Client::deleteAllGiftCertificates();
    }
    
    
    public function testGettingWebhooksReturnsAllWebhooks()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/hooks', false)
            ->will($this->returnValue(array(new \Bigcommerce\Api\Resource(),new \Bigcommerce\Api\Resource())));
        $collection = Client::listWebhooks();
        $this->assertInternalType('array', $collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resource', $resource);
        }
    }
    
    public function testGettingSpecifiedWebhookReturnsTheSpecifiedWebhook()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/hooks/1', false)
            ->will($this->returnValue(new \Bigcommerce\Api\Resource()));
        $resource = Client::getWebhook(1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resource', $resource);
    }
    
    public function testCreatingWebhookPostsToTheSpecifiedResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/hooks', (object)array());
        Client::createWebhook(array());
    }
    public function testUpdatingWebhookPutsToTheSpecifiedResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/hooks/1', (object)array());
        Client::updateWebhook(1, array());
    }
    
    public function testDeleteWebhookDeletesToTheSpecifiedResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/hooks/1');
        Client::deleteWebhook(1);
    }

    public function testCreatingProductReviewPostsToTheProductReviewResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/reviews', (object)array());

        Client::createProductReview(1, array());
    }

    public function testCreatingProductBulkPricingRulesPostsToTheProductBulkPricingRulesResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/discount_rules', (object)array());

        Client::createProductBulkPricingRules(1, array());
    }

    public function testCreatingMarketingBannerPostsToTheMarketingBannerResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/banners', (object)array());

        Client::createMarketingBanner(array());
    }

    public function testGettingMarketingBannersReturnsTheMarketingBanners()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/banners', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getMarketingBanners();
    }

    public function testDeletingAllMarketingBannerDeletesToTheAllMarketingBannerResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/banners');

        Client::deleteAllMarketingBanners();
    }

    public function testDeletingMarketingBannerDeletesToTheMarketingBannerResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/banners/1');

        Client::deleteMarketingBanner(1);
    }

    public function testUpdatingMarketingBannerPutsToTheMarketingBannerResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/banners/1', (object)array());

        Client::updateMarketingBanner(1, array());
    }

    public function testCreatingCustomerAddressPostsToTheCustomerAddressResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/customers/1/addresses', (object)array());

        Client::createCustomerAddress(1, array());
    }

    public function testCreatingProductRulePostsToTheProductRuleResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/rules', (object)array());

        Client::createProductRule(1, array());
    }

    public function testCreatingCustomerGroupPostsToTheCustomerGroupResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/customer_groups', (object)array());

        Client::createCustomerGroup(array());
    }

    public function testGettingASpecifiedCustomerGroupsReturnsTheCustomerGroups()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/customer_groups', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getCustomerGroups();
    }

    public function testDeletingCustomerGroupDeletesToTheCustomerGroupResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/customer_groups/1');

        Client::deleteCustomerGroup(1);
    }

    public function testDeletingAllCustomersDeletesToTheCustomersResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/customers');

        Client::deleteAllCustomers();
    }

    public function testDeletingAllProductOptionsDeletesToTheProductOptionsResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/options');

        Client::deleteAllOptions();
    }

    public function testGettingASpecifiedProductOptionsReturnsThoseProductOptions()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/options', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getProductOptions(1);
    }

    public function testGettingASpecifiedProductOptionReturnsThatProductOption()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/options/1', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getProductOption(1, 1);
    }

    public function testGettingASpecifiedProductRuleReturnsThatProductRule()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/rules/1', false)
            ->will($this->returnValue(array(array(), array())));

        Client::getProductRule(1, 1);
    }

    public function testCreatingOptionValuePostsToTheOptionValueResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/options/1/values', (object)array());

        Client::createOptionValue(1, array());
    }

    public function testDeletingAllOptionSetsDeletesToTheOptionSetsResource()
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/optionsets');

        Client::deleteAllOptionSets();
    }

    public function testUpdatingOptionValuePutsToTheOptionValueResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/options/1/values/1', (object)array());

        Client::updateOptionValue(1, 1, array());
    }
}
