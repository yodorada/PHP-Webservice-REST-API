<?php

/**
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 *
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.1.1
 */

define('API_ROOT', dirname(__FILE__));

/** @var Composer\Autoload\ClassLoader */
require __DIR__ . '/../../app/vendor/autoload.php';

use Yodorada\Classes\Registry;
use Yodorada\WebService;

/** initialize the webservice */
include_once __DIR__ . '/../../app/config/initialize.php';

/** add your own custom controller */
$routes = include_once __DIR__ . '/../../app/config/custom.php';
if (is_array($routes)) {
    Registry::register($routes);
}

/** instantiate and run webservice */
$api = new WebService();
$api->run();
