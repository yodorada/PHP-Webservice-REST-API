<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Controller;

use Yodorada\Classes\Errors;
use Yodorada\Classes\Filters;
use Yodorada\Classes\Headers;
use Yodorada\Classes\Input;
use Yodorada\Classes\Query;
use Yodorada\Classes\Registry;
use Yodorada\Classes\Request;
use Yodorada\Classes\Setting;
use Yodorada\Classes\Translate;
use Yodorada\Classes\Utils;
use Yodorada\Core;

/**
 * abstract class Controller
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.2.0
 */
abstract class Controller extends Core
{
    const SCOPE_RESOURCE = 'resource';
    const SCOPE_COLLECTION = 'collection';
    const SCOPE_ANY = 'any';

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_FAIL = 'fail';

    /**
     * current controller version
     *
     * @var string
     */
    public static $version = '0.2.0';

    /**
     * brief self explanatory text
     *
     * @var string
     */
    protected $selfInfo = '';

    /**
     * total count when lists
     *
     * @var int
     */
    protected $totalCount = -1;

    /**
     * resource id if scope resource
     *
     * @var id
     */
    protected $resourceId = false;

    /**
     * resource id of parent if available
     *
     * @var id
     */
    protected $parentId = false;

    /**
     * current registred controller key
     *
     * @var string
     */
    protected $primaryKey;

    /**
     * current registred controller parent key
     *
     * @var string|null
     */
    protected $parentKey;

    /**
     * controller status
     *
     * @var string
     */
    protected static $status = self::STATUS_SUCCESS;

    /**
     * scope ( resource / collection / any)
     *
     * @var string
     */
    public static $scope = self::SCOPE_COLLECTION;

    /**
     * log message
     *
     * @var array
     */
    public static $logMessages = array();

    /**
     * retrieve info
     *
     */
    public function info()
    {
        return $this->selfInfo;
    }

    /**
     * check for parent ID if non flat hierarchy
     *
     * @return  bool
     */
    protected function needsAndHasParent()
    {
        return ($GLOBALS['CONFIG']['APPLICATION']['FLAT_HIERARCHY'] && isset($this->parentId));
    }

    /**
     * add new location to output when successful POST
     *
     * @param $id the new id
     * @return  string
     */
    protected function locationNewResource($id)
    {
        return array('location' => Utils::getHost() . '/' . join('/', Setting::get('route')) . '/' . $id);
    }

    /**
     * run the controller and echo response -> output
     *
     */
    public function run()
    {
        $now = time();
        $this->primaryKey = $pk = Registry::getController()['primaryKey'];
        $this->parentKey = $fk = Registry::getController()['parentKey'];

        if (Request::get($pk)) {
            $this->resourceId = Request::get($pk);
        }
        if (Request::get($fk)) {
            $this->parentId = Request::get($fk);
        } elseif (Input::get($fk . 'Id') && (int) Input::get($fk . 'Id') > 0) {
            $this->parentId = Input::get($fk . 'Id');
        } elseif (Query::field($fk . 'Id')) {
            $this->parentId = Query::field($fk . 'Id');
        }

        $method = Setting::get('method');

        if ($method == 'patch') {
            $method = 'put';
        }

        if ($method == 'get' && Request::get($pk) && Request::get($pk) == 'info') {
            $json = $this->selfInfo;
            static::$scope = self::SCOPE_ANY;
        } else {
            // auto set scope when PUT, PATCH, DELETE or resource id is set
            if ($method == 'put' || $method == 'patch' || $method == 'delete' || $method == 'post' || $this->resourceId) {
                static::$scope = self::SCOPE_RESOURCE;
            }
            if ($method == 'get') {
                if ($this->resourceId == null) {
                    // make a x-total-count db call first
                    $this->totalCount = $this->total();
                }
            }

            //execute method and get return data

            if (Filters::hasData() && $method == 'get') {
                $json = $this->filter();
            } else {
                $json = $this->$method();
            }

            if ($method == 'delete' && ($json === true || !$GLOBALS['CONFIG']['APPLICATION']['AOR_BACKEND'])) {
                Headers::send(204);
                $log = array('status' => static::$status, 'scope' => static::$scope);
                static::log($log);
                exit;
            }
        }

        if (static::$status == self::STATUS_ERROR || $json === null) {
            Errors::exitWithErrors();
        }

        // service response
        $service = array(
            'start' => Utils::timestampToOutput(Setting::get('scriptStart')),
            'stop' => Utils::timestampToOutput($now),
            'elapsed' => (microtime(true) - Setting::get('elapsedStart')),
            'resource' => Utils::getHostAndApiPath() . '/' . join('/', Setting::get('route')),
            'method' => strtoupper($method),
        );

        if ($method == 'get') {
            if ($this->totalCount !== -1 && $this->totalCount !== null) {
                Headers::totalCount($this->totalCount);
                $service['totalCount'] = $this->totalCount;
            } else {
                // try to evaluate json count
                Headers::totalCount($json !== false ? 1 : 0);
                $service['totalCount'] = $json !== false ? 1 : 0;
            }
        }

        // check for info / prepare json data
        if ($json == $this->selfInfo) {
            $resources = Registry::getController()['resources'];
            $fields = $this->fields();
            $json = array('info' => Translate::get($this->selfInfo), 'resources' => $resources, 'fields' => $fields);
        }

        if (!count(Filters::getAllData()) && Setting::get('route')[0] != 'authorized') {
            // log
            $log = array('status' => static::$status, 'scope' => static::$scope);
            if (count(static::$logMessages)) {
                $log['message'] = static::$logMessages;
            }
            static::log($log);
        }

        $output = array('data' => $json);
        if ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['ADD_WEBSERVICE_STATS']) {
            $output = array_merge_recursive($output,
                array(
                    'status' => static::$status,
                    'scope' => static::$scope,
                    'webservice' => $service,
                )
            );
        }
        if ($method == 'post') {
            Headers::send(201);
        } else {
            Headers::send(200);
        }

        switch ($GLOBALS['CONFIG']['APPLICATION']['OUTPUT']['FORMAT']) {
            case 'xml':
                // xml output
                Headers::contentType('text/xml');
                echo Utils::generateXML($output);
                break;
            case 'json':
            default:
                // json output
                Headers::contentType('application/x-' . static::$scope . '+json');
                echo json_encode($output);
                break;
        }
    }
}
