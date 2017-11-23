<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Classes;

use Yodorada\Controller\Controller;
use Yodorada\Core;

/**
 * static Class Errors
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.2
 */

class Errors
{

    /**
     * Current data
     * @var array
     */
    protected static $arrErrors = array();

    /**
     * exit with collected errors (400)
     *
     * @return  void
     *
     */
    public static function exitWith($msg)
    {
        Headers::send(400);
        Utils::outputError($msg);
        Core::log(array('message' => $msg, 'status' => Controller::STATUS_ERROR));
        exit;
    }

    /**
     * exit with collected errors (400)
     *
     * @return  void
     *
     */
    public static function exitWithErrors()
    {
        if (!count(static::$arrErrors)) {
            static::exitGeneralError();
        }
        Headers::send(400);
        Utils::outputError(static::$arrErrors);
        Core::log(array('message' => static::$arrErrors, 'status' => Controller::STATUS_ERROR));
        exit;
    }

    /**
     * exit with 400 Bad Request
     *
     * @return  void
     */
    public static function exitBadRequest($err = null)
    {
        Headers::send(400);
        $err = $err != null ? $err : 'The request could not be understood by the server due to malformed syntax.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_FAIL));
        exit;
    }

    /**
     * exit with 403 Forbidden
     *
     * @return  void
     */
    public static function exitForbidden($err = null)
    {
        Headers::send(403);
        $err = $err != null ? $err : 'Not authorized to perform the operation or the resource is unavailable.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_FAIL));
        exit;
    }

    /**
     * exit with 404 Not Found
     *
     * @return  void
     */
    public static function exitNotFound($err = null)
    {
        Headers::send(404);
        $err = $err != null ? $err : 'The server has not found anything matching the Request-URI.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_ERROR));
        exit;
    }

    /**
     * exit with 405 Method Not Allowed
     *
     * @return  void
     *
     */
    public static function exitMethodNotAllowed($err = null)
    {
        Headers::send(405);
        $err = $err != null ? $err : 'Current method is not allowed.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_ERROR));
        exit;
    }

    /**
     * exit with 409 Conflict
     *
     * @return  void
     */
    public static function exitAlreadyExists($err = null)
    {
        Headers::send(409);
        $err = $err != null ? $err : 'The request could not be completed due to a conflict with the current state of the resource.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_FAIL));
        exit;
    }

    /**
     * exit with 422 Unprocessable entity
     *
     * @return  void
     */
    public static function exitUnprocessable($err = null)
    {
        Headers::send(422);
        $err = $err != null ? $err : 'The server encountered a validation error or an unprocessable entity.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_FAIL));
        exit;
    }

    /**
     * exit with 500 Internal Server Error
     *
     * @return  void
     *
     */
    public static function exitGeneralError($err = null)
    {
        Headers::send(500);
        $err = $err != null ? $err : 'The server encountered an unexpected condition which prevented it from fulfilling the request.';
        Utils::outputError($err);
        Core::log(array('message' => $err, 'status' => Controller::STATUS_ERROR));
        exit;
    }

    /**
     * set errors
     *
     * @param array $arr
     */
    public static function setErrors($arr)
    {
        static::$arrErrors = $arr;
    }

    /**
     * Add an error
     *
     * @param string $str
     */
    public static function add($str)
    {
        static::$arrErrors[] = $str;
    }

    /**
     * has error(s)
     *
     * @return bool
     */
    public static function hasError()
    {
        return count(static::$arrErrors) > 0;
    }

}
