<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada;

use Yodorada\Classes\Database;
use Yodorada\Classes\Filters;
use Yodorada\Classes\Headers;
use Yodorada\Classes\Input;
use Yodorada\Classes\Query;
use Yodorada\Classes\Registry;
use Yodorada\Classes\Request;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Translate;
use Yodorada\Models\LogsModel;
use Yodorada\Modules\ServiceUser;

/**
 * class Core
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
abstract class Core
{
    const ACCESS_LEVEL_PUBLIC = 'public';
    const ACCESS_LEVEL_RESTRICTED = 'restricted';

    /**
     * save access level
     *
     * @var string
     */
    protected static $accessLevel = self::ACCESS_LEVEL_RESTRICTED;

    protected function initialize()
    {

        define('PUBLIC_ROOT', dirname(API_ROOT));

        Setting::initialize();
        Input::initialize();
        Request::initialize(Setting::get('route'));
        Query::initialize(Setting::get('query'));
        Filters::initialize(Setting::get('query'));

        Database::initialize();
        Registry::initialize();
        Translate::initialize();

        Headers::allowOrigin(array('*'));
        Headers::cacheControl();
        Headers::contentType();
    }

    public static function log($arr = array('status' => 'success', 'message' => ''))
    {
        if (self::$accessLevel == self::ACCESS_LEVEL_PUBLIC) {
            // do not log public access
            return;
        }

        $controller = Registry::getController();
        $log = new LogsModel();
        $log->id = 0;
        $log->created = time();
        $log->usersId = ServiceUser::get('id');
        $log->resource = join('/', Setting::get('route'));
        $log->method = strtoupper(Setting::get('method'));
        $log->controller = ($controller != null ? $controller['controller'] : '');
        $log->status = isset($arr['status']) ? $arr['status'] : '';
        $log->scope = isset($arr['scope']) ? $arr['scope'] : '';
        $log->httpStatusCode = Headers::$statusCode;
        $log->httpStatusString = Headers::statusString();
        $log->version = ($controller != null ? $controller['version'] : '');
        if (isset($arr['message'])) {
            if (is_array($arr['message'])) {
                $arr['message'] = join(" / ", array_values($arr['message']));
            }
            $log->message = $arr['message'];
        }
        $log->dataTransfer = serialize(array('input' => array_keys(Input::getAllData()), 'query' => Query::getAllData(), 'filter' => Filters::getAllData()));
        $id = $log->save();
    }

    public static function debug($obj = null)
    {
        Headers::contentType('application/json');
        if (WEBSERVICE_MODE == 'development') {

            if ($obj != null) {
                echo json_encode($obj);
            }

        }
        exit;
    }
    public static function debugText($obj = null)
    {
        Headers::contentType('text/html');
        if (WEBSERVICE_MODE == 'development') {

            if ($obj != null) {
                echo $obj;
            }

        }
        exit;
    }
    public static function debugArray($obj = null)
    {
        Headers::contentType('text/html');
        if (WEBSERVICE_MODE == 'development') {

            if ($obj != null) {
                print_r($obj);
            }

        }
        exit;
    }
}
