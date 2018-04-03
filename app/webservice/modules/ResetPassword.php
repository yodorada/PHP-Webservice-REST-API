<?php

/*
 * Part of PHP-Webservice-REST-API
 *
 * Copyright (c) Maya K. Herrmann | EINS[23].TV
 *
 * @license LGPL-3.0+
 */

namespace Yodorada\Modules;

use Yodorada\Classes\Utils;
use Yodorada\Models\UsersModel;

/**
 * class ResetPassword
 * @package   Yodorada\Webservice
 * @author    EINS[23].TV | Maya K. Herrmann <maya.k.herrmann@gmail.com>
 * @copyright EINS[23].TV, 2017
 * @version 0.0.1
 */

class ResetPassword
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
     * Return boolean result
     *
     * @return bool
     */
    public static function valid()
    {
        if (isset(static::$arrData['user'])) {
            return true;
        }
        return false;
    }

    /**
     * Return boolean result
     *
     * @return bool
     */
    public static function resetted()
    {
        if (isset(static::$arrData['reset'])) {
            return true;
        }
        return false;
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
        if (in_array($strKey, get_class_methods('\Yodorada\Modules\ResetPassword'))) {
            static::$arrData[$strKey] = static::$strKey();
        }
        if (isset(static::$arrData[$strKey])) {
            return static::$arrData[$strKey];
        }
        return null;
    }
    /**
     * authentification
     *
     * @return  void
     */
    public static function initialize()
    {

        if (strlen($_SERVER['QUERY_STRING'])) {
            $parameters = array();
            parse_str($_SERVER['QUERY_STRING'], $parameters);
            if (isset($parameters['token'])) {
                $userModel = UsersModel::where('confirmationToken', $parameters['token'])->getOne();
                if (count($userModel)) {
                    // check time
                    // token valid for 6 hours
                    if ($userModel->passwordRequestedAt < time() - 60 * 60 * 6) {
                        static::$arrData['error'] = 'Der Link zum Zurücksetzen des Passwortes ist bereits abgelaufen.';
                        return;
                    }
                    $user = $userModel->toArray();
                    unset($user['password']);
                    static::$arrData['user'] = $user;
                } else {
                    static::$arrData['error'] = 'Dieser Link ist ungültig.';
                    return;
                }
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $parameters = filter_input_array(INPUT_POST, array('action' => FILTER_SANITIZE_STRING, 'password' => FILTER_SANITIZE_STRING, 'passwordConfirm' => FILTER_SANITIZE_STRING));

            if (
                isset($parameters['action']) &&
                $parameters['action'] == 'resetpassword' &&
                isset($parameters['password']) &&
                isset($parameters['passwordConfirm'])

            ) {

                // check password input
                if (!strlen($parameters['password']) || !strlen($parameters['passwordConfirm'])) {
                    static::$arrData['error'] = 'Bitte ein Passwort angeben.';
                    return;
                }

                // check identical passwords
                if ($parameters['password'] != $parameters['passwordConfirm']) {
                    static::$arrData['error'] = 'Die beiden Passwörter müssen übereinstimmen.';
                    return;
                }

                // check password requirements
                preg_match('/^(?=.*[0-9])([a-zA-Z0-9.\-_]){6,}$/', $parameters['password'], $pwCheck);
                if (!count($pwCheck)) {
                    static::$arrData['error'] = 'Das eingegebene Passwort entspricht nicht den Anforderungen (siehe Hinweis weiter unten).';
                    return;
                }

                // all good > set new password
                $newPw = Utils::hashPassword($parameters['password']);
                $userModel->password = $newPw;
                $userModel->confirmationToken = '';
                $userModel->passwordRequestedAt = 0;
                $userModel->save();

                static::$arrData['reset'] = true;
            }
        }
    }

}
