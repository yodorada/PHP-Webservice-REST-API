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
 * Class Database
 *
 * Provide input data settings
 * @package   Yodorada\Webservice
 * @author    Yodorada | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright Yodorada, 2017
 * @version 0.0.2
 */

class Database
{

    /**
     * database
     * @var \MysqliDb
     */
    protected static $db;

    /**
     * create object instance (Singleton)
     *
     * @return void
     *
     */
    public static function initialize()
    {
        static::$db = new \MysqliDb(
            $GLOBALS['CONFIG']['DB']['HOST'],
            $GLOBALS['CONFIG']['DB']['USER'],
            $GLOBALS['CONFIG']['DB']['PASSWORD'],
            $GLOBALS['CONFIG']['DB']['NAME'],
            $GLOBALS['CONFIG']['DB']['PORT']
        );
        static::$db->setPrefix($GLOBALS['CONFIG']['DB']['PREFIX']);
    }

    /**
     * Return the MysqliDb instance
     *
     * @return \MysqliDb The object instance
     *
     */
    public static function getInstance()
    {
        if (!static::$db instanceof \MysqliDb) {
            static::initialize();
        }
        return static::$db;
    }
}
