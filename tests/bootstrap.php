<?php

if (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION == 4) {
    // ignore deprecation warning in PHP 7.4
    // https://github.com/symfony/symfony/issues/34807
    error_reporting(-1 & ~E_DEPRECATED);
} else {
    error_reporting(-1);
}

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('UTC');
