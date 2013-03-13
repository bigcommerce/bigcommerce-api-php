<?php

require_once 'Bigcommerce/Api.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');


Bigcommerce_Api::configure(array(
'store_url' => 'https://store-bwvr466.mybigcommerce.com',
'username' => 'saranyan',
'api_key' => 'd7948f71684475c6b6d8b29c36ae37cd'
));
Bigcommerce_Api::setCipher('RC4-SHA');
Bigcommerce_Api::verifyPeer(false);


$count = Bigcommerce_Api::getOrdersCount()/10;
for ($i = 1; $i <= $count+1; $i++) {
    $filter = array('limit' => 10, 'page' => $i);
    $orders = Bigcommerce_Api::getOrders($filter);
    echo sizeof($orders);
 }


?>