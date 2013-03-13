#!/bin/bash

curl -s https://getcomposer.org/installer | php -d detect_unicode=Off
php composer.phar install
