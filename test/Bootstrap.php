<?php
/**
 * Bootstrap file for test suite
 */
define('APPLICATION_PATH',dirname(dirname(__FILE__)).'/');
require_once  APPLICATION_PATH . '/vendor/autoload.php';
if (file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . '.env')) {
    \Dotenv::load(APPLICATION_PATH);
}
