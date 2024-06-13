<?php

namespace Bigcommerce\Test\Unit\Api;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Connection;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Connection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connection;
    private $basePath = '';

    public function setUp(): void
    {
        $methods = ['useXml', 'failOnError', 'authenticate', 'setTimeout', 'useProxy', 'verifyPeer', 'addHeader', 'getLastError', 'get', 'post', 'head', 'put', 'delete', 'getStatus', 'getStatusMessage', 'getBody', 'getHeader', 'getHeaders', '__destruct'];
        $this->basePath = Client::$api_path;
        $this->connection = $this->getMockBuilder(\Bigcommerce\Api\Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        Client::setConnection($this->connection);
    }

    public function tearDown(): void
    {
        Client::configure(['username' => '', 'api_key' => '', 'store_url' => '']);
        unset($this->connection);
    }

    public function testConfigureRequiresStoreUrl()
    {
        $this->expectException('\\Exception');
        $this->expectExceptionMessage("'store_url' must be provided");
        Client::configure(['username' => 'whatever', 'api_key' => 'whatever']);
    }

    public function testConfigureRequiresUsername()
    {
        $this->expectException('\\Exception');
        $this->expectExceptionMessage("'username' must be provided");
        Client::configure(['store_url' => 'whatever', 'api_key' => 'whatever']);
    }

    public function testConfigureRequiresApiKey()
    {
        $this->expectException('\\Exception');
        $this->expectExceptionMessage("'api_key' must be provided");
        Client::configure(['username' => 'whatever', 'store_url' => 'whatever']);
    }

    public function testFailOnErrorPassesThroughToConnection()
    {
        $matcher = $this->exactly(2);
        $this->connection->expects($matcher)
            ->method('failOnError')->willReturnCallback(fn () => match ($matcher->numberOfInvocations()) {
                1 => [true],
                2 => [false],
            });
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
        $matcher = $this->exactly(2);
        $this->connection->expects($matcher)
            ->method('verifyPeer')->willReturnCallback(fn () => match ($matcher->numberOfInvocations()) {
                1 => [true],
                2 => [false],
            });
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
        Client::configureOAuth(['client_id' => '123', 'auth_token' => 'def', 'store_hash' => 'abc', 'client_secret' => 'zyx']);
        $expectedPayload = ['iss' => '123', 'operation' => 'customer_login', 'store_hash' => 'abc', 'customer_id' => 1];
        $token = Client::getCustomerLoginToken(1);
        $key = new \Firebase\JWT\Key('zyx', 'HS256');
        $actualPayload = (array)\Firebase\JWT\JWT::decode($token, $key);
        foreach ($expectedPayload as $value) {
            $this->assertContains($value, $actualPayload);
        }
    }

    public function testGetCustomerLoginTokenThrowsIfNoClientSecret()
    {
        Client::configureOAuth(['client_id' => '123', 'auth_token' => 'def', 'store_hash' => 'abc']);
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Cannot sign customer login tokens without a client secret');
        Client::getCustomerLoginToken(1);
    }

    public function testGetResourceReturnsSpecifiedType()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue([[]]));

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $resource = Client::getResource('/whatever');
        $this->assertInstanceOf(\Bigcommerce\Api\Resource::class, $resource);
    }

    public function testGetCountReturnsSpecifiedCount()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue((object)['count' => 5]));

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $count = Client::getCount('/whatever');
        $this->assertSame(5, $count);
    }

    public function testGetCollectionReturnsCollectionOfSpecifiedTypes()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('http://storeurl' . $this->basePath . '/whatever', false)
            ->will($this->returnValue([[], []]));

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $resources = Client::getCollection('/whatever');
        $this->assertIsArray($resources);
        foreach ($resources as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resource::class, $resource);
        }
    }

    public function testCreateResourcePostsToTheRightPlace()
    {
        $new = [random_int(0, mt_getrandmax()) => random_int(0, mt_getrandmax())];
        $this->connection->expects($this->once())
            ->method('post')
            ->with('http://storeurl' . $this->basePath . '/whatever', (object)$new)
            ->will($this->returnValue($new));

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $result = Client::createResource('/whatever', $new);
        $this->assertSame($new, $result);
    }

    public function testUpdateResourcePutsToTheRightPlace()
    {
        $update = [random_int(0, mt_getrandmax()) => random_int(0, mt_getrandmax())];
        $this->connection->expects($this->once())
            ->method('put')
            ->with('http://storeurl' . $this->basePath . '/whatever', (object)$update)
            ->will($this->returnValue($update));

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
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

        Client::configure(['store_url' => 'http://storeurl', 'username' => 'whatever', 'api_key' => 'whatever']);
        Client::setConnection($this->connection); // re-set the connection since Client::configure unsets it
        $result = Client::deleteResource('/whatever');
        $this->assertSame("Successfully deleted", $result);
    }

    public function testGetTimeReturnsTheExpectedTime()
    {
        $now = new \DateTime();
        $this->connection->expects($this->once())
            ->method('get')
            ->with('https://api.bigcommerce.com/time', false)
            ->will($this->returnValue('1718283600000'));

        $this->assertEquals('2024-06-13 13:00:00', Client::getTime()->format('Y-m-d H:i:s'));
    }

    public function testGetStoreReturnsTheResultBodyDirectly()
    {
        $body = [random_int(0, mt_getrandmax()) => random_int(0, mt_getrandmax())];
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
            ->with('https://api.bigcommerce.com/time', false)
            ->will($this->returnValue(time()));

        $this->assertSame(12345, Client::getRequestsRemaining());
    }

    public static function collections()
    {
        return [
            //      path           function             classname
            ['products', 'getProducts', 'Product'],
            ['brands', 'getBrands', 'Brand'],
            ['orders', 'getOrders', 'Order'],
            ['customers', 'getCustomers', 'Customer'],
            ['coupons', 'getCoupons', 'Coupon'],
            ['categories', 'getCategories', 'Category'],
            ['options', 'getOptions', 'Option'],
            ['optionsets', 'getOptionSets', 'OptionSet'],
            ['products/skus', 'getSkus', 'Sku'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('collections')]
    public function testGettingASpecificResourceReturnsACollectionOfThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path, false)
            ->will($this->returnValue([[], []]));

        $collection = Client::$fnName();
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $class, $resource);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('collections')]
    public function testGettingTheCountOfACollectionReturnsThatCollectionsCount($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path . '/count', false)
            ->will($this->returnValue((object)['count' => 7]));

        $fnName .= 'Count';
        $count = Client::$fnName();
        $this->assertSame(7, $count);
    }

    public static function resources()
    {
        return [
            //    path            function        classname
            ['products',     '%sProduct',    'Product'],
            ['brands',       '%sBrand',      'Brand'],
            ['orders',       '%sOrder',      'Order'],
            ['customers',    '%sCustomer',   'Customer'],
            ['categories',   '%sCategory',   'Category'],
            ['options',      '%sOption',     'Option'],
            ['optionsets',   '%sOptionSet',  'OptionSet'],
            ['coupons',      '%sCoupon',     'Coupon'],
            ['currencies',   '%sCurrency',   'Currency'],
            ['pages',        '%sPage',       'Page'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('resources')]
    public function testGettingASpecificResourceReturnsThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/' . $path . '/1', false)
            ->will($this->returnValue([[], []]));

        $fnName = sprintf($fnName, 'get');
        $resource = Client::$fnName(1);
        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $class, $resource);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('resources')]
    public function testCreatingASpecificResourcePostsToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/' . $path, (object)[]);

        $fnName = sprintf($fnName, 'create');
        Client::$fnName([]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('resources')]
    public function testDeletingASpecificResourceDeletesToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/' . $path . '/1');

        $fnName = sprintf($fnName, 'delete');
        Client::$fnName(1);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('resources')]
    public function testUpdatingASpecificResourcePutsToThatResource($path, $fnName, $class)
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/' . $path . '/1');

        $fnName = sprintf($fnName, 'update');
        Client::$fnName(1, []);
    }

    // hand-test the Sku resource because of the wonky urls
    public function testCreatingASkuPostsToTheSkuResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/skus', (object)[]);

        Client::createSku(1, []);
    }

    public function testUpdatingASkuPutsToTheSkuResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/skus/1', (object)[]);

        Client::updateSku(1, []);
    }

    public function testGettingProductGoogleProductSearch()
    {
        $this->connection->expects($this->once())
          ->method('get')
          ->with($this->basePath . '/products/1/googleproductsearch')
          ->will($this->returnValue((object)[]));

        $resource = Client::getGoogleProductSearch(1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\ProductGoogleProductSearch::class, $resource);
    }

    public function testGettingProductImagesReturnsCollectionOfProductImages()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/images', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getProductImages(1);
        $this->assertIsArray($collection);
        $this->assertContainsOnlyInstancesOf(\Bigcommerce\Api\Resources\ProductImage::class, $collection);
    }

    public function testGettingProductCustomFieldsReturnsCollectionOfProductCustomFields()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/custom_fields', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getProductCustomFields(1);
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\ProductCustomField::class, $resource);
        }
    }

    public function testGettingASpecifiedProductImageReturnsThatProductImage()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/images/1', false)
            ->will($this->returnValue([[], []]));

        $resource = Client::getProductImage(1, 1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\ProductImage::class, $resource);
    }

    public function testGettingASpecifiedProductCustomFieldReturnsThatProductCustomField()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/custom_fields/1', false)
            ->will($this->returnValue([[], []]));

        $resource = Client::getProductCustomField(1, 1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\ProductCustomField::class, $resource);
    }

    public function testGettingASpecifiedOptionValueReturnsThatOptionValue()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/1/values/1', false)
            ->will($this->returnValue([[], []]));

        $resource = Client::getOptionValue(1, 1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\OptionValue::class, $resource);
    }

    public function testGettingCustomerAddressesReturnsCollectionOfCustomerAddresses()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/customers/1/addresses', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getCustomerAddresses(1);
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Address::class, $resource);
        }
    }

    public function testGettingOptionValuesReturnsCollectionOfOptionValues()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/options/values', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getOptionValues();
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\OptionValue::class, $resource);
        }
    }

    public function testCreatingAnOptionSetPostsToTheOptionSetsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/optionsets', (object)[]);

        Client::createOptionSet([]);
    }

    public function testCreatingAnOptionPostsToTheOptionResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/options', (object)[]);

        Client::createOption([]);
    }

    public function testCreatingAnOptionSetOptionPostsToTheOptionSetsOptionsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/optionsets/1/options', (object)[]);

        Client::createOptionSetOption([], 1);
    }

    public function testCreatingAProductImagePostsToTheProductImageResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/images', (object)[]);

        Client::createProductImage(1, []);
    }

    public function testCreatingAProductCustomFieldPostsToTheProductCustomFieldResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/custom_fields', (object)[]);

        Client::createProductCustomField(1, []);
    }

    public function testUpdatingAProductImagePutsToTheProductImageResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/images/1', (object)[]);

        Client::updateProductImage(1, 1, []);
    }

    public function testUpdatingAProductCustomFieldPutsToTheProductCustomFieldResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/custom_fields/1', (object)[]);

        Client::updateProductCustomField(1, 1, []);
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
            ->with($this->basePath . '/products/1/custom_fields/1');

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
            ->will($this->returnValue([[], []]));

        $resource = Client::getCoupon(1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\Coupon::class, $resource);
    }

    public function testGettingASpecifiedOrderStatusReturnsThatOrderStatus()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/order_statuses/1', false)
            ->will($this->returnValue([[], []]));

        $resource = Client::getOrderStatus(1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\OrderStatus::class, $resource);
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
            ->will($this->returnValue((object)['count' => 7]));

        $count = Client::getOrderProductsCount(1);
        $this->assertSame(7, $count);
    }

    public function testGettingOrderShipmentReturnsTheOrderShipmentResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments/1', false)
            ->will($this->returnValue([[], []]));

        $resource = Client::getShipment(1, 1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\Shipment::class, $resource);
    }

    public function testGettingOrderProductsReturnsTheOrderProductsCollection()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/products', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getOrderProducts(1);
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\OrderProduct::class, $resource);
        }
    }

    public function testGettingOrderShipmentsReturnsTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getShipments(1);
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Shipment::class, $resource);
        }
    }

    public function testCreatingOrderShipmentsPostsToTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/orders/1/shipments', (object)[]);

        Client::createShipment(1, []);
    }

    public function testUpdatingOrderShipmentsPutsToTheOrderShipmentsResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/orders/1/shipments/1', (object)[]);

        Client::updateShipment(1, 1, []);
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
            ->will($this->returnValue([[], []]));

        $resource = Client::getOrderShippingAddress(1, 1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resources\Address::class, $resource);
    }

    public function testGettingOrderShippingAddressesReturnsTheAddressResource()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipping_addresses', false)
            ->will($this->returnValue([[], []]));

        $collection = Client::getOrderShippingAddresses(1);
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Address::class, $resource);
        }
    }

    public function testCreatingGiftCertificatePostsToTheGiftCertificateResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/gift_certificates', (object)[]);

        Client::createGiftCertificate([]);
    }

    public function testGettingSpecifiedGiftCertificateReturnsTheSpecifiedGiftCertificate()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/gift_certificates/1', false)
            ->will($this->returnValue([[], []]));

        Client::getGiftCertificate(1);
    }

    public function testGettingGiftCertificatesReturnsTheGiftCertificates()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/gift_certificates', false)
            ->will($this->returnValue([[], []]));

        Client::getGiftCertificates();
    }

    public function testUpdatingSpecifiedGiftCertificatePutsToTheSpecifiedGiftCertificateResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/gift_certificates/1', (object)[]);

        Client::updateGiftCertificate(1, []);
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
            ->will($this->returnValue([new \Bigcommerce\Api\Resource(), new \Bigcommerce\Api\Resource()]));
        $collection = Client::listWebhooks();
        $this->assertIsArray($collection);
        foreach ($collection as $resource) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resource::class, $resource);
        }
    }

    public function testGettingSpecifiedWebhookReturnsTheSpecifiedWebhook()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/hooks/1', false)
            ->will($this->returnValue(new \Bigcommerce\Api\Resource()));
        $resource = Client::getWebhook(1);
        $this->assertInstanceOf(\Bigcommerce\Api\Resource::class, $resource);
    }

    public function testCreatingWebhookPostsToTheSpecifiedResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/hooks', (object)[]);
        Client::createWebhook([]);
    }
    public function testUpdatingWebhookPutsToTheSpecifiedResource()
    {
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/hooks/1', (object)[]);
        Client::updateWebhook(1, []);
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
            ->with($this->basePath . '/products/1/reviews', (object)[]);

        Client::createProductReview(1, []);
    }

    public function testCreatingProductBulkPricingRulesPostsToTheProductBulkPricingRulesResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/discount_rules', (object)[]);

        Client::createProductBulkPricingRules(1, []);
    }

    public function testCreatingMarketingBannerPostsToTheMarketingBannerResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/banners', (object)[]);

        Client::createMarketingBanner([]);
    }

    public function testGettingMarketingBannersReturnsTheMarketingBanners()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/banners', false)
            ->will($this->returnValue([[], []]));

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
            ->with($this->basePath . '/banners/1', (object)[]);

        Client::updateMarketingBanner(1, []);
    }

    public function testCreatingCustomerAddressPostsToTheCustomerAddressResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/customers/1/addresses', (object)[]);

        Client::createCustomerAddress(1, []);
    }

    public function testCreatingProductRulePostsToTheProductRuleResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/rules', (object)[]);

        Client::createProductRule(1, []);
    }

    public function testCreatingCustomerGroupPostsToTheCustomerGroupResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/customer_groups', (object)[]);

        Client::createCustomerGroup([]);
    }

    public function testGettingASpecifiedCustomerGroupsReturnsTheCustomerGroups()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/customer_groups', false)
            ->will($this->returnValue([[], []]));

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
            ->will($this->returnValue([[], []]));

        Client::getProductOptions(1);
    }

    public function testGettingASpecifiedProductOptionReturnsThatProductOption()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/options/1', false)
            ->will($this->returnValue([[], []]));

        Client::getProductOption(1, 1);
    }

    public function testGettingASpecifiedProductRuleReturnsThatProductRule()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/rules/1', false)
            ->will($this->returnValue([[], []]));

        Client::getProductRule(1, 1);
    }

    public function testCreatingOptionValuePostsToTheOptionValueResource()
    {
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/options/1/values', (object)[]);

        Client::createOptionValue(1, []);
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
            ->with($this->basePath . '/options/1/values/1', (object)[]);

        Client::updateOptionValue(1, 1, []);
    }

    public function testConnectionUsesApiUrlOverride()
    {
        $this->connection->expects($this->once())
            ->method('get')
            ->with('https://api.url.com/time');

        Client::configureOAuth([
            'client_id' => '123',
            'auth_token' => '123xyz',
            'store_hash' => 'abc123',
            'api_url' => 'https://api.url.com',
            'login_url' => 'https://login.url.com',
        ]);
        Client::setConnection($this->connection); // re-set the connection since Client::setConnection unsets it

        Client::getTime();
    }
}
