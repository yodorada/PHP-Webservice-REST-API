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
 * Class Input
 *
 * Provide input data settings
 *
 * This static class gets the php://input and processes it
 * into key value pairs (if input is valid json).
 *
 * usage:
 * $username = Input::get('username');
 * $someValue = Input::get('someValue');
 *
 * returns null if key not set
 *
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.4
 */

class Input
{

    /**
     * Current data
     * @var array
     */
    protected static $arrData = array();

    /**
     * json status
     * @var bool
     */
    public static $validJson = true;

    /**
     * initalize
     *
     * @return  void
     *
     */
    public static function initialize()
    {
        $input = file_get_contents('php://input');
        if (!strlen($input)) {
            return;
        }
        if (!Utils::isJson($input)) {
            static::$validJson = false;
            return;
        }
        $data = json_decode($input, true);
        if (!is_array($data) || !count($data)) {
            return;
        }
        if (isset($data['token'])) {
            unset($data['token']);
        }
        $keys = Utils::sanitize(array_keys($data));
        $values = array();
        foreach ($data as $key => $value) {
            $values[] = $value;
        }
        static::$arrData = array_combine($keys, $values);
    }

    /**
     * check if self has data
     *
     * @return array
     */
    public static function hasData()
    {
        return count(static::$arrData);
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
        if (in_array($strKey, get_class_methods('\Yodorada\Classes\Input'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (!isset(static::$arrData[$strKey])) {
            return null;
        }
        return static::$arrData[$strKey];
    }

}
