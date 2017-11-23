<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

/**
 * Class Filters
 *
 * Provides the current request queries as key value pairs
 *
 * usage:
 * $offset = Filters::get('offset');
 * $limit = Filters::get('limit');
 *
 * returns null if key not set
 *
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.1
 */

class Filters
{

    /**
     * Current data
     * @var array
     */
    protected static $arrData = array();

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
     * Return bool if has data
     *
     * @return bool
     */
    public static function hasData()
    {
        return (count(static::$arrData) > 0);

    }

    /**
     * set all object properties
     *
     * @param array  $arr
     */
    public static function initialize($arr = array())
    {
        $params = array();
        if (count($arr)) {
            if (array_key_exists('filter', $arr)) {
                $params = json_decode($arr['filter'], true);
            }
        }
        static::$arrData = $params;
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
        if (in_array($strKey, get_class_methods('\Yodorada\Classes\Filters'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (!isset(static::$arrData[$strKey])) {
            return null;
        }
        return static::$arrData[$strKey];
    }

}
