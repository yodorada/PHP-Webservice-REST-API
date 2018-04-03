<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

use Yodorada\Classes\Utils;

/**
 * Class Request
 *
 * Provides the current uri request as key value pairs
 * eg: api.tld/groups/123/users/2
 * generates "groups" => 123 / "users" => 2
 *
 * usage:
 * $groups = Request::get('groups');
 * $users = Request::get('users');
 *
 * returns null if key not set
 *
 * Provide request data function
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.6
 */

class Request
{

    /**
     * Current data
     * @var array
     */
    protected static $arrData = array();

    /**
     * initialize with request uri
     *
     * @param $arr array
     * @return void
     */
    public static function initialize($arr = array())
    {
        static::$arrData = Utils::pairArrayNth($arr);
    }

    /**
     * Return all object properties
     *
     * @return array
     */
    public static function getAllData()
    {
        return static::$arrData;

    }

    /**
     * set all object properties
     *
     * @param array  $arr
     */
    public static function setData($arr)
    {
        static::$arrData = $arr;
    }

    /**
     * Set an object property
     *
     * @param string $strKey
     * @param mixed  $varValue
     */
    public static function set($strKey, $varValue)
    {
        static::$arrData[$strKey] = $varValue;
    }

    /**
     * Return an object prop
     *
     * @param string $strKey The variable name
     *
     * @return mixed The variable value
     */
    public static function get($strKey)
    {
        if (isset(static::$arrData[$strKey])) {
            return static::$arrData[$strKey];
        }
        if (in_array($strKey, get_class_methods('\Yodorada\Classes\Request'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (!isset(static::$arrData[$strKey])) {
            return null;
        }
        return static::$arrData[$strKey];
    }
}
