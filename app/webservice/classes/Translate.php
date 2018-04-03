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
 * Class Translate
 *
 * Provide translation settings
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 */

class Translate
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
    protected static $lang;

    /**
     * initalize
     *
     * @return  void
     *
     */
    public static function initialize()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if ($locale !== null) {
                $locale = explode("_", $locale)[0];
            }
            $langFile = 'lang_' . $locale . '.json';
            if (file_exists(__DIR__ . '/../../translations/' . $langFile)) {
                $content = file_get_contents(__DIR__ . '/../../translations/' . $langFile);
                if (!Utils::isJson($content)) {
                    $content = null;
                }
            }
        }
        if (!isset($content)) {
            $content = file_get_contents(__DIR__ . '/../../translations/lang_en.json');
        }
        static::$lang = json_decode($content, true);
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
        if (in_array($strKey, get_class_methods('\Yodorada\Classes\Translate'))) {
            static::$arrData[$strKey] = static::$strKey();
        }

        static::$arrData[$strKey] = static::findKey(static::$lang, explode(".", $strKey));

        if (func_num_args() > 1) {
            $args = array_slice(func_get_args(), 1);
            static::$arrData[$strKey] = vsprintf(static::$arrData[$strKey], $args);
        }

        return static::$arrData[$strKey];
    }

    protected static function findKey(array $arr, $parts)
    {
        $key = array_shift($parts);
        if (isset($arr[$key])) {
            if (is_array($arr[$key])) {
                return static::findKey($arr[$key], $parts);
            }
            return $arr[$key];
        }
        return null;
    }
}
