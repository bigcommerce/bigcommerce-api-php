<?php

require 'vendor/autoload.php';
use Bigcommerce\Api\Client as Bigcommerce;

error_reporting(E_ALL);
ini_set('display_errors', '1');


Bigcommerce::configure(array(
'store_url' => 'https://store-bwvr466.mybigcommerce.com',
'username' => 'saranyan',
'api_key' => 'd7948f71684475c6b6d8b29c36ae37cd'
));



Bigcommerce::setCipher('RC4-SHA');
Bigcommerce::verifyPeer(false);

// $option = array('name' => 'h simpson', 'type' => 'T');
    
// $options = Bigcommerce::createOptions($option);
// print_r($options);

// $optionset = array('name' => 'Crazy S family');
// $oset =  Bigcommerce::createOptionsets($optionset);
// print_r($oset)

$option = array('option_id' => 34, 'display_name' => "Crazy Simpson Family");
$optionset_id = 29;
$options =  Bigcommerce::createOptionsets_Options($option,$optionset_id);
print_r($options);
?>
