<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | Yodorada
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

/**
 * Class Setting
 *
 * Provide settings
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.4
 */

class Setting
{

    /**
     * Current data
     * @var array
     */
    protected static $arrData = array();

    /**
     * set settings array
     *
     * @return  void
     *
     * @author maya.k.herrmann@gmail.com
     */
    public static function initialize()
    {
        $arr = array();
        $arr['scriptStart'] = time();
        $arr['elapsedStart'] = microtime(true);
        $arr['method'] = strtolower($_SERVER['REQUEST_METHOD']);
        $uri = $_SERVER['REQUEST_URI'];
        $arr['query'] = array();
        if (strlen($_SERVER['QUERY_STRING'])) {
            $parameters = array();
            parse_str($_SERVER['QUERY_STRING'], $parameters);
            $arr['query'] = $parameters;
            $uri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $uri);
        }
        $arr['outputFormat'] = 'json';
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $arr['contentType'] = $_SERVER['CONTENT_TYPE'];
            if ($arr['contentType'] == "application/json") {
                $arr['inputFormat'] = 'json';
            } elseif ($arr['contentType'] == "application/x-www-form-urlencoded" || $arr['contentType'] == "multipart/form-data") {
                $arr['inputFormat'] = 'html';
            }
        }
        if (strlen($GLOBALS['CONFIG']['API']['DIRECTORY'])) {
            $dir = trim($GLOBALS['CONFIG']['API']['DIRECTORY'], '/');
            $uri = str_replace($dir, '', $uri);
        }
        $arr['pathInfo'] = trim(trim($uri, '//'), '/');
        $arr['script'] = rtrim($_SERVER['PHP_SELF'], '/');

        $arr['route'] = array_filter(explode('/', $arr['pathInfo']), function ($val) {return strlen(trim($val));});

        if (isset($_SERVER['HTTP_REALM']) || isset($_SERVER['REDIRECT_HTTP_REALM'])) {
            if (isset($_SERVER['HTTP_REALM'])) {
                $arr['realm'] = $_SERVER['HTTP_REALM'];
            } else {
                $arr['realm'] = $_SERVER['REDIRECT_HTTP_REALM'];
            }
        }

        static::$arrData = $arr;
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
        if (in_array($strKey, get_class_methods('\Yodorada\Classes\Setting'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (!isset(static::$arrData[$strKey])) {
            return null;
        }
        return static::$arrData[$strKey];
    }
}
